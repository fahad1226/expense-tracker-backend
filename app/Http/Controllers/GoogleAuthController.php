<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $clientId = config('services.google.client_id');
        if (! is_string($clientId) || $clientId === '') {
            return response()->json([
                'message' => 'Google sign-in is not configured on the server.',
            ], 503);
        }

        $request->validate([
            'credential' => ['required', 'string'],
        ]);

        $idToken = $request->input('credential');

        $tokenInfo = Http::acceptJson()
            ->timeout(10)
            ->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

        if (! $tokenInfo->ok()) {
            return response()->json(['message' => 'Invalid Google sign-in token.'], 401);
        }

        /** @var array<string, mixed> $payload */
        $payload = $tokenInfo->json();

        if (($payload['aud'] ?? null) !== $clientId) {
            return response()->json(['message' => 'Google token audience mismatch.'], 401);
        }

        $iss = (string) ($payload['iss'] ?? '');
        if ($iss !== 'https://accounts.google.com' && $iss !== 'accounts.google.com') {
            return response()->json(['message' => 'Invalid token issuer.'], 401);
        }

        $email = (string) ($payload['email'] ?? '');
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Google did not return a valid email.'], 422);
        }

        $verified = $payload['email_verified'] ?? false;
        if ($verified !== true && $verified !== 'true') {
            return response()->json(['message' => 'Google email is not verified.'], 422);
        }

        $sub = (string) ($payload['sub'] ?? '');
        if ($sub === '') {
            return response()->json(['message' => 'Invalid Google account.'], 422);
        }

        $name = (string) ($payload['name'] ?? '');
        if ($name === '') {
            $name = Str::before($email, '@');
        }

        $user = User::query()->where('google_id', $sub)->first()
            ?? User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'google_id' => $sub,
                'password' => Hash::make(Str::random(64)),
            ]);
        } else {
            if ($user->google_id === null) {
                $user->google_id = $sub;
            }
            if ($user->name === '' || $user->name === $user->email) {
                $user->name = $name;
            }
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Google authentication successful',
            'token' => $token,
            'user' => $user->fresh()->toSummaryArray(),
        ], 200);
    }
}

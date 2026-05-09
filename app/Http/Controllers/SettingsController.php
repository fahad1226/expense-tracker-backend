<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Support\SupportedCurrencies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $user = request()->user();

        return response()->json([
            'user' => $user->toSummaryArray(),
            'currencies' => SupportedCurrencies::options(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }
        if (array_key_exists('currency', $data)) {
            $user->currency = strtoupper($data['currency']);
        }

        $user->save();

        return response()->json([
            'user' => $user->toSummaryArray(),
            'currencies' => SupportedCurrencies::options(),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->password = Hash::make($request->validated('password'));
        $user->save();

        return response()->json(['message' => 'Password updated']);
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('avatar');
        if (! $file instanceof UploadedFile) {
            return response()->json(['message' => 'Invalid upload'], 422);
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $file->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'user' => $user->fresh()->toSummaryArray(),
            'currencies' => SupportedCurrencies::options(),
        ]);
    }

    public function destroyAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return response()->json([
            'user' => $user->fresh()->toSummaryArray(),
            'currencies' => SupportedCurrencies::options(),
        ]);
    }
}

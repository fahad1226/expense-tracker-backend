<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Support\SupportedCurrencies;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

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
}

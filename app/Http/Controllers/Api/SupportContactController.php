<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (filled(trim((string) $request->input('website', '')))) {
            return response()->json([
                'id' => (string) Str::uuid(),
                'receivedAt' => now()->toIso8601String(),
            ], 201);
        }

        $request->merge([
            'reply_email' => $request->input('reply_email', $request->input('replyEmail')),
        ]);

        $validated = $request->validate([
            'category' => ['sometimes', 'string', 'max:64'],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'reply_email' => ['required', 'email', 'max:254'],
        ], [], [
            'reply_email' => 'reply email',
        ]);

        $userId = User::query()
            ->where('email', $validated['reply_email'])
            ->value('id');

        $ticket = SupportTicket::query()->create([
            'user_id' => $userId,
            'category' => $validated['category'] ?? 'other',
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'reply_email' => $validated['reply_email'],
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return response()->json([
            'id' => (string) $ticket->id,
            'receivedAt' => $ticket->created_at->toIso8601String(),
        ], 201);
    }
}

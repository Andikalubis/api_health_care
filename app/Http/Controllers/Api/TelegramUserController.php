<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\Request;

class TelegramUserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = TelegramUser::with('user');

        if ($user->role->value !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'telegram_chat_id' => 'required|string|max:255',
            'telegram_username' => 'nullable|string|max:100',
        ]);

        if ($request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to set telegram for another user'], 403);
        }

        $telegramUser = TelegramUser::create($validated);
        return response()->json($telegramUser, 201);
    }

    public function show(Request $request, TelegramUser $telegramUser)
    {
        if ($request->user()->role->value !== 'admin' && $telegramUser->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($telegramUser->load('user'));
    }

    public function update(Request $request, TelegramUser $telegramUser)
    {
        if ($request->user()->role->value !== 'admin' && $telegramUser->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'telegram_chat_id' => 'sometimes|required|string|max:255',
            'telegram_username' => 'nullable|string|max:100',
        ]);

        if (isset($validated['user_id']) && $request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to move telegram to another user'], 403);
        }

        $telegramUser->update($validated);
        return response()->json($telegramUser);
    }

    public function destroy(Request $request, TelegramUser $telegramUser)
    {
        if ($request->user()->role->value !== 'admin' && $telegramUser->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $telegramUser->delete();
        return response()->json(null, 204);
    }
}

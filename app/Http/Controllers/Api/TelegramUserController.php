<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\Request;

class TelegramUserController extends Controller
{
    public function index()
    {
        return response()->json(TelegramUser::with('user')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'telegram_chat_id' => 'required|string|max:255',
            'telegram_username' => 'nullable|string|max:100',
        ]);

        $telegramUser = TelegramUser::create($validated);
        return response()->json($telegramUser, 201);
    }

    public function show(TelegramUser $telegramUser)
    {
        return response()->json($telegramUser->load('user'));
    }

    public function update(Request $request, TelegramUser $telegramUser)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'telegram_chat_id' => 'sometimes|required|string|max:255',
            'telegram_username' => 'nullable|string|max:100',
        ]);

        $telegramUser->update($validated);
        return response()->json($telegramUser);
    }

    public function destroy(TelegramUser $telegramUser)
    {
        $telegramUser->delete();
        return response()->json(null, 204);
    }
}

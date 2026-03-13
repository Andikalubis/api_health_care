<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Notification::with('user');

        if ($user->role->value !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:100',
            'message' => 'required|string',
            'notification_type' => 'required|string|max:50',
            'send_time' => 'required|date',
        ]);

        if ($request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to send notification to another user'], 403);
        }

        $notification = Notification::create($validated);
        return response()->json($notification, 201);
    }

    public function show(Request $request, Notification $notification)
    {
        if ($request->user()->role->value !== 'admin' && $notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($notification->load('user'));
    }

    public function update(Request $request, Notification $notification)
    {
        if ($request->user()->role->value !== 'admin' && $notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'title' => 'sometimes|required|string|max:100',
            'message' => 'sometimes|required|string',
            'notification_type' => 'sometimes|required|string|max:50',
            'send_time' => 'sometimes|required|date',
        ]);

        if (isset($validated['user_id']) && $request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized to move notification to another user'], 403);
        }

        $notification->update($validated);
        return response()->json($notification);
    }

    public function destroy(Request $request, Notification $notification)
    {
        if ($request->user()->role->value !== 'admin' && $notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $notification->delete();
        return response()->json(null, 204);
    }
}

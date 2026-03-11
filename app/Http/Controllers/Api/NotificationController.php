<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(Notification::with('user')->get());
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

        $notification = Notification::create($validated);
        return response()->json($notification, 201);
    }

    public function show(Notification $notification)
    {
        return response()->json($notification->load('user'));
    }

    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'title' => 'sometimes|required|string|max:100',
            'message' => 'sometimes|required|string',
            'notification_type' => 'sometimes|required|string|max:50',
            'send_time' => 'sometimes|required|date',
        ]);

        $notification->update($validated);
        return response()->json($notification);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(null, 204);
    }
}

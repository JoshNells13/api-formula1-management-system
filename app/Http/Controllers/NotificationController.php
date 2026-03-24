<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return all notifications for the authenticated user.
     */
    public function index()
    {
        $notifications = Notification::latest()->get();

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read.
     */
    public function read($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'message'      => 'Notification marked as read.',
            'notification' => $notification,
        ]);
    }
}

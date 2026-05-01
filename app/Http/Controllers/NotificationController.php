<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Get user notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', $request->user()->id)
            ->recent();

        // Filter by read status
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->unread();
            } elseif ($request->filter === 'read') {
                $query->read();
            } elseif ($request->filter === 'trashed') {
                $query->onlyTrashed();
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        $notifications = $query->paginate($request->per_page ?? 20)
            ->through(function ($notification) {
                return $this->formatNotification($notification);
            });

        $unreadCount = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->count();

        $trashedCount = Notification::where('user_id', $request->user()->id)
            ->onlyTrashed()
            ->count();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'trashed_count' => $trashedCount,
            'filters' => $request->only(['filter', 'type']),
        ]);
    }

    /**
     * Get notifications for API/dropdown.
     */
    public function latest(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->recent()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return $this->formatNotification($notification);
            });

        $unreadCount = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id, Request $request)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        Notification::markAllAsReadForUser($request->user()->id);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Soft delete a notification.
     */
    public function destroy($id, Request $request)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Restore a soft deleted notification.
     */
    public function restore($id, Request $request)
    {
        $notification = Notification::withTrashed()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->restore();

        return response()->json([
            'success' => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Force delete a notification.
     */
    public function forceDelete($id, Request $request)
    {
        $notification = Notification::withTrashed()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->forceDelete();

        return response()->json([
            'success' => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Format notification for response.
     */
    private function formatNotification($notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'icon' => $notification->icon ?? 'fa-solid fa-bell',
            'color' => $notification->color ?? '#64748b',
            'action_url' => $notification->action_url,
            'read_at' => $notification->read_at,
            'trashed' => $notification->trashed(),
            'created_at' => $notification->created_at->toISOString(),
            'time_ago' => $notification->created_at->diffForHumans(),
        ];
    }
}
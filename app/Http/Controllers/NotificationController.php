<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Notification Controller
 * 
 * Handles all notification operations for authenticated users.
 * Supports: list, latest, mark read, mark all read, soft delete, restore, force delete.
 * 
 * Access: All authenticated users (only their own notifications)
 */
class NotificationController extends Controller
{
    /**
     * Display paginated list of user's notifications.
     * Supports filtering by: read status (unread/read/trashed), type.
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

        // Filter by notification type
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
            'unread_count'  => $unreadCount,
            'trashed_count' => $trashedCount,
            'filters'       => $request->only(['filter', 'type']),
        ]);
    }

    /**
     * Get latest notifications for the dropdown (API endpoint).
     * Returns max 10 recent notifications + unread count.
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
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     * Only the owner can mark their own notifications.
     */
    public function markAsRead($id, Request $request)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->markAsRead();

        return response()->json([
            'success'      => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Mark ALL notifications as read for the current user.
     */
    public function markAllAsRead(Request $request)
    {
        Notification::markAllAsReadForUser($request->user()->id);

        return response()->json([
            'success'      => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Soft delete a notification (move to trash).
     */
    public function destroy($id, Request $request)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->delete();

        return response()->json([
            'success'      => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Restore a soft-deleted notification from trash.
     */
    public function restore($id, Request $request)
    {
        $notification = Notification::withTrashed()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->restore();

        return response()->json([
            'success'      => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Permanently delete a notification (cannot be recovered).
     */
    public function forceDelete($id, Request $request)
    {
        $notification = Notification::withTrashed()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $notification->forceDelete();

        return response()->json([
            'success'      => true,
            'unread_count' => Notification::unreadCountForUser($request->user()->id),
        ]);
    }

    /**
     * Format a notification for API/Inertia response.
     * Ensures consistent data structure across all endpoints.
     */
    private function formatNotification($notification): array
    {
        return [
            'id'         => $notification->id,
            'type'       => $notification->type,
            'title'      => $notification->title,
            'message'    => $notification->message,
            'icon'       => $notification->icon ?? 'fa-solid fa-bell',
            'color'      => $notification->color ?? '#64748b',
            'action_url' => $notification->action_url,
            'read_at'    => $notification->read_at,
            'trashed'    => $notification->trashed(),
            'created_at' => $notification->created_at->toISOString(),
            'time_ago'   => $notification->created_at->diffForHumans(),
        ];
    }
}
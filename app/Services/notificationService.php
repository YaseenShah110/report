<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Notification Service
 * 
 * Centralized service for creating and managing notifications.
 * Handles all notification types: tasks, reports, mentions, system.
 * 
 * Usage: NotificationService::taskCreated($task, $user);
 */
class NotificationService
{
    /**
     * Create a single notification for a user.
     *
     * @param User|int $user - User model or user ID to notify
     * @param string $type - Notification type (e.g., 'task_created', 'report_shared')
     * @param string $title - Short title for the notification
     * @param string $message - Detailed message body
     * @param mixed $notifiable - Related model (Report, Task, etc.) - optional
     * @param string|null $actionUrl - URL to navigate when clicked - optional
     * @param string|null $icon - Font Awesome icon class - optional
     * @param string|null $color - Accent color for the notification - optional
     * @param array|null $data - Additional JSON data - optional
     * @return Notification|null - The created notification or null on failure
     */
    public static function create(
        User|int $user,
        string $type,
        string $title,
        string $message,
        $notifiable = null,
        ?string $actionUrl = null,
        ?string $icon = null,
        ?string $color = null,
        ?array $data = null
    ): ?Notification {
        try {
            // Accept either User model or user ID
            $userId = $user instanceof User ? $user->id : $user;

            return Notification::create([
                'user_id'         => $userId,
                'type'            => $type,
                'title'           => $title,
                'message'         => $message,
                'notifiable_type' => $notifiable ? get_class($notifiable) : null,
                'notifiable_id'   => $notifiable ? $notifiable->id : null,
                'action_url'      => $actionUrl,
                'icon'            => $icon ?? self::getDefaultIcon($type),
                'color'           => $color ?? self::getDefaultColor($type),
                'data'            => $data,
            ]);
        } catch (\Exception $e) {
            // Log error but don't crash the application
            Log::error('Failed to create notification: ' . $e->getMessage(), [
                'user_id' => $userId ?? null,
                'type'    => $type,
                'title'   => $title,
            ]);
            return null;
        }
    }

    /**
     * Notify multiple users at once with the same notification.
     * Uses a single database insert for better performance.
     *
     * @param array $userIds - Array of user IDs to notify
     * @param string $type - Notification type
     * @param string $title - Notification title
     * @param string $message - Notification message
     * @param mixed $notifiable - Related model (optional)
     * @param string|null $actionUrl - Click action URL (optional)
     * @param string|null $icon - Font Awesome icon (optional)
     * @param string|null $color - Accent color (optional)
     * @param array|null $data - Additional data (optional)
     */
    public static function notifyMany(
        array $userIds,
        string $type,
        string $title,
        string $message,
        $notifiable = null,
        ?string $actionUrl = null,
        ?string $icon = null,
        ?string $color = null,
        ?array $data = null
    ): void {
        // Build notification data for each user
        $notifications = array_map(function ($userId) use ($type, $title, $message, $notifiable, $actionUrl, $icon, $color, $data) {
            return [
                'user_id'         => $userId,
                'type'            => $type,
                'title'           => $title,
                'message'         => $message,
                'notifiable_type' => $notifiable ? get_class($notifiable) : null,
                'notifiable_id'   => $notifiable ? $notifiable->id : null,
                'action_url'      => $actionUrl,
                'icon'            => $icon ?? self::getDefaultIcon($type),
                'color'           => $color ?? self::getDefaultColor($type),
                'data'            => json_encode($data),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }, $userIds);

        // Single insert for all notifications (better performance)
        DB::table('notifications')->insert($notifications);
    }

    /**
     * Create notification when a task is created.
     * Notifies the assigned user and all admins.
     *
     * @param mixed $task - The created task
     * @param mixed $assignedTo - User the task is assigned to (optional, defaults to task's assigned_to)
     */
    public static function taskCreated($task, $assignedTo = null): void
    {
        $assignedUser = $assignedTo ?? $task->assigned_to;
        
        // Notify the assigned user
        if ($assignedUser) {
            $userId = $assignedUser instanceof User ? $assignedUser->id : $assignedUser;
            self::create(
                user: $userId,
                type: 'task_created',
                title: 'New Task Assigned',
                message: "Task \"{$task->title}\" has been assigned to you.",
                notifiable: $task,
                actionUrl: route('admin.tasks.my'),
                icon: 'fa-solid fa-tasks',
                color: '#6366f1'  // Indigo
            );
        }

        // Notify all admin users about the new task
        $admins = User::role('admin')->pluck('id')->toArray();
        if (!empty($admins)) {
            self::notifyMany(
                userIds: $admins,
                type: 'task_created',
                title: 'New Task Created',
                message: "Task \"{$task->title}\" has been created by " . auth()->user()->name,
                notifiable: $task,
                actionUrl: route('admin.tasks.index'),
                icon: 'fa-solid fa-tasks',
                color: '#6366f1'
            );
        }
    }

    /**
     * Create notification when a task is completed.
     * Notifies all admin users.
     *
     * @param mixed $task - The completed task
     */
    public static function taskCompleted($task): void
    {
        $admins = User::role('admin')->pluck('id')->toArray();
        if (!empty($admins)) {
            self::notifyMany(
                userIds: $admins,
                type: 'task_completed',
                title: 'Task Completed',
                message: "Task \"{$task->title}\" has been completed by " . auth()->user()->name,
                notifiable: $task,
                actionUrl: route('admin.tasks.index'),
                icon: 'fa-solid fa-check-circle',
                color: '#10b981'  // Emerald
            );
        }
    }

    /**
     * Create notification when a task status is updated.
     *
     * @param mixed $task - The updated task
     * @param string $oldStatus - Previous status
     * @param string $newStatus - New status
     */
    public static function taskStatusChanged($task, string $oldStatus, string $newStatus): void
    {
        // Notify the assigned user about status change (if not self)
        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            self::create(
                user: $task->assigned_to,
                type: 'task_updated',
                title: 'Task Status Updated',
                message: "Task \"{$task->title}\" status changed from \"{$oldStatus}\" to \"{$newStatus}\".",
                notifiable: $task,
                actionUrl: route('admin.tasks.my'),
                icon: 'fa-solid fa-pen-to-square',
                color: '#6366f1'
            );
        }

        // Special notification if task is marked as completed
        if ($newStatus === 'completed') {
            self::taskCompleted($task);
        }
    }

    /**
     * Create notification when a report is assigned to a user.
     *
     * @param mixed $report - The report being assigned
     * @param mixed $assignedTo - User receiving the assignment
     */
    public static function reportAssigned($report, $assignedTo): void
    {
        $userId = $assignedTo instanceof User ? $assignedTo->id : $assignedTo;
        
        self::create(
            user: $userId,
            type: 'report_assigned',
            title: 'Report Assigned to You',
            message: "Report \"{$report->title}\" has been assigned to you by " . auth()->user()->name,
            notifiable: $report,
            actionUrl: route('reports.preview', $report->slug),
            icon: 'fa-solid fa-share-alt',
            color: '#8b5cf6'  // Violet
        );
    }

    /**
     * Create notification when a report is shared publicly.
     *
     * @param mixed $report - The shared report
     * @param mixed $sharedWith - User the report is shared with
     */
    public static function reportShared($report, $sharedWith): void
    {
        $userId = $sharedWith instanceof User ? $sharedWith->id : $sharedWith;
        
        self::create(
            user: $userId,
            type: 'report_shared',
            title: 'Report Shared with You',
            message: "Report \"{$report->title}\" has been shared with you.",
            notifiable: $report,
            actionUrl: route('reports.preview', $report->slug),
            icon: 'fa-solid fa-share-nodes',
            color: '#06b6d4'  // Cyan
        );
    }

    /**
     * Create notification when a report is updated by someone else.
     *
     * @param mixed $report - The updated report
     * @param mixed $updatedBy - User who made the update (optional, defaults to auth user)
     */
    public static function reportUpdated($report, $updatedBy = null): void
    {
        $updater = $updatedBy ?? auth()->user();
        
        // Notify the report owner if someone else updated it
        if ($report->user_id !== $updater->id) {
            self::create(
                user: $report->user_id,
                type: 'report_updated',
                title: 'Report Updated',
                message: "Your report \"{$report->title}\" was updated by {$updater->name}.",
                notifiable: $report,
                actionUrl: route('reports.preview', $report->slug),
                icon: 'fa-solid fa-pen-to-square',
                color: '#f59e0b'  // Amber
            );
        }
    }

    /**
     * Create notification when a user is mentioned in a report or comment.
     *
     * @param mixed $mentionedUser - User who was mentioned
     * @param mixed $report - Report where mention occurred (optional)
     * @param mixed $comment - Comment where mention occurred (optional)
     */
    public static function userMentioned($mentionedUser, $report = null, $comment = null): void
    {
        $userId = $mentionedUser instanceof User ? $mentionedUser->id : $mentionedUser;
        
        self::create(
            user: $userId,
            type: 'user_mentioned',
            title: 'You Were Mentioned',
            message: auth()->user()->name . " mentioned you" . ($report ? " in report \"{$report->title}\"" : "") . ".",
            notifiable: $report,
            actionUrl: $report ? route('reports.preview', $report->slug) : null,
            icon: 'fa-solid fa-at',
            color: '#ec4899'  // Pink
        );
    }

    /**
     * Soft delete all notifications for a specific notifiable entity.
     * Used when a report or task is soft-deleted.
     *
     * @param mixed $notifiable - The related model (Report, Task, etc.)
     */
    public static function deleteForNotifiable($notifiable): void
    {
        Notification::where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->delete();
    }

    /**
     * Restore all notifications for a specific notifiable entity.
     * Used when a report or task is restored from trash.
     *
     * @param mixed $notifiable - The related model (Report, Task, etc.)
     */
    public static function restoreForNotifiable($notifiable): void
    {
        Notification::withTrashed()
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->restore();
    }

    /**
     * Force delete all notifications for a specific notifiable entity.
     * Used when a report or task is permanently deleted.
     *
     * @param mixed $notifiable - The related model (Report, Task, etc.)
     */
    public static function forceDeleteForNotifiable($notifiable): void
    {
        Notification::withTrashed()
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->forceDelete();
    }

    /**
     * Get default icon based on notification type.
     * Each notification type has a specific Font Awesome icon.
     *
     * @param string $type - Notification type
     * @return string - Font Awesome icon class
     */
    private static function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'task_created'    => 'fa-solid fa-tasks',
            'task_completed'  => 'fa-solid fa-check-circle',
            'task_updated'    => 'fa-solid fa-pen-to-square',
            'task_deleted'    => 'fa-solid fa-trash',
            'task_restored'   => 'fa-solid fa-rotate-left',
            'report_assigned' => 'fa-solid fa-share-alt',
            'report_shared'   => 'fa-solid fa-share-nodes',
            'report_created'  => 'fa-solid fa-file-pen',
            'report_updated'  => 'fa-solid fa-pen-to-square',
            'report_deleted'  => 'fa-solid fa-trash',
            'report_restored' => 'fa-solid fa-rotate-left',
            'user_mentioned'  => 'fa-solid fa-at',
            'system'          => 'fa-solid fa-gear',
            default           => 'fa-solid fa-bell',
        };
    }

    /**
     * Get default color based on notification type.
     * Each notification type has a specific accent color.
     *
     * @param string $type - Notification type
     * @return string - Hex color code
     */
    private static function getDefaultColor(string $type): string
    {
        return match ($type) {
            'task_created', 'task_updated'   => '#6366f1',  // Indigo
            'task_completed'                 => '#10b981',  // Emerald
            'task_deleted'                   => '#ef4444',  // Red
            'task_restored'                  => '#f59e0b',  // Amber
            'report_assigned'                => '#8b5cf6',  // Violet
            'report_shared'                  => '#06b6d4',  // Cyan
            'report_created'                 => '#f59e0b',  // Amber
            'report_updated'                 => '#3b82f6',  // Blue
            'report_deleted'                 => '#ef4444',  // Red
            'report_restored'                => '#10b981',  // Emerald
            'user_mentioned'                 => '#ec4899',  // Pink
            'system'                         => '#64748b',  // Slate
            default                          => '#64748b',  // Slate
        };
    }
}
<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a new notification.
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
            $userId = $user instanceof User ? $user->id : $user;

            return Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'notifiable_type' => $notifiable ? get_class($notifiable) : null,
                'notifiable_id' => $notifiable ? $notifiable->id : null,
                'action_url' => $actionUrl,
                'icon' => $icon ?? self::getDefaultIcon($type),
                'color' => $color ?? self::getDefaultColor($type),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify multiple users at once.
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
        $notifications = array_map(function ($userId) use ($type, $title, $message, $notifiable, $actionUrl, $icon, $color, $data) {
            return [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'notifiable_type' => $notifiable ? get_class($notifiable) : null,
                'notifiable_id' => $notifiable ? $notifiable->id : null,
                'action_url' => $actionUrl,
                'icon' => $icon ?? self::getDefaultIcon($type),
                'color' => $color ?? self::getDefaultColor($type),
                'data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $userIds);

        DB::table('notifications')->insert($notifications);
    }

    /**
     * Create notification when a task is created.
     */
    public static function taskCreated($task, $assignedTo = null): void
    {
        $assignedUser = $assignedTo ?? $task->assigned_to;
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
                color: '#6366f1'
            );
        }

        // Notify admins
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
                color: '#10b981'
            );
        }
    }

    /**
     * Create notification when a report is assigned.
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
            color: '#8b5cf6'
        );
    }

    /**
     * Create notification when a report is shared.
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
            color: '#06b6d4'
        );
    }

    /**
     * Create notification when a report is updated.
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
                color: '#f59e0b'
            );
        }
    }

    /**
     * Create notification when a user is mentioned.
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
            color: '#ec4899'
        );
    }

    /**
     * Soft delete all notifications for a notifiable entity.
     */
    public static function deleteForNotifiable($notifiable): void
    {
        Notification::where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->delete();
    }

    /**
     * Restore all notifications for a notifiable entity.
     */
    public static function restoreForNotifiable($notifiable): void
    {
        Notification::withTrashed()
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->restore();
    }

    /**
     * Force delete all notifications for a notifiable entity.
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
     */
    private static function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'task_created' => 'fa-solid fa-tasks',
            'task_completed' => 'fa-solid fa-check-circle',
            'task_updated' => 'fa-solid fa-pen-to-square',
            'task_deleted' => 'fa-solid fa-trash',
            'task_restored' => 'fa-solid fa-rotate-left',
            'report_assigned' => 'fa-solid fa-share-alt',
            'report_shared' => 'fa-solid fa-share-nodes',
            'report_created' => 'fa-solid fa-file-pen',
            'report_updated' => 'fa-solid fa-pen-to-square',
            'report_deleted' => 'fa-solid fa-trash',
            'report_restored' => 'fa-solid fa-rotate-left',
            'user_mentioned' => 'fa-solid fa-at',
            'system' => 'fa-solid fa-gear',
            default => 'fa-solid fa-bell',
        };
    }

    /**
     * Get default color based on notification type.
     */
    private static function getDefaultColor(string $type): string
    {
        return match ($type) {
            'task_created', 'task_updated' => '#6366f1', // Indigo
            'task_completed' => '#10b981', // Emerald
            'task_deleted' => '#ef4444', // Red
            'task_restored' => '#f59e0b', // Amber
            'report_assigned' => '#8b5cf6', // Violet
            'report_shared' => '#06b6d4', // Cyan
            'report_created' => '#f59e0b', // Amber
            'report_updated' => '#3b82f6', // Blue
            'report_deleted' => '#ef4444', // Red
            'report_restored' => '#10b981', // Emerald
            'user_mentioned' => '#ec4899', // Pink
            'system' => '#64748b', // Slate
            default => '#64748b', // Slate
        };
    }
}
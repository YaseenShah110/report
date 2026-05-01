<?php
// app/Http/Controllers/Admin/TaskController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Report;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks (Admin view)
     */
    public function index(Request $request)
    {
        $tasks = Task::with(['assignedTo', 'assignedBy', 'report'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->trashed, fn($q) => $q->onlyTrashed())
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'created_at' => $task->created_at,
                'deleted_at' => $task->deleted_at,
                'trashed' => $task->trashed(),
                'assigned_to' => $task->assignedTo ? [
                    'id' => $task->assignedTo->id,
                    'name' => $task->assignedTo->name,
                ] : null,
                'assigned_by' => $task->assignedBy ? [
                    'id' => $task->assignedBy->id,
                    'name' => $task->assignedBy->name,
                ] : null,
                'report' => $task->report ? [
                    'id' => $task->report->id,
                    'title' => $task->report->title,
                    'slug' => $task->report->slug,
                ] : null,
            ]);

        $users = User::all(['id', 'name']);
        $stats = [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed' => Task::where('status', 'completed')->count(),
            'overdue' => Task::where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
            'trashed' => Task::onlyTrashed()->count(),
        ];

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'search', 'sort', 'direction', 'trashed'])
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $users = User::all(['id', 'name']);
        $reports = Report::where('user_id', auth()->id())
            ->orWhereHas('assignments', fn($q) => $q->where('user_id', auth()->id()))
            ->get(['id', 'title', 'slug']);
            
        return Inertia::render('Admin/Tasks/Create', [
            'users' => $users,
            'reports' => $reports
        ]);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
            'report_id' => 'nullable|exists:reports,id',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_by' => auth()->id(),
            'assigned_to' => $request->assigned_to,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'report_id' => $request->report_id,
            'status' => 'pending',
        ]);

        // Log activity
        UserActivity::log(auth()->id(), 'task_created', 'task', $task->id, [
            'task_title' => $task->title,
            'assigned_to' => $task->assignedTo->name
        ]);

        // 🔔 Create notification for assigned user
        NotificationService::taskCreated($task, $request->assigned_to);

        return redirect()->route('admin.tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task (View single task details)
     */
    public function show(Task $task)
    {
        // Load relationships
        $task->load(['assignedTo', 'assignedBy', 'report']);
        
        // Get task activity logs
        $activities = UserActivity::where('entity_type', 'task')
            ->where('entity_id', $task->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($activity) => [
                'action' => $activity->action,
                'user_name' => $activity->user->name,
                'created_at' => $activity->created_at,
                'details' => $activity->details,
            ]);
        
        // Get related tasks
        $relatedTasks = Task::where('assigned_to', $task->assigned_to)
            ->where('id', '!=', $task->id)
            ->limit(5)
            ->get(['id', 'title', 'status', 'priority', 'due_date']);
        
        return Inertia::render('Admin/Tasks/Show', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'completed_at' => $task->completed_at,
                'completion_notes' => $task->completion_notes,
                'deleted_at' => $task->deleted_at,
                'trashed' => $task->trashed(),
                'assigned_to' => $task->assignedTo ? [
                    'id' => $task->assignedTo->id,
                    'name' => $task->assignedTo->name,
                    'email' => $task->assignedTo->email,
                    'avatar' => $task->assignedTo->avatar ?? null,
                ] : null,
                'assigned_by' => $task->assignedBy ? [
                    'id' => $task->assignedBy->id,
                    'name' => $task->assignedBy->name,
                ] : null,
                'report' => $task->report ? [
                    'id' => $task->report->id,
                    'title' => $task->report->title,
                    'slug' => $task->report->slug,
                    'status' => $task->report->status,
                ] : null,
            ],
            'activities' => $activities,
            'relatedTasks' => $relatedTasks,
        ]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        $users = User::all(['id', 'name']);
        $reports = Report::all(['id', 'title', 'slug']);
            
        return Inertia::render('Admin/Tasks/Edit', [
            'task' => $task,
            'users' => $users,
            'reports' => $reports
        ]);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,overdue',
            'due_date' => 'nullable|date',
            'report_id' => 'nullable|exists:reports,id',
        ]);

        $oldStatus = $task->status;
        $oldAssignee = $task->assigned_to;
        
        $task->update($request->only([
            'title', 'description', 'assigned_to', 'priority', 
            'status', 'due_date', 'report_id'
        ]));

        // Set completed_at if status changed to completed
        if ($request->status === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        // Log status change
        if ($oldStatus !== $request->status) {
            UserActivity::log(auth()->id(), 'task_status_changed', 'task', $task->id, [
                'task_title' => $task->title,
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            // 🔔 Notify if task is completed
            if ($request->status === 'completed') {
                NotificationService::taskCompleted($task);
            }
        }

        // 🔔 Notify if assigned user changed
        if ($oldAssignee != $request->assigned_to) {
            NotificationService::create(
                user: $request->assigned_to,
                type: 'task_assigned',
                title: 'Task Reassigned to You',
                message: "Task \"{$task->title}\" has been reassigned to you by " . auth()->user()->name,
                notifiable: $task,
                actionUrl: route('admin.tasks.show', $task->id),
                icon: 'fa-solid fa-user-plus',
                color: '#3b82f6'
            );
        }

        // Log general update
        UserActivity::log(auth()->id(), 'task_updated', 'task', $task->id, [
            'task_title' => $task->title,
            'changes' => $request->only(['title', 'status', 'priority'])
        ]);

        return redirect()->route('admin.tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task from storage (Soft Delete).
     */
    public function destroy(Task $task)
    {
        UserActivity::log(auth()->id(), 'task_deleted', 'task', $task->id, [
            'task_title' => $task->title
        ]);

        // 🔔 Notify assigned user that task was deleted
        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            NotificationService::create(
                user: $task->assigned_to,
                type: 'task_deleted',
                title: 'Task Deleted',
                message: "Task \"{$task->title}\" has been deleted by " . auth()->user()->name,
                notifiable: $task,
                actionUrl: route('admin.tasks.my'),
                icon: 'fa-solid fa-trash',
                color: '#ef4444'
            );
        }

        // Soft delete the task
        $task->delete();

        // 🔔 Soft delete related notifications
        NotificationService::deleteForNotifiable($task);

        return redirect()->route('admin.tasks.index')->with('success', 'Task moved to trash successfully.');
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();

        // 🔔 Restore related notifications
        NotificationService::restoreForNotifiable($task);

        // Log activity
        UserActivity::log(auth()->id(), 'task_restored', 'task', $task->id, [
            'task_title' => $task->title
        ]);

        // 🔔 Notify assigned user that task was restored
        if ($task->assigned_to) {
            NotificationService::create(
                user: $task->assigned_to,
                type: 'task_restored',
                title: 'Task Restored',
                message: "Task \"{$task->title}\" has been restored.",
                notifiable: $task,
                actionUrl: route('admin.tasks.show', $task->id),
                icon: 'fa-solid fa-rotate-left',
                color: '#f59e0b'
            );
        }

        return redirect()->route('admin.tasks.index')->with('success', 'Task restored successfully.');
    }

    /**
     * Force delete a task permanently.
     */
    public function forceDelete($id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        // Log activity before deleting
        UserActivity::log(auth()->id(), 'task_force_deleted', 'task', $task->id, [
            'task_title' => $task->title
        ]);

        // 🔔 Force delete related notifications
        NotificationService::forceDeleteForNotifiable($task);

        // Permanently delete the task
        $task->forceDelete();

        return redirect()->route('admin.tasks.index')->with('success', 'Task permanently deleted.');
    }

    /**
     * Display trashed tasks.
     */
    public function trashed(Request $request)
    {
        $tasks = Task::onlyTrashed()
            ->with(['assignedTo', 'assignedBy', 'report'])
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderBy('deleted_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'created_at' => $task->created_at,
                'deleted_at' => $task->deleted_at,
                'trashed' => true,
                'assigned_to' => $task->assignedTo ? [
                    'id' => $task->assignedTo->id,
                    'name' => $task->assignedTo->name,
                ] : null,
                'assigned_by' => $task->assignedBy ? [
                    'id' => $task->assignedBy->id,
                    'name' => $task->assignedBy->name,
                ] : null,
            ]);

        return Inertia::render('Admin/Tasks/Trashed', [
            'tasks' => $tasks,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Update task status (AJAX call)
     */
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'completion_notes' => 'nullable|string'
        ]);

        $oldStatus = $task->status;
        
        $task->update([
            'status' => $request->status,
            'completion_notes' => $request->completion_notes,
            'completed_at' => $request->status === 'completed' ? now() : null
        ]);

        // Log status change
        UserActivity::log(auth()->id(), 'task_status_changed', 'task', $task->id, [
            'task_title' => $task->title,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'completion_notes' => $request->completion_notes
        ]);

        // 🔔 Notify if task is completed
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            NotificationService::taskCompleted($task);
        }

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => [
                'id' => $task->id,
                'status' => $task->status,
                'completed_at' => $task->completed_at,
            ]
        ]);
    }

    /**
     * Get tasks assigned to the authenticated user (for regular users)
     */
    public function myTasks(Request $request)
    {
        $user = auth()->user();
        
        $tasks = Task::with(['assignedBy', 'report'])
            ->where('assigned_to', $user->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderByRaw("CASE WHEN status = 'overdue' THEN 0 WHEN status = 'pending' THEN 1 WHEN status = 'in_progress' THEN 2 ELSE 3 END")
            ->orderBy('due_date', 'asc')
            ->paginate(12)
            ->withQueryString()
            ->through(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'status' => $task->isOverdue() ? 'overdue' : $task->status,
                'due_date' => $task->due_date,
                'assigned_by' => $task->assignedBy?->name,
                'report' => $task->report ? [
                    'id' => $task->report->id,
                    'title' => $task->report->title,
                    'slug' => $task->report->slug,
                ] : null,
            ]);
        
        $stats = [
            'pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
            'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'completed' => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
            'overdue' => Task::where('assigned_to', $user->id)
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
        ];
        
        return Inertia::render('Tasks/MyTasks', [
            'tasks' => $tasks,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'priority'])
        ]);
    }

    /**
     * Get task statistics for dashboard
     */
    public function getStats()
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            $stats = [
                'total' => Task::count(),
                'pending' => Task::where('status', 'pending')->count(),
                'in_progress' => Task::where('status', 'in_progress')->count(),
                'completed' => Task::where('status', 'completed')->count(),
                'overdue' => Task::where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
                'trashed' => Task::onlyTrashed()->count(),
                'completion_rate' => round((Task::where('status', 'completed')->count() / max(Task::count(), 1)) * 100),
            ];
        } else {
            $stats = [
                'total' => Task::where('assigned_to', $user->id)->count(),
                'pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
                'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
                'completed' => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
                'overdue' => Task::where('assigned_to', $user->id)
                    ->where('status', '!=', 'completed')
                    ->where('due_date', '<', now())
                    ->count(),
                'completion_rate' => round((Task::where('assigned_to', $user->id)->where('status', 'completed')->count() / max(Task::where('assigned_to', $user->id)->count(), 1)) * 100),
            ];
        }
        
        // Get tasks by priority
        $byPriority = [
            'low' => Task::where('priority', 'low')->count(),
            'medium' => Task::where('priority', 'medium')->count(),
            'high' => Task::where('priority', 'high')->count(),
            'urgent' => Task::where('priority', 'urgent')->count(),
        ];
        
        return response()->json([
            'stats' => $stats,
            'by_priority' => $byPriority,
        ]);
    }

    /**
     * Bulk delete tasks (Soft Delete)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        $deletedCount = 0;
        foreach ($request->task_ids as $taskId) {
            $task = Task::find($taskId);
            if ($task) {
                // Log activity
                UserActivity::log(auth()->id(), 'task_deleted', 'task', $task->id, [
                    'task_title' => $task->title,
                    'bulk_delete' => true
                ]);

                // 🔔 Notify assigned user
                if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
                    NotificationService::create(
                        user: $task->assigned_to,
                        type: 'task_deleted',
                        title: 'Task Deleted',
                        message: "Task \"{$task->title}\" has been deleted.",
                        notifiable: $task,
                        actionUrl: route('admin.tasks.my'),
                        icon: 'fa-solid fa-trash',
                        color: '#ef4444'
                    );
                }

                // Soft delete
                $task->delete();
                
                // 🔔 Soft delete related notifications
                NotificationService::deleteForNotifiable($task);
                
                $deletedCount++;
            }
        }

        return response()->json([
            'message' => "{$deletedCount} tasks deleted successfully",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Bulk assign tasks to user
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignedCount = 0;
        foreach ($request->task_ids as $taskId) {
            $task = Task::find($taskId);
            if ($task) {
                $oldAssignee = $task->assigned_to;
                $task->update(['assigned_to' => $request->assigned_to]);

                // 🔔 Notify new assignee
                if ($request->assigned_to !== $oldAssignee) {
                    NotificationService::create(
                        user: $request->assigned_to,
                        type: 'task_assigned',
                        title: 'New Task Assigned',
                        message: "Task \"{$task->title}\" has been assigned to you.",
                        notifiable: $task,
                        actionUrl: route('admin.tasks.show', $task->id),
                        icon: 'fa-solid fa-tasks',
                        color: '#6366f1'
                    );
                }

                $assignedCount++;
            }
        }

        return response()->json([
            'message' => "{$assignedCount} tasks assigned successfully",
            'assigned_count' => $assignedCount
        ]);
    }

    /**
     * Bulk update task status
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $updatedCount = 0;
        foreach ($request->task_ids as $taskId) {
            $task = Task::find($taskId);
            if ($task && $task->status !== $request->status) {
                $oldStatus = $task->status;
                
                $task->update([
                    'status' => $request->status,
                    'completed_at' => $request->status === 'completed' ? now() : null
                ]);

                // 🔔 Notify if completed
                if ($request->status === 'completed') {
                    NotificationService::taskCompleted($task);
                }

                $updatedCount++;
            }
        }

        return response()->json([
            'message' => "{$updatedCount} tasks updated successfully",
            'updated_count' => $updatedCount
        ]);
    }

    /**
     * Search tasks (AJAX)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $tasks = Task::with(['assignedTo', 'assignedBy'])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'assigned_to_name' => $task->assignedTo?->name,
                'trashed' => $task->trashed(),
            ]);
        
        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Export tasks to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            $tasks = Task::with(['assignedTo', 'assignedBy'])
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->get();
        } else {
            $tasks = Task::with(['assignedBy'])
                ->where('assigned_to', $user->id)
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->get();
        }
        
        $filename = 'tasks_' . now()->format('Y-m-d_His') . '.csv';
        
        $callback = function() use ($tasks) {
            $handle = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($handle, ['ID', 'Title', 'Description', 'Priority', 'Status', 'Assigned To', 'Assigned By', 'Due Date', 'Created At', 'Completed At']);
            
            // Add data rows
            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->id,
                    $task->title,
                    $task->description,
                    $task->priority,
                    $task->status,
                    $task->assignedTo?->name ?? 'N/A',
                    $task->assignedBy?->name ?? 'N/A',
                    $task->due_date?->format('Y-m-d') ?? 'N/A',
                    $task->created_at->format('Y-m-d H:i:s'),
                    $task->completed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ]);
            }
            
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export tasks to PDF
     */
    public function exportPdf(Request $request)
    {
        // You can implement PDF export using packages like barryvdh/laravel-dompdf
        // or spatie/laravel-pdf
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            $tasks = Task::with(['assignedTo', 'assignedBy'])->get();
        } else {
            $tasks = Task::with(['assignedBy'])
                ->where('assigned_to', $user->id)
                ->get();
        }
        
        // Placeholder for PDF export
        // $pdf = PDF::loadView('exports.tasks', compact('tasks'));
        // return $pdf->download('tasks_' . now()->format('Y-m-d') . '.pdf');
        
        return back()->with('info', 'PDF export feature coming soon.');
    }

    /**
     * Export tasks to Excel
     */
    public function exportExcel(Request $request)
    {
        // You can implement Excel export using packages like maatwebsite/excel
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            $tasks = Task::with(['assignedTo', 'assignedBy'])->get();
        } else {
            $tasks = Task::with(['assignedBy'])
                ->where('assigned_to', $user->id)
                ->get();
        }
        
        // Placeholder for Excel export
        // return Excel::download(new TasksExport($tasks), 'tasks_' . now()->format('Y-m-d') . '.xlsx');
        
        return back()->with('info', 'Excel export feature coming soon.');
    }
}
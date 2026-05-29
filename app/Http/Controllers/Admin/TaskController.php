<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Report;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Task Management Controller
 *
 * Handles all task CRUD operations for administrators and regular users.
 * Supports soft deletes, bulk operations, status updates, and export.
 *
 * Access: Admin/Manager for all tasks, Regular users for their own tasks
 *
 * Export methods:
 *   - export()         → Admin: all tasks with full filters + sort (GET /admin/tasks/export)
 *   - exportMyTasks()  → User: only tasks assigned to auth user   (GET /my-tasks/export)
 */
class TaskController extends Controller
{
    // =========================================================================
    // LISTING
    // =========================================================================

    /**
     * Display paginated list of ALL tasks (Admin view).
     */
    public function index(Request $request)
    {
        $now = now();

        $query = Task::with(['assignedTo', 'assignedBy', 'report'])
            ->when($request->priority,    fn ($q) => $q->where('priority',    $request->priority))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->search,      fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->trashed,     fn ($q) => $q->onlyTrashed());

        // ── Status filter — "overdue" is a computed state, not a stored DB value.
        // The DB stores 'pending' / 'in_progress' / 'completed'.
        // Overdue = status != 'completed' AND due_date < now AND NOT trashed.
        // Pending/In-Progress must EXCLUDE overdue tasks so no task is double-counted.
        if ($request->status === 'overdue') {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', $now);
        } elseif ($request->status === 'pending') {
            $query->where('status', 'pending')
                  ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now));
        } elseif ($request->status === 'in_progress') {
            $query->where('status', 'in_progress')
                  ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now));
        } elseif ($request->status === 'completed') {
            $query->where('status', 'completed');
        }
        // status='' → no filter, show all active tasks

        $tasks = $query
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($task) use ($now) {
                // is_overdue = computed flag — true when due_date is past and NOT completed/trashed
                $isOverdue = ! $task->trashed()
                    && $task->due_date
                    && $task->due_date < $now
                    && $task->status !== 'completed';

                return [
                    'id'          => $task->id,
                    'title'       => $task->title,
                    'description' => $task->description,
                    'priority'    => $task->priority,
                    'status'      => $task->status,   // always the real stored status
                    'is_overdue'  => $isOverdue,       // frontend uses this for display/badge
                    'due_date'    => $task->due_date,
                    'created_at'  => $task->created_at,
                    'deleted_at'  => $task->deleted_at,
                    'trashed'     => $task->trashed(),
                    'assigned_to' => $task->assignedTo ? [
                        'id'   => $task->assignedTo->id,
                        'name' => $task->assignedTo->name,
                    ] : null,
                    'assigned_by' => $task->assignedBy ? [
                        'id'   => $task->assignedBy->id,
                        'name' => $task->assignedBy->name,
                    ] : null,
                    'report' => $task->report ? [
                        'id'    => $task->report->id,
                        'title' => $task->report->title,
                        'slug'  => $task->report->slug,
                    ] : null,
                ];
            });

        $users = User::all(['id', 'name']);

        // ── Stats: pending/in_progress EXCLUDE overdue tasks (no double-counting)
        $stats = [
            'total'       => Task::count(),
            'pending'     => Task::where('status', 'pending')
                                 ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now))
                                 ->count(),
            'in_progress' => Task::where('status', 'in_progress')
                                 ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now))
                                 ->count(),
            'completed'   => Task::where('status', 'completed')->count(),
            'overdue'     => Task::where('status', '!=', 'completed')
                                 ->whereNotNull('due_date')
                                 ->where('due_date', '<', $now)
                                 ->count(),
            'trashed'     => Task::onlyTrashed()->count(),
        ];

        return Inertia::render('Admin/Tasks/Index', [
            'tasks'   => $tasks,
            'users'   => $users,
            'stats'   => $stats,
            'filters' => $request->only([
                'status', 'priority', 'assigned_to',
                'search', 'sort', 'direction', 'trashed',
            ]),
        ]);
    }

    // =========================================================================
    // CREATE / STORE
    // =========================================================================

    public function create()
    {
        $users   = User::all(['id', 'name']);
        $reports = Report::where('user_id', auth()->id())
            ->orWhereHas('assignments', fn ($q) => $q->where('user_id', auth()->id()))
            ->get(['id', 'title', 'slug']);

        return Inertia::render('Admin/Tasks/Create', [
            'users'   => $users,
            'reports' => $reports,
        ]);
    }

    /**
     * Store a new task.
     * ✅ due_date allows today and future times (after_or_equal:now)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'priority'    => 'required|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date|after_or_equal:now',
            'report_id'   => 'nullable|exists:reports,id',
        ]);

        $task = Task::create([
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_by' => auth()->id(),
            'assigned_to' => $request->assigned_to,
            'priority'    => $request->priority,
            'due_date'    => $request->due_date,
            'report_id'   => $request->report_id,
            'status'      => 'pending',
        ]);

        UserActivity::log(auth()->id(), 'task_created', 'task', $task->id, [
            'task_title'  => $task->title,
            'assigned_to' => $task->assignedTo->name,
        ]);

        NotificationService::taskCreated($task, $request->assigned_to);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    // =========================================================================
    // SHOW / EDIT / UPDATE
    // =========================================================================

    public function show(Task $task)
    {
        $task->load(['assignedTo', 'assignedBy', 'report']);

        $activities = UserActivity::where('entity_type', 'task')
            ->where('entity_id', $task->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn ($activity) => [
                'action'     => $activity->action,
                'user_name'  => $activity->user->name,
                'created_at' => $activity->created_at,
                'details'    => $activity->details,
            ]);

        $relatedTasks = Task::where('assigned_to', $task->assigned_to)
            ->where('id', '!=', $task->id)
            ->limit(5)
            ->get(['id', 'title', 'status', 'priority', 'due_date']);

        return Inertia::render('Admin/Tasks/Show', [
            'task' => [
                'id'               => $task->id,
                'title'            => $task->title,
                'description'      => $task->description,
                'priority'         => $task->priority,
                'status'           => $task->status,
                'due_date'         => $task->due_date,
                'created_at'       => $task->created_at,
                'updated_at'       => $task->updated_at,
                'completed_at'     => $task->completed_at,
                'completion_notes' => $task->completion_notes,
                'deleted_at'       => $task->deleted_at,
                'trashed'          => $task->trashed(),
                'assigned_to' => $task->assignedTo ? [
                    'id'    => $task->assignedTo->id,
                    'name'  => $task->assignedTo->name,
                    'email' => $task->assignedTo->email,
                ] : null,
                'assigned_by' => $task->assignedBy ? [
                    'id'   => $task->assignedBy->id,
                    'name' => $task->assignedBy->name,
                ] : null,
                'report' => $task->report ? [
                    'id'     => $task->report->id,
                    'title'  => $task->report->title,
                    'slug'   => $task->report->slug,
                    'status' => $task->report->status,
                ] : null,
            ],
            'activities'   => $activities,
            'relatedTasks' => $relatedTasks,
        ]);
    }

    public function edit(Task $task)
    {
        $users   = User::all(['id', 'name']);
        $reports = Report::all(['id', 'title', 'slug']);

        return Inertia::render('Admin/Tasks/Edit', [
            'task'    => $task,
            'users'   => $users,
            'reports' => $reports,
        ]);
    }

    /**
     * Update task details.
     * ✅ due_date allows today and future times (after_or_equal:now)
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'priority'    => 'required|in:low,medium,high,urgent',
            'status'      => 'required|in:pending,in_progress,completed,overdue',
            'due_date'    => 'nullable|date|after_or_equal:now',
            'report_id'   => 'nullable|exists:reports,id',
        ]);

        $oldStatus   = $task->status;
        $oldAssignee = $task->assigned_to;

        $task->update($request->only([
            'title', 'description', 'assigned_to', 'priority',
            'status', 'due_date', 'report_id',
        ]));

        if ($request->status === 'completed' && ! $task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        if ($oldStatus !== $request->status) {
            UserActivity::log(auth()->id(), 'task_status_changed', 'task', $task->id, [
                'task_title' => $task->title,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ]);

            if ($request->status === 'completed') {
                NotificationService::taskCompleted($task);
            }
        }

        if ($oldAssignee != $request->assigned_to) {
            NotificationService::create(
                user:      $request->assigned_to,
                type:      'task_assigned',
                title:     'Task Reassigned to You',
                message:   "Task \"{$task->title}\" has been reassigned to you by " . auth()->user()->name,
                notifiable: $task,
                actionUrl: route('admin.tasks.show', $task->id),
                icon:      'fa-solid fa-user-plus',
                color:     '#3b82f6'
            );
        }

        UserActivity::log(auth()->id(), 'task_updated', 'task', $task->id, [
            'task_title' => $task->title,
            'changes'    => $request->only(['title', 'status', 'priority']),
        ]);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    // =========================================================================
    // DELETE / RESTORE / FORCE DELETE
    // =========================================================================

    public function destroy(Task $task)
    {
        UserActivity::log(auth()->id(), 'task_deleted', 'task', $task->id, [
            'task_title' => $task->title,
        ]);

        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            NotificationService::create(
                user:      $task->assigned_to,
                type:      'task_deleted',
                title:     'Task Deleted',
                message:   "Task \"{$task->title}\" has been deleted by " . auth()->user()->name,
                notifiable: $task,
                actionUrl: route('my-tasks.index'),
                icon:      'fa-solid fa-trash',
                color:     '#ef4444'
            );
        }

        NotificationService::deleteForNotifiable($task);
        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task moved to trash successfully.');
    }

    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();

        NotificationService::restoreForNotifiable($task);

        UserActivity::log(auth()->id(), 'task_restored', 'task', $task->id, [
            'task_title' => $task->title,
        ]);

        if ($task->assigned_to) {
            NotificationService::create(
                user:      $task->assigned_to,
                type:      'task_restored',
                title:     'Task Restored',
                message:   "Task \"{$task->title}\" has been restored.",
                notifiable: $task,
                actionUrl: route('admin.tasks.show', $task->id),
                icon:      'fa-solid fa-rotate-left',
                color:     '#f59e0b'
            );
        }

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task restored successfully.');
    }

    public function forceDelete($id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        UserActivity::log(auth()->id(), 'task_force_deleted', 'task', $task->id, [
            'task_title' => $task->title,
        ]);

        NotificationService::forceDeleteForNotifiable($task);
        $task->forceDelete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task permanently deleted.');
    }

    public function trashed(Request $request)
    {
        $tasks = Task::onlyTrashed()
            ->with(['assignedTo', 'assignedBy', 'report'])
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderBy('deleted_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($task) => [
                'id'          => $task->id,
                'title'       => $task->title,
                'description' => $task->description,
                'priority'    => $task->priority,
                'status'      => $task->status,
                'due_date'    => $task->due_date,
                'deleted_at'  => $task->deleted_at,
                'assigned_to' => $task->assignedTo ? [
                    'id'   => $task->assignedTo->id,
                    'name' => $task->assignedTo->name,
                ] : null,
                'assigned_by' => $task->assignedBy ? [
                    'id'   => $task->assignedBy->id,
                    'name' => $task->assignedBy->name,
                ] : null,
            ]);

        return Inertia::render('Admin/Tasks/Trashed', [
            'tasks'   => $tasks,
            'filters' => $request->only(['search']),
        ]);
    }

    // =========================================================================
    // STATUS UPDATE
    // =========================================================================

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status'           => 'required|in:pending,in_progress,completed',
            'completion_notes' => 'nullable|string',
        ]);

        $oldStatus = $task->status;

        $task->update([
            'status'           => $request->status,
            'completion_notes' => $request->completion_notes,
            'completed_at'     => $request->status === 'completed' ? now() : null,
        ]);

        UserActivity::log(auth()->id(), 'task_status_changed', 'task', $task->id, [
            'task_title'       => $task->title,
            'old_status'       => $oldStatus,
            'new_status'       => $request->status,
            'completion_notes' => $request->completion_notes,
        ]);

        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            NotificationService::taskCompleted($task);
        }

        return redirect()->back();
    }

    // =========================================================================
    // MY TASKS (Current User)
    // =========================================================================

    public function myTasks(Request $request)
    {
        $user  = auth()->user();
        $query = Task::with(['assignedBy', 'report'])
            ->where('assigned_to', $user->id);

        // Apply status filter with special trashed / overdue handling
        if ($request->status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($request->status === 'overdue') {
            $query->where(function ($q) {
                $q->where('status', 'overdue')
                    ->orWhere(function ($sub) {
                        $sub->where('status', '!=', 'completed')
                            ->where('due_date', '<', now());
                    });
            });
        } elseif ($request->status === 'pending') {
            $query->where('status', 'pending')
                ->where(function ($q) {
                    $q->whereNull('due_date')->orWhere('due_date', '>=', now());
                });
        } elseif ($request->status === 'in_progress') {
            $query->where('status', 'in_progress')
                ->where(function ($q) {
                    $q->whereNull('due_date')->orWhere('due_date', '>=', now());
                });
        } elseif ($request->status) {
            $query->where('status', $request->status);
        }

        $query
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->search,   fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderByRaw("CASE
                WHEN status = 'overdue'     THEN 0
                WHEN status = 'pending'     THEN 1
                WHEN status = 'in_progress' THEN 2
                ELSE 3
            END")
            ->orderBy('due_date', 'asc');

        $tasks = $query->paginate(12)
            ->withQueryString()
            ->through(fn ($task) => [
                'id'               => $task->id,
                'title'            => $task->title,
                'description'      => $task->description,
                'priority'         => $task->priority,
                'status'           => $task->trashed()
                                          ? 'trashed'
                                          : ($task->isOverdue() ? 'overdue' : $task->status),
                'due_date'         => $task->due_date,
                'created_at'       => $task->created_at,
                'completed_at'     => $task->completed_at,
                'completion_notes' => $task->completion_notes,
                'assigned_by'      => $task->assignedBy?->name,
                'report'           => $task->report ? [
                    'id'    => $task->report->id,
                    'title' => $task->report->title,
                    'slug'  => $task->report->slug,
                ] : null,
            ]);

        $stats = [
            'pending'     => Task::where('assigned_to', $user->id)
                                 ->where('status', 'pending')
                                 ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', now()))
                                 ->count(),
            'in_progress' => Task::where('assigned_to', $user->id)
                                 ->where('status', 'in_progress')
                                 ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', now()))
                                 ->count(),
            'completed'   => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
            'overdue'     => Task::where('assigned_to', $user->id)
                                 ->where('status', '!=', 'completed')
                                 ->where('due_date', '<', now())
                                 ->count(),
            'trashed'     => Task::onlyTrashed()->where('assigned_to', $user->id)->count(),
        ];

        return Inertia::render('Tasks/MyTasks', [
            'tasks'   => $tasks,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'status', 'priority', 'sort']),
        ]);
    }

    // =========================================================================
    // STATS API
    // =========================================================================

    public function getStats()
    {
        $user = auth()->user();
        $now  = now();

        if ($user->hasRole('admin')) {
            $total = Task::count();
            $stats = [
                'total'           => $total,
                'pending'         => Task::where('status', 'pending')
                                         ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now))
                                         ->count(),
                'in_progress'     => Task::where('status', 'in_progress')
                                         ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now))
                                         ->count(),
                'completed'       => Task::where('status', 'completed')->count(),
                'overdue'         => Task::where('status', '!=', 'completed')
                                         ->whereNotNull('due_date')
                                         ->where('due_date', '<', $now)
                                         ->count(),
                'trashed'         => Task::onlyTrashed()->count(),
                'completion_rate' => round(
                    (Task::where('status', 'completed')->count() / max($total, 1)) * 100
                ),
            ];
        } else {
            $total = Task::where('assigned_to', $user->id)->count();
            $stats = [
                'total'           => $total,
                'pending'         => Task::where('assigned_to', $user->id)
                                         ->where('status', 'pending')
                                         ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now))
                                         ->count(),
                'in_progress'     => Task::where('assigned_to', $user->id)
                                         ->where('status', 'in_progress')
                                         ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now))
                                         ->count(),
                'completed'       => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
                'overdue'         => Task::where('assigned_to', $user->id)
                                         ->where('status', '!=', 'completed')
                                         ->whereNotNull('due_date')
                                         ->where('due_date', '<', $now)
                                         ->count(),
                'completion_rate' => round(
                    (Task::where('assigned_to', $user->id)->where('status', 'completed')->count()
                     / max($total, 1)) * 100
                ),
            ];
        }

        $byPriority = [
            'low'    => Task::where('priority', 'low')->count(),
            'medium' => Task::where('priority', 'medium')->count(),
            'high'   => Task::where('priority', 'high')->count(),
            'urgent' => Task::where('priority', 'urgent')->count(),
        ];

        return response()->json(['stats' => $stats, 'by_priority' => $byPriority]);
    }

    // =========================================================================
    // BULK OPERATIONS
    // =========================================================================

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'task_ids'   => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        $deletedCount = 0;

        foreach ($request->task_ids as $taskId) {
            $task = Task::find($taskId);
            if (! $task) {
                continue;
            }

            UserActivity::log(auth()->id(), 'task_deleted', 'task', $task->id, [
                'task_title'  => $task->title,
                'bulk_delete' => true,
            ]);

            if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
                NotificationService::create(
                    user:      $task->assigned_to,
                    type:      'task_deleted',
                    title:     'Task Deleted',
                    message:   "Task \"{$task->title}\" has been deleted.",
                    notifiable: $task,
                    actionUrl: route('my-tasks.index'),
                    icon:      'fa-solid fa-trash',
                    color:     '#ef4444'
                );
            }

            NotificationService::deleteForNotifiable($task);
            $task->delete();
            $deletedCount++;
        }

        return response()->json([
            'message'       => "{$deletedCount} tasks deleted successfully",
            'deleted_count' => $deletedCount,
        ]);
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'task_ids'    => 'required|array',
            'task_ids.*'  => 'exists:tasks,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignedCount = 0;

        foreach ($request->task_ids as $taskId) {
            $task = Task::find($taskId);
            if (! $task) {
                continue;
            }

            $oldAssignee = $task->assigned_to;
            $task->update(['assigned_to' => $request->assigned_to]);

            if ($request->assigned_to !== $oldAssignee) {
                NotificationService::create(
                    user:      $request->assigned_to,
                    type:      'task_assigned',
                    title:     'New Task Assigned',
                    message:   "Task \"{$task->title}\" has been assigned to you.",
                    notifiable: $task,
                    actionUrl: route('admin.tasks.show', $task->id),
                    icon:      'fa-solid fa-tasks',
                    color:     '#6366f1'
                );
            }

            $assignedCount++;
        }

        return response()->json([
            'message'        => "{$assignedCount} tasks assigned successfully",
            'assigned_count' => $assignedCount,
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'task_ids'   => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status'     => 'required|in:pending,in_progress,completed',
        ]);

        $updatedCount = 0;

        foreach ($request->task_ids as $taskId) {
            $task = Task::find($taskId);
            if (! $task || $task->status === $request->status) {
                continue;
            }

            $task->update([
                'status'       => $request->status,
                'completed_at' => $request->status === 'completed' ? now() : null,
            ]);

            if ($request->status === 'completed') {
                NotificationService::taskCompleted($task);
            }

            $updatedCount++;
        }

        return response()->json([
            'message'       => "{$updatedCount} tasks updated successfully",
            'updated_count' => $updatedCount,
        ]);
    }

    // =========================================================================
    // SEARCH
    // =========================================================================

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $tasks = Task::with(['assignedTo', 'assignedBy'])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn ($task) => [
                'id'               => $task->id,
                'title'            => $task->title,
                'status'           => $task->status,
                'priority'         => $task->priority,
                'assigned_to_name' => $task->assignedTo?->name,
                'trashed'          => $task->trashed(),
            ]);

        return response()->json(['tasks' => $tasks]);
    }

    // =========================================================================
    // EXPORT — ADMIN (all tasks with filters + sort, mirrors the index list)
    // =========================================================================

    /**
     * Export ALL tasks to CSV for admins.
     * Route: GET /admin/tasks/export  (admin.tasks.export)
     *
     * ✅ Applies EVERY filter the index list uses:
     *   status, priority, assigned_to, search, trashed, sort, direction
     *
     * This guarantees the exported rows are EXACTLY what is visible in the
     * current filtered/sorted table — nothing more, nothing less.
     */
    public function export(Request $request)
    {
        $now = now();

        // Whitelist sort column to prevent SQL injection
        $allowedSorts = ['created_at', 'updated_at', 'due_date', 'title', 'priority', 'status'];
        $sortCol      = in_array($request->sort, $allowedSorts, true) ? $request->sort : 'created_at';
        $sortDir      = $request->direction === 'asc' ? 'asc' : 'desc';

        $query = Task::with(['assignedTo', 'assignedBy', 'report'])
            ->when($request->priority,    fn ($q) => $q->where('priority',    $request->priority))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->search,      fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->trashed,     fn ($q) => $q->onlyTrashed());

        // Mirror exact same status filter logic as index()
        if ($request->status === 'overdue') {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', $now);
        } elseif ($request->status === 'pending') {
            $query->where('status', 'pending')
                  ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now));
        } elseif ($request->status === 'in_progress') {
            $query->where('status', 'in_progress')
                  ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', $now));
        } elseif ($request->status === 'completed') {
            $query->where('status', 'completed');
        }

        $tasks    = $query->orderBy($sortCol, $sortDir)->get();
        $filename = 'tasks_' . now()->format('Y-m-d_His') . '.csv';

        return $this->streamCsv($tasks, $filename, isAdmin: true);
    }

    // =========================================================================
    // EXPORT — MY TASKS (current user only)
    // =========================================================================

    /**
     * Export tasks assigned to the currently authenticated user.
     * Route: GET /my-tasks/export  (my-tasks.export)
     *
     * Mirrors the exact filtering logic used in myTasks() for consistency.
     * Supports filters: status, priority, search
     * Sort order: overdue → pending → in_progress → completed, then due_date asc
     */
    public function exportMyTasks(Request $request)
    {
        $user  = auth()->user();
        $query = Task::with(['assignedBy', 'report'])
            ->where('assigned_to', $user->id);

        // Mirror the same status logic as myTasks()
        if ($request->status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($request->status === 'overdue') {
            $query->where(function ($q) {
                $q->where('status', 'overdue')
                    ->orWhere(function ($sub) {
                        $sub->where('status', '!=', 'completed')
                            ->where('due_date', '<', now());
                    });
            });
        } elseif ($request->status === 'pending') {
            $query->where('status', 'pending')
                ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', now()));
        } elseif ($request->status === 'in_progress') {
            $query->where('status', 'in_progress')
                ->where(fn ($q) => $q->whereNull('due_date')->orWhere('due_date', '>=', now()));
        } elseif ($request->status) {
            $query->where('status', $request->status);
        }

        $query
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->search,   fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderByRaw("CASE
                WHEN status = 'overdue'     THEN 0
                WHEN status = 'pending'     THEN 1
                WHEN status = 'in_progress' THEN 2
                ELSE 3
            END")
            ->orderBy('due_date', 'asc');

        $tasks    = $query->get();
        $filename = 'my_tasks_' . now()->format('Y-m-d_His') . '.csv';

        return $this->streamCsv($tasks, $filename, isAdmin: false);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Stream a tasks collection as a UTF-8 CSV download.
     *
     * Admin export includes "Assigned To" + email columns.
     * My-Tasks export omits them (the user IS the assignee).
     *
     * The UTF-8 BOM (\xEF\xBB\xBF) is written first so Microsoft Excel
     * opens the file with correct encoding without any manual import step.
     *
     * @param  \Illuminate\Support\Collection  $tasks
     * @param  string  $filename
     * @param  bool    $isAdmin
     */
    private function streamCsv(
        $tasks,
        string $filename,
        bool $isAdmin = false
    ): \Symfony\Component\HttpFoundation\StreamedResponse {

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($tasks, $isAdmin) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM — Excel auto-detects encoding without manual import step
            fwrite($handle, "\xEF\xBB\xBF");

            // ── Header row ────────────────────────────────────────────────────
            $headerRow = [
                'ID',
                'Title',
                'Description',
                'Priority',
                'Status',           // real stored status
                'Display Status',   // computed: Overdue / Trashed if applicable
                'Assigned By',
                'Assigned By Email',
            ];

            if ($isAdmin) {
                $headerRow[] = 'Assigned To';
                $headerRow[] = 'Assigned To Email';
            }

            array_push(
                $headerRow,
                'Due Date',
                'Is Overdue',
                'Days Overdue',
                'Created At',
                'Updated At',
                'Completed At',
                'Completion Notes',
                'Related Report',
                'Report Slug',
                'Report Status',
                'Trashed At'
            );

            fputcsv($handle, $headerRow);

            // ── Data rows ─────────────────────────────────────────────────────
            $now = now();

            foreach ($tasks as $task) {
                // Compute overdue flag — same logic as index() and Vue frontend
                $isOverdue = ! $task->trashed()
                    && $task->due_date
                    && $task->due_date < $now
                    && $task->status !== 'completed';

                // Human-readable display status (what the user SEES, not DB value)
                $displayStatus = $task->trashed()
                    ? 'Trashed'
                    : ($isOverdue
                        ? 'Overdue'
                        : ucwords(str_replace('_', ' ', $task->status)));

                // Days overdue (positive integer, 0 if not overdue)
                $daysOverdue = $isOverdue
                    ? (int) $task->due_date->diffInDays($now)
                    : 0;

                $row = [
                    $task->id,
                    $task->title,
                    $task->description ?? '',
                    ucfirst($task->priority),
                    ucwords(str_replace('_', ' ', $task->status)),   // real stored status
                    $displayStatus,                                    // computed display status
                    $task->assignedBy?->name  ?? 'N/A',
                    $task->assignedBy?->email ?? 'N/A',
                ];

                if ($isAdmin) {
                    $row[] = $task->assignedTo?->name  ?? 'N/A';
                    $row[] = $task->assignedTo?->email ?? 'N/A';
                }

                $row[] = $task->due_date     ? $task->due_date->format('d M Y, H:i')     : 'No due date';
                $row[] = $isOverdue          ? 'Yes'                                       : 'No';
                $row[] = $daysOverdue > 0    ? $daysOverdue . ' day(s)'                   : '—';
                $row[] = $task->created_at   ? $task->created_at->format('d M Y, H:i')    : 'N/A';
                $row[] = $task->updated_at   ? $task->updated_at->format('d M Y, H:i')    : 'N/A';
                $row[] = $task->completed_at ? $task->completed_at->format('d M Y, H:i')  : 'N/A';
                $row[] = $task->completion_notes ?? '';
                $row[] = $task->report?->title   ?? '';
                $row[] = $task->report?->slug    ?? '';
                $row[] = $task->report?->status  ?? '';
                $row[] = $task->deleted_at       ? $task->deleted_at->format('d M Y, H:i') : '';

                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
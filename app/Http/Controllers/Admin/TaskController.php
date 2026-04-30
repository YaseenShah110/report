<?php
// app/Http/Controllers/Admin/TaskController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Report;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
  

    public function index(Request $request)
    {
        
        
        $tasks = Task::with(['assignedTo', 'assignedBy', 'report'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(15)
            ->through(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'created_at' => $task->created_at,
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
        ];

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'search', 'sort', 'direction'])
        ]);
    }

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

        UserActivity::log(auth()->id(), 'task_created', 'task', $task->id, [
            'task_title' => $task->title,
            'assigned_to' => $task->assignedTo->name
        ]);

        return redirect()->route('admin.tasks.index')->with('success', 'Task created successfully.');
    }

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

        $task->update($request->only([
            'title', 'description', 'assigned_to', 'priority', 
            'status', 'due_date', 'report_id'
        ]));

        if ($request->status === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        UserActivity::log(auth()->id(), 'task_updated', 'task', $task->id, [
            'task_title' => $task->title,
            'changes' => $request->only(['title', 'status', 'priority'])
        ]);

        return redirect()->route('admin.tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        
        
        UserActivity::log(auth()->id(), 'task_deleted', 'task', $task->id, [
            'task_title' => $task->title
        ]);

        $task->delete();

        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'completion_notes' => 'nullable|string'
        ]);

        $task->update([
            'status' => $request->status,
            'completion_notes' => $request->completion_notes,
            'completed_at' => $request->status === 'completed' ? now() : null
        ]);

        return response()->json(['message' => 'Task status updated']);
    }
}
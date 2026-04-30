<?php
// app/Http/Controllers/Admin/ReportAssignmentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\ReportAssignment;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportAssignmentController extends Controller
{

    public function index(Request $request)
    {
        $assignments = ReportAssignment::with(['report', 'user', 'assignedBy'])
            ->when($request->report_id, fn($q) => $q->where('report_id', $request->report_id))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->orderBy('assigned_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $reports = Report::all();
        $users = User::all();

        return Inertia::render('Admin/Reports/Assignments', [
            'assignments' => $assignments,
            'reports' => $reports,
            'users' => $users,
            'filters' => $request->only(['report_id', 'user_id'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:view,edit,manage',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $existing = ReportAssignment::where('report_id', $request->report_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existing) {
            $existing->update([
                'permission' => $request->permission,
                'expires_at' => $request->expires_at,
                'is_active' => true,
            ]);
            $message = 'Assignment updated successfully.';
        } else {
            ReportAssignment::create([
                'report_id' => $request->report_id,
                'user_id' => $request->user_id,
                'assigned_by' => auth()->id(),
                'permission' => $request->permission,
                'expires_at' => $request->expires_at,
            ]);
            $message = 'Report assigned successfully.';
        }

        $report = Report::find($request->report_id);
        $user = User::find($request->user_id);

        UserActivity::log(auth()->id(), 'report_assigned', 'report', $report->id, [
            'report_title' => $report->title,
            'assigned_to' => $user->name,
            'permission' => $request->permission
        ]);

        return redirect()->back()->with('success', $message);
    }

    public function destroy(ReportAssignment $assignment)
    {
        $assignment->delete();

        UserActivity::log(auth()->id(), 'report_unassigned', 'report', $assignment->report_id, [
            'report_title' => $assignment->report->title,
            'user' => $assignment->user->name
        ]);

        return redirect()->back()->with('success', 'Assignment removed successfully.');
    }

    public function toggleActive(ReportAssignment $assignment)
    {
        $assignment->update(['is_active' => !$assignment->is_active]);

        return response()->json([
            'message' => $assignment->is_active ? 'Assignment activated.' : 'Assignment deactivated.'
        ]);
    }
}
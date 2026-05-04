<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\ReportAssignment;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Report Assignment Controller
 * 
 * Manages report assignments (sharing reports with users).
 * Supports CRUD operations, toggle active status, and export.
 * 
 * Access: Admin and Manager roles
 */
class ReportAssignmentController extends Controller
{
    /**
     * Display all report assignments with filters.
     */
    public function index(Request $request)
    {
        $assignments = ReportAssignment::with(['report', 'user', 'assignedBy'])
            ->when($request->report_id, fn($q) => $q->where('report_id', $request->report_id))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->orderBy('assigned_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $reports = Report::all(['id', 'title']);
        $users = User::all(['id', 'name']);

        return Inertia::render('Admin/Reports/Assignments', [
            'assignments' => $assignments,
            'reports'     => $reports,
            'users'       => $users,
            'filters'     => $request->only(['report_id', 'user_id'])
        ]);
    }

    /**
     * Store a new assignment or update existing one.
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_id'  => 'required|exists:reports,id',
            'user_id'    => 'required|exists:users,id',
            'permission' => 'required|in:view,edit,manage',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Update or create the assignment
        $existing = ReportAssignment::where('report_id', $request->report_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existing) {
            $existing->update([
                'permission' => $request->permission,
                'expires_at' => $request->expires_at,
                'is_active'  => true,
            ]);
            $message = 'Assignment updated successfully.';
        } else {
            ReportAssignment::create([
                'report_id'   => $request->report_id,
                'user_id'     => $request->user_id,
                'assigned_by' => auth()->id(),
                'permission'  => $request->permission,
                'expires_at'  => $request->expires_at,
            ]);
            $message = 'Report assigned successfully.';
        }

        // Log activity
        $report = Report::find($request->report_id);
        $user = User::find($request->user_id);
        UserActivity::log(auth()->id(), 'report_assigned', 'report', $report->id, [
            'report_title' => $report->title,
            'assigned_to'  => $user->name,
            'permission'   => $request->permission
        ]);

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove an assignment.
     */
    public function destroy(ReportAssignment $assignment)
    {
        $reportTitle = $assignment->report->title;
        $userName = $assignment->user->name;
        
        $assignment->delete();

        UserActivity::log(auth()->id(), 'report_unassigned', 'report', $assignment->report_id, [
            'report_title' => $reportTitle,
            'user'         => $userName
        ]);

        return redirect()->back()->with('success', 'Assignment removed successfully.');
    }

    /**
     * Toggle assignment active/inactive status.
     */
    public function toggleActive(ReportAssignment $assignment)
    {
        $assignment->update(['is_active' => !$assignment->is_active]);

        return response()->json([
            'message' => $assignment->is_active ? 'Assignment activated.' : 'Assignment deactivated.'
        ]);
    }

    /**
     * Filter assignments for a specific report.
     */
    public function forReport(Report $report)
    {
        $assignments = ReportAssignment::where('report_id', $report->id)
            ->with(['user', 'assignedBy'])
            ->paginate(20);
        
        return Inertia::render('Admin/Reports/Assignments', [
            'assignments' => $assignments,
            'report'      => $report,
            'reports'     => Report::all(['id', 'title']),
            'users'       => User::all(['id', 'name']),
        ]);
    }

    /**
     * Filter assignments for a specific user.
     */
    public function forUser(User $user)
    {
        $assignments = ReportAssignment::where('user_id', $user->id)
            ->with(['report', 'assignedBy'])
            ->paginate(20);
        
        return Inertia::render('Admin/Reports/Assignments', [
            'assignments' => $assignments,
            'reports'     => Report::all(['id', 'title']),
            'users'       => User::all(['id', 'name']),
        ]);
    }

    /**
     * Bulk assign reports to a user.
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'report_ids'  => 'required|array',
            'report_ids.*' => 'exists:reports,id',
            'user_id'     => 'required|exists:users,id',
            'permission'  => 'required|in:view,edit,manage',
        ]);

        $count = 0;
        foreach ($request->report_ids as $reportId) {
            ReportAssignment::updateOrCreate(
                ['report_id' => $reportId, 'user_id' => $request->user_id],
                [
                    'assigned_by' => auth()->id(),
                    'permission'  => $request->permission,
                    'is_active'   => true,
                ]
            );
            $count++;
        }

        return response()->json(['message' => "{$count} reports assigned successfully"]);
    }

    /**
     * Bulk revoke assignments.
     */
    public function bulkRevoke(Request $request)
    {
        $request->validate([
            'assignment_ids'   => 'required|array',
            'assignment_ids.*' => 'exists:report_assignments,id',
        ]);

        $count = ReportAssignment::whereIn('id', $request->assignment_ids)->delete();

        return response()->json(['message' => "{$count} assignments revoked"]);
    }

    /**
     * Export assignments to CSV.
     */
    public function export(Request $request)
    {
        $assignments = ReportAssignment::with(['report', 'user', 'assignedBy'])
            ->when($request->report_id, fn($q) => $q->where('report_id', $request->report_id))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->get();

        $filename = 'report_assignments_' . now()->format('Y-m-d') . '.csv';
        
        $callback = function() use ($assignments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Report', 'User', 'Permission', 'Assigned By', 'Status', 'Expires', 'Assigned At']);
            
            foreach ($assignments as $a) {
                fputcsv($handle, [
                    $a->report->title ?? 'N/A',
                    $a->user->name ?? 'N/A',
                    $a->permission,
                    $a->assignedBy->name ?? 'N/A',
                    $a->is_active ? 'Active' : 'Inactive',
                    $a->expires_at ?? 'Never',
                    $a->created_at->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
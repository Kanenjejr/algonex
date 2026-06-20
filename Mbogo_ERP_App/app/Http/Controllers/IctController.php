<?php

namespace App\Http\Controllers;

use App\Models\SoftwareHardwareIssue;
use App\Models\ItMaintenance;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class IctController extends Controller
{
    public function issuesIndex()
    {
        $issues = SoftwareHardwareIssue::with('assignedTo')
            ->orderByDesc('created_at')
            ->get();

        $users = User::orderBy('name')->get();

        return view('admin.ict_issues', compact('issues', 'users'));
    }

    public function storeIssue(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'device_name' => 'required|string|max:255',
                'issue_type' => 'required|in:software,hardware',
                'category' => 'required|string|max:255',
                'problem_description' => 'required|string',
                'priority_level' => 'required|in:low,medium,high,critical',
                'date_reported' => 'required|date',
                'assigned_to' => 'nullable|exists:users,id',
                'issue_status' => 'required|in:open,pending,resolved',
                'resolution_details' => 'nullable|string',
                'resolved_date' => 'nullable|date',
            ]);

            $data = $request->only([
                'user_id',
                'device_name',
                'issue_type',
                'category',
                'problem_description',
                'priority_level',
                'date_reported',
                'assigned_to',
                'issue_status',
                'resolution_details',
                'resolved_date',
            ]);

            SoftwareHardwareIssue::create($data);

            Alert::success('Success', 'ICT issue created successfully');
            return redirect()->route('ict.issues.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to create ICT issue');
            return back()->withInput();
        }
    }

    public function updateIssue(Request $request, $id)
    {
        try {
            $realId = decrypt($id);
            $issue = SoftwareHardwareIssue::findOrFail($realId);

            $request->validate([
                'device_name' => 'required|string|max:255',
                'issue_type' => 'required|in:software,hardware',
                'category' => 'required|string|max:255',
                'problem_description' => 'required|string',
                'priority_level' => 'required|in:low,medium,high,critical',
                'date_reported' => 'required|date',
                'assigned_to' => 'nullable|exists:users,id',
                'issue_status' => 'required|in:open,pending,resolved',
                'resolution_details' => 'nullable|string',
                'resolved_date' => 'nullable|date',
            ]);

            $data = $request->only([
                'device_name',
                'issue_type',
                'category',
                'problem_description',
                'priority_level',
                'date_reported',
                'assigned_to',
                'issue_status',
                'resolution_details',
                'resolved_date',
            ]);

            $issue->update($data);

            Alert::success('Success', 'ICT issue updated successfully');
            return redirect()->route('ict.issues.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to update ICT issue');
            return back()->withInput();
        }
    }

    public function destroyIssue($id)
    {
        try {
            $realId = decrypt($id);
            $issue = SoftwareHardwareIssue::findOrFail($realId);

            $issue->delete();

            Alert::success('Success', 'ICT issue deleted successfully');
            return redirect()->route('ict.issues.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to delete ICT issue');
            return back();
        }
    }

    public function maintenanceIndex()
    {
        $maintenances = ItMaintenance::with('issue')
            ->orderByDesc('created_at')
            ->get();

        $issues = SoftwareHardwareIssue::orderByDesc('created_at')->get();

        return view('admin.it_maintenance', compact('maintenances', 'issues'));
    }

    public function storeMaintenance(Request $request)
    {
        try {
            $request->validate([
                'issue_id' => 'nullable|exists:software_hardware_issues,issue_id',
                'asset_id' => 'nullable|integer',
                'maintenance_type' => 'required|in:preventive,corrective',
                'description' => 'required|string',
                'technician_name' => 'required|string|max:255',
                'maintenance_date' => 'required|date',
                'status' => 'required|in:pending,in_progress,completed',
                'cost' => 'nullable|numeric',
                'remarks' => 'nullable|string',
            ]);

            $data = $request->only([
                'issue_id',
                'asset_id',
                'maintenance_type',
                'description',
                'technician_name',
                'maintenance_date',
                'status',
                'cost',
                'remarks',
            ]);

            ItMaintenance::create($data);

            Alert::success('Success', 'Maintenance record created successfully');
            return redirect()->route('ict.maintenance.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to create maintenance record');
            return back()->withInput();
        }
    }

    public function updateMaintenance(Request $request, $id)
    {
        try {
            $realId = decrypt($id);
            $maintenance = ItMaintenance::findOrFail($realId);

            $request->validate([
                'issue_id' => 'nullable|exists:software_hardware_issues,issue_id',
                'asset_id' => 'nullable|integer',
                'maintenance_type' => 'required|in:preventive,corrective',
                'description' => 'required|string',
                'technician_name' => 'required|string|max:255',
                'maintenance_date' => 'required|date',
                'status' => 'required|in:pending,in_progress,completed',
                'cost' => 'nullable|numeric',
                'remarks' => 'nullable|string',
            ]);

            $data = $request->only([
                'issue_id',
                'asset_id',
                'maintenance_type',
                'description',
                'technician_name',
                'maintenance_date',
                'status',
                'cost',
                'remarks',
            ]);

            $maintenance->update($data);

            Alert::success('Success', 'Maintenance record updated successfully');
            return redirect()->route('ict.maintenance.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to update maintenance record');
            return back()->withInput();
        }
    }

    public function destroyMaintenance($id)
    {
        try {
            $realId = decrypt($id);
            $maintenance = ItMaintenance::findOrFail($realId);

            $maintenance->delete();

            Alert::success('Success', 'Maintenance record deleted successfully');
            return redirect()->route('ict.maintenance.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to delete maintenance record');
            return back();
        }
    }
}
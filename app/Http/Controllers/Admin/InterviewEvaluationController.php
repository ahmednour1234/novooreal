<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterviewEvaluation;
use Illuminate\Http\Request;
use Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function App\CPU\translate;

class InterviewEvaluationController extends Controller
{
    /**
     * Store a new interview evaluation for a given applicant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $applicantId
     * @return \Illuminate\Http\RedirectResponse
     */
        public function create($applicantId)
    {
            $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("meeting.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        // Pass the applicant ID to the view so you know for which applicant this evaluation is created.
        return view('admin-views.job_applicants.interview_create', compact('applicantId'));
    }
    public function store(Request $request, $applicantId)
    {
        $request->validate([
            'interviewer'      => 'required|string|max:255',
            'interview_date'   => 'required|date',
            'evaluation_notes' => 'nullable|string',
            'score'            => 'nullable|integer',
        ]);

        try {
            InterviewEvaluation::create([
                'job_applicant_id' => $applicantId,
                'interviewer'      => $request->interviewer,
                'interview_date'   => $request->interview_date,
                'evaluation_notes' => $request->evaluation_notes,
                'score'            => $request->score,
            ]);

            Toastr::success('Interview evaluation recorded successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('Error adding interview evaluation: ' . $e->getMessage());
            Toastr::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing an interview evaluation.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
            $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("meeting.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $evaluation = InterviewEvaluation::findOrFail($id);
        return view('admin-views.job_applicants.interview_edit', compact('evaluation'));
    }

    /**
     * Update the specified interview evaluation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $evaluation = InterviewEvaluation::findOrFail($id);

        $request->validate([
            'interviewer'      => 'required|string|max:255',
            'interview_date'   => 'required|date',
            'evaluation_notes' => 'nullable|string',
            'score'            => 'nullable|integer',
        ]);

        try {
            $evaluation->update([
                'interviewer'      => $request->interviewer,
                'interview_date'   => $request->interview_date,
                'evaluation_notes' => $request->evaluation_notes,
                'score'            => $request->score,
            ]);

            Toastr::success('Interview evaluation updated successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('Error updating interview evaluation: ' . $e->getMessage());
            Toastr::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified interview evaluation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
            $adminId = Auth::guard('admin')->id();
    $admin = DB::table('admins')->where('id', $adminId)->first();

    if (!$admin) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $roleId = $admin->role_id;
    $role = DB::table('roles')->where('id', $roleId)->first();

    if (!$role) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    $decodedData = json_decode($role->data, true);
    if (is_string($decodedData)) {
        $decodedData = json_decode($decodedData, true);
    }

    if (!is_array($decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }

    if (!in_array("meeting.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $evaluation = InterviewEvaluation::findOrFail($id);

        try {
            $evaluation->delete();
            Toastr::success('Interview evaluation deleted successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('Error deleting interview evaluation: ' . $e->getMessage());
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplicant;
use App\Models\ApplicationStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Toastr;
use function App\CPU\translate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobApplicantController extends Controller
{
    /**
     * Display a listing of the job applicants.
     */
    public function index()
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

    if (!in_array("interview.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $applicants = JobApplicant::orderBy('applied_date', 'desc')->paginate(20);
        return view('admin-views.job_applicants.index', compact('applicants'));
    }
public function showInterviews($id)
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

    if (!in_array("meeting.index", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
    // Retrieve the job applicant along with his/her interview evaluations
    $applicant = JobApplicant::with('interviewEvaluations')->findOrFail($id);

    // Get the collection of interview evaluations for clarity (optional)
    $interviews = $applicant->interviewEvaluations;

    // Return a view to display the interviews
    return view('admin-views.job_applicants.interviews', compact('applicant', 'interviews'));
}

    /**
     * Show the form for creating a new job applicant.
     */
    public function create()
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

    if (!in_array("interview.store", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        return view('admin-views.job_applicants.create');
    }

    /**
     * Store a newly created job applicant.
     */
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:job_applicants,email',
            'phone'        => 'nullable|string|max:50',
            'resume_pdf'   => 'nullable|mimes:pdf|max:2048',
            'applied_date' => 'required|date',
            // Optionally, you can allow setting an interview_date and a status
            'interview_date' => 'nullable|date',
            'status'         => 'nullable|string|max:50',
        ]);

        try {
            $data = $request->only(['full_name', 'email', 'phone', 'applied_date']);
            // Set default status to "new" if not provided
            $data['status'] = $request->status ?? 'new';
            if ($request->filled('interview_date')) {
                $data['interview_date'] = $request->interview_date;
            }

            // If a resume PDF is uploaded, store it and save its basename
            if ($request->hasFile('resume_pdf')) {
                $path = $request->file('resume_pdf')->store('public/resumes');
                $data['resume_pdf'] = basename($path);
            }

            // Create the job applicant record
            $applicant = JobApplicant::create($data);

            // Record initial application status history
            ApplicationStatusHistory::create([
                'job_applicant_id' => $applicant->id,
                'previous_status'  => '',
                'new_status'       => $applicant->status,
                'comment'          => 'تم انشاء الحالة الابتدائية',
            ]);

            Toastr::success('تم إنشاء طلب التوظيف بنجاح');
            return redirect()->route('admin.job_applicants.index');
        } catch (\Exception $e) {
            \Log::error('Error creating job applicant: ' . $e->getMessage());
            Toastr::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified job applicant.
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

    if (!in_array("interview.update", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $applicant = JobApplicant::findOrFail($id);
        return view('admin-views.job_applicants.edit', compact('applicant'));
    }

    /**
     * Update the specified job applicant.
     */
   public function update(Request $request, $id)
{
    
    $applicant = JobApplicant::findOrFail($id);

    $request->validate([
        'full_name'      => 'required|string|max:255',
        'email'          => 'required|email|unique:job_applicants,email,' . $applicant->id,
        'phone'          => 'nullable|string|max:50',
        'resume_pdf'     => 'nullable|mimes:pdf|max:2048',
        'applied_date'   => 'required|date',
        'interview_date' => 'nullable|date',
        'status'         => 'nullable|string|max:50',
    ]);

    try {
        // Gather the data to update
        $data = $request->only(['full_name', 'email', 'phone', 'applied_date', 'interview_date']);

        // Check if the status is provided and has changed
        if ($request->filled('status') && $request->status !== $applicant->status) {
            // Record the status change in the history table
            ApplicationStatusHistory::create([
                'job_applicant_id' => $applicant->id,
                'previous_status'  => $applicant->status,
                'new_status'       => $request->status,
                'comment'          => 'تم تحديث الحالة',
            ]);
            $data['status'] = $request->status;
        } else {
            // If not provided, keep the current status
            $data['status'] = $applicant->status;
        }

        // Handle resume PDF upload if available
        if ($request->hasFile('resume_pdf')) {
            // If an old resume exists, delete it from storage
            if ($applicant->resume_pdf && Storage::exists('public/resumes/' . $applicant->resume_pdf)) {
                Storage::delete('public/resumes/' . $applicant->resume_pdf);
            }
            // Store the new resume
            $path = $request->file('resume_pdf')->store('public/resumes');
            $data['resume_pdf'] = basename($path);
        }

        // Update the applicant record
        $applicant->update($data);

        Toastr::success(translate('تم تحديث طلب التوظيف بنجاح'));
        return redirect()->route('admin.job_applicants.index');
    } catch (\Exception $e) {
        Toastr::error($e->getMessage());
        return redirect()->route('admin.job_applicants.index');
    }
}


    /**
     * Remove the specified job applicant.
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

    if (!in_array("interview.destroy", $decodedData)) {
        Toastr::warning('غير مسموح لك! كلم المدير.');
        return redirect()->back();
    }   
        $applicant = JobApplicant::findOrFail($id);
        try {
            // Delete resume PDF file if exists
            if ($applicant->resume_pdf && Storage::exists('public/resumes/' . $applicant->resume_pdf)) {
                Storage::delete('public/resumes/' . $applicant->resume_pdf);
            }
            $applicant->delete();

            Toastr::success('تم حذف طلب التوظيف بنجاح');
            return redirect()->route('admin.job_applicants.index');
        } catch (\Exception $e) {
            \Log::error('Error deleting job applicant: ' . $e->getMessage());
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }
}

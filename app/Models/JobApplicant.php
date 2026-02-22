<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplicant extends Model
{
    // Specify the table name (optional if it matches the plural form of the model name)
    protected $table = 'job_applicants';

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'resume_pdf',
        'status',
        'applied_date',
    ];

    // Optionally, define the type casting
    protected $casts = [
        'applied_date' => 'date',
    ];

    // You can define any relationships here as needed. For example,
    // if you have interview evaluations related to this applicant:
    public function interviewEvaluations()
    {
        return $this->hasMany(InterviewEvaluation::class, 'job_applicant_id');
    }
      public function applicationStatusHistory()
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'job_applicant_id');
    }

 
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewEvaluation extends Model
{
    // Optionally specify the table name, if not following Laravel's naming convention:
    protected $table = 'interview_evaluations';

    // Define the fillable properties for mass assignment:
    protected $fillable = [
        'job_applicant_id',
        'interviewer',
        'interview_date',
        'evaluation_notes',
        'score',
    ];

    // Cast the interview_date field to a date instance
    protected $casts = [
        'interview_date' => 'date',
    ];

    /**
     * Define a relationship back to the job applicant.
     */
    public function jobApplicant()
    {
        return $this->belongsTo(JobApplicant::class, 'job_applicant_id');
    }
}

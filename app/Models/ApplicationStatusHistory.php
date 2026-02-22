<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'application_status_history';

    // Disable default timestamps if your table uses a custom timestamp column (e.g., changed_at)
    public $timestamps = false;

    // Define fillable properties for mass assignment
    protected $fillable = [
        'job_applicant_id',
        'previous_status',
        'new_status',
        'comment',
    ];

    // Optionally, you can define a custom date cast for the changed_at field if you intend to use it
    // protected $casts = [
    //     'changed_at' => 'datetime',
    // ];

    /**
     * Get the job applicant that owns this status history record.
     */
    public function jobApplicant()
    {
        return $this->belongsTo(JobApplicant::class, 'job_applicant_id');
    }
}

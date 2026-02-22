<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalEntryDetail extends Model
{
    use HasFactory;

    protected $table = 'journal_entries_details';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'cost_center_id',
        'description',
        'attachment_path',
    ];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class); // تأكد أن لديك موديل الحسابات
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class); // تأكد أن لديك موديل مراكز التكلفة
    }
        public function branch(){ return $this->belongsTo(\App\Models\Branch::class, 'branch_id'); }

}

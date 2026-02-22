<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'entry_date',
        'reference',
        'description',
        'created_by',
        'payment_voucher_id'
    ];

    public function details()
    {
        return $this->hasMany(JournalEntryDetail::class, 'journal_entry_id');
    }

      public function seller()
    {
        return $this->belongsTo(Seller::class, 'created_by');
    }
    public function branch(){ return $this->belongsTo(\App\Models\Branch::class, 'branch_id'); }

}

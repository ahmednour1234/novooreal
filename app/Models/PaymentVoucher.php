<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    use HasFactory;

    protected $table = 'payment_vouchers';

    protected $fillable = [
        'voucher_number',
        'date',
        'payee_name',
        'debit_account_id',
        'credit_account_id',
        'amount',
        'currency',
        'payment_method',
        'cheque_number',
        'description',
        'attachment',
        'created_by',
        'journal_entry_id'
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    /* ---------------------------------
     | Relationships
     |----------------------------------*/
    public function debitAccount(): BelongsTo
    {
        // غيّر App\Models\Account لو اسم الموديل/المسار مختلف عندك
        return $this->belongsTo(Account::class, 'debit_account_id');
    }
    
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
        public function voucher(){ return $this->belongsTo(PaymentVoucher::class, 'payment_voucher_id'); }


    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'credit_account_id');
    }

    public function creator(): BelongsTo
    {
        // غيّر App\Models\User لو بتستخدم موديل مستخدم مختلف
        return $this->belongsTo(Seller::class, 'created_by');
    }

    /* ---------------------------------
     | Scopes (اختياريه ومفيدة في الفلاتر)
     |----------------------------------*/
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                     ->when($to,   fn($q) => $q->whereDate('date', '<=', $to));
    }

    public function scopeByAccount($query, $accountId)
    {
        return $query->when($accountId, function ($q) use ($accountId) {
            $q->where(function ($sq) use ($accountId) {
                $sq->where('debit_account_id', $accountId)
                   ->orWhere('credit_account_id', $accountId);
            });
        });
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, function ($q) use ($term) {
            $q->where('voucher_number', 'like', "%{$term}%")
              ->orWhere('payee_name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /* ---------------------------------
     | Accessors / Mutators
     |----------------------------------*/
    // مثال: تنسيق العملة عند الحاجة
    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2) . ' ' . ($this->currency ?? '');
    }

    /* ---------------------------------
     | Helpers
     |----------------------------------*/
    /**
     * إنشاء رقم سند تلقائي لو مش مبعوت
     */
    public static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->voucher_number)) {
                // صيغة: PV-YYYYMM-XXXX
                $prefix = 'PV-' . now()->format('Ym') . '-';
                $last = static::where('voucher_number', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->value('voucher_number');

                $nextSeq = 1;
                if ($last && preg_match('/(\d+)$/', $last, $m)) {
                    $nextSeq = ((int) $m[1]) + 1;
                }
                $model->voucher_number = $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}

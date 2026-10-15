<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends SchoolModel
{
    protected $table = 'salary_payments';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'staff_profile_id' => 'integer',
            'bank_account_id' => 'integer',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'recorded_by' => 'integer',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'recorded_by');
    }
}

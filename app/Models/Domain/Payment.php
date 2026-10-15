<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends SchoolModel
{
    protected $table = 'payments';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'student_id' => 'integer',
            'bank_account_id' => 'integer',
            'received_by' => 'integer',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'received_by');
    }
}

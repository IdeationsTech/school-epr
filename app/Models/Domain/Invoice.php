<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends SchoolModel
{
    protected $table = 'invoices';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'fee_assignment_id' => 'integer',
            'period' => 'date',
            'installment_no' => 'integer',
            'issued_on' => 'date',
            'due_on' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
    public function feeAssignment(): BelongsTo
    {
        return $this->belongsTo(StudentFeeAssignment::class, 'fee_assignment_id');
    }

}

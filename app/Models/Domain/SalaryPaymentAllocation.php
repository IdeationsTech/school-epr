<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPaymentAllocation extends SchoolModel
{
    protected $table = 'salary_payment_allocations';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'salary_payment_id' => 'integer',
            'payroll_item_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function salaryPayment(): BelongsTo
    {
        return $this->belongsTo(SalaryPayment::class, 'salary_payment_id');
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class, 'payroll_item_id');
    }
}

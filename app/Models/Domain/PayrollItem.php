<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends SchoolModel
{
    protected $table = 'payroll_items';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'payroll_run_id' => 'integer',
            'staff_profile_id' => 'integer',
            'salary_structure_id' => 'integer',
            'gross_amount' => 'decimal:2',
            'deduction_amount' => 'decimal:2',
            'advance_recovery' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }
}

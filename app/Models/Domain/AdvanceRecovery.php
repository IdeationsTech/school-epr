<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceRecovery extends SchoolModel
{
    protected $table = 'advance_recoveries';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'staff_advance_id' => 'integer',
            'payroll_item_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function staffAdvance(): BelongsTo
    {
        return $this->belongsTo(StaffAdvance::class, 'staff_advance_id');
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class, 'payroll_item_id');
    }
}

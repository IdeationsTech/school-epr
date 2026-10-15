<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItemComponent extends SchoolModel
{
    protected $table = 'payroll_item_components';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'payroll_item_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class, 'payroll_item_id');
    }
}

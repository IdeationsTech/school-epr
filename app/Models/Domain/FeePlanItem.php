<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePlanItem extends SchoolModel
{
    protected $table = 'fee_plan_items';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'fee_plan_id' => 'integer',
            'fee_head_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function feePlan(): BelongsTo
    {
        return $this->belongsTo(FeePlan::class, 'fee_plan_id');
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }
}

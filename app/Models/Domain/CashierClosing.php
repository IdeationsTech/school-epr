<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierClosing extends SchoolModel
{
    protected $table = 'cashier_closings';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'cashier_membership_id' => 'integer',
            'closing_date' => 'date',
            'expected_cash' => 'decimal:2',
            'counted_cash' => 'decimal:2',
            'variance' => 'decimal:2',
            'approved_by' => 'integer',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function cashierMembership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'cashier_membership_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'approved_by');
    }
}

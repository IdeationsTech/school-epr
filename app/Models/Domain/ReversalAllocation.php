<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReversalAllocation extends SchoolModel
{
    protected $table = 'reversal_allocations';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'payment_reversal_id' => 'integer',
            'payment_allocation_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function paymentReversal(): BelongsTo
    {
        return $this->belongsTo(PaymentReversal::class, 'payment_reversal_id');
    }

    public function paymentAllocation(): BelongsTo
    {
        return $this->belongsTo(PaymentAllocation::class, 'payment_allocation_id');
    }
}

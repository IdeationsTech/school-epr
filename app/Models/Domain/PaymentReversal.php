<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReversal extends SchoolModel
{
    protected $table = 'payment_reversals';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'payment_id' => 'integer',
            'amount' => 'decimal:2',
            'approved_by' => 'integer',
            'reversed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'approved_by');
    }
}

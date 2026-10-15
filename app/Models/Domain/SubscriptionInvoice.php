<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends SchoolModel
{
    protected $table = 'subscription_invoices';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'subscription_id' => 'integer',
            'issued_on' => 'date',
            'due_on' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}

<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends SchoolModel
{
    protected $table = 'invoice_items';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'invoice_id' => 'integer',
            'fee_head_id' => 'integer',
            'quantity' => 'decimal:2',
            'unit_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }
}

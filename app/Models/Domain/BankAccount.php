<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends SchoolModel
{
    protected $table = 'bank_accounts';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'opening_balance' => 'decimal:2',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }
}

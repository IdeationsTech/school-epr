<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends SchoolModel
{
    protected $table = 'subscriptions';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'plan_id' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}

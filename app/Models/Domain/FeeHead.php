<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeHead extends SchoolModel
{
    protected $table = 'fee_heads';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
        ];
    }
}

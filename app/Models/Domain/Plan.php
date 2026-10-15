<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    protected $table = 'plans';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'student_limit' => 'integer',
            'campus_limit' => 'integer',
            'monthly_price' => 'decimal:2',
            'features' => 'array',
        ];
    }
}

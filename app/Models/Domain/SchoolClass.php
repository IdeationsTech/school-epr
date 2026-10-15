<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolClass extends SchoolModel
{
    protected $table = 'classes';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}

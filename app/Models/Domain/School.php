<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $table = 'schools';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
        ];
    }
}

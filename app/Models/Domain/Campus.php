<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campus extends SchoolModel
{
    protected $table = 'campuses';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
        ];
    }
}

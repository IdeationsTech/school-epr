<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends SchoolModel
{
    protected $table = 'subjects';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
        ];
    }
}

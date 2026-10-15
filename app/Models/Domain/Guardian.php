<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardian extends SchoolModel
{
    protected $table = 'guardians';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
        ];
    }
}

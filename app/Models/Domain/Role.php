<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    protected $table = 'roles';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
        ];
    }
}

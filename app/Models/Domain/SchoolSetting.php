<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSetting extends SchoolModel
{
    protected $table = 'school_settings';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}

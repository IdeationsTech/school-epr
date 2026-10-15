<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingScheme extends SchoolModel
{
    protected $table = 'grading_schemes';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'rules' => 'array',
        ];
    }
}

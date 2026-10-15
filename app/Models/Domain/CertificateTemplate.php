<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends SchoolModel
{
    protected $table = 'certificate_templates';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }
}

<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends SchoolModel
{
    protected $table = 'students';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'admitted_on' => 'date',
            'application_id' => 'integer',
            'photo_file_id' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    public function photoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'photo_file_id');
    }
}

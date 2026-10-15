<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicTerm extends SchoolModel
{
    protected $table = 'academic_terms';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'academic_session_id' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'position' => 'integer',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }
}

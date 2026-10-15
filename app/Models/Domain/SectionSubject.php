<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionSubject extends SchoolModel
{
    protected $table = 'section_subjects';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'section_id' => 'integer',
            'subject_id' => 'integer',
            'required_for_diary' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}

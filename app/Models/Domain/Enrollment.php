<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends SchoolModel
{
    protected $table = 'enrollments';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'student_id' => 'integer',
            'section_id' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}

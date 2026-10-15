<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends SchoolModel
{
    protected $table = 'teacher_assignments';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'staff_profile_id' => 'integer',
            'section_id' => 'integer',
            'subject_id' => 'integer',
            'is_class_teacher' => 'boolean',
        ];
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
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

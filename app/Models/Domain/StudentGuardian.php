<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGuardian extends SchoolModel
{
    protected $table = 'student_guardians';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'student_id' => 'integer',
            'guardian_id' => 'integer',
            'is_primary' => 'boolean',
            'can_receive_reports' => 'boolean',
            'can_pick_up' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }
}

<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamCycle extends SchoolModel
{
    protected $table = 'exam_cycles';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'academic_session_id' => 'integer',
            'academic_term_id' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'grading_scheme_id' => 'integer',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function gradingScheme(): BelongsTo
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }
}

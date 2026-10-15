<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Test extends SchoolModel
{
    protected $table = 'tests';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'section_id' => 'integer',
            'subject_id' => 'integer',
            'exam_cycle_id' => 'integer',
            'grading_scheme_id' => 'integer',
            'test_date' => 'date',
            'max_marks' => 'decimal:2',
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

    public function examCycle(): BelongsTo
    {
        return $this->belongsTo(ExamCycle::class, 'exam_cycle_id');
    }

    public function gradingScheme(): BelongsTo
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }
}

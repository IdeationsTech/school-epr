<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentWeightage extends SchoolModel
{
    protected $table = 'assessment_weightages';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'exam_cycle_id' => 'integer',
            'class_id' => 'integer',
            'subject_id' => 'integer',
            'weight' => 'decimal:2',
        ];
    }

    public function examCycle(): BelongsTo
    {
        return $this->belongsTo(ExamCycle::class, 'exam_cycle_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}

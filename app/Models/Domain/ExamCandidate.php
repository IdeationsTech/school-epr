<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamCandidate extends SchoolModel
{
    protected $table = 'exam_candidates';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'exam_cycle_id' => 'integer',
            'enrollment_id' => 'integer',
        ];
    }

    public function examCycle(): BelongsTo
    {
        return $this->belongsTo(ExamCycle::class, 'exam_cycle_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
}

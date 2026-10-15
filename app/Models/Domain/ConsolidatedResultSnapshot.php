<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsolidatedResultSnapshot extends SchoolModel
{
    protected $table = 'consolidated_result_snapshots';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'exam_cycle_id' => 'integer',
            'enrollment_id' => 'integer',
            'revision' => 'integer',
            'grading_scheme_id' => 'integer',
            'payload' => 'array',
            'published_by' => 'integer',
            'published_at' => 'datetime',
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

    public function gradingScheme(): BelongsTo
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'published_by');
    }
}

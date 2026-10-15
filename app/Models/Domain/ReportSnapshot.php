<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSnapshot extends SchoolModel
{
    protected $table = 'report_snapshots';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'test_result_id' => 'integer',
            'grading_scheme_id' => 'integer',
            'revision' => 'integer',
            'payload' => 'array',
            'published_by' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class, 'test_result_id');
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

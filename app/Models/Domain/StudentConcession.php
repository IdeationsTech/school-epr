<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentConcession extends SchoolModel
{
    protected $table = 'student_concessions';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'fee_head_id' => 'integer',
            'value' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'approved_by' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'approved_by');
    }
}

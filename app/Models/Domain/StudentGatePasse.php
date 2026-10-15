<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGatePasse extends SchoolModel
{
    protected $table = 'student_gate_passes';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'pickup_guardian_id' => 'integer',
            'approved_by' => 'integer',
            'departed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function pickupGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'pickup_guardian_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'approved_by');
    }
}

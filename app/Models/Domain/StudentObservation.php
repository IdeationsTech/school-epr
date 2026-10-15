<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentObservation extends SchoolModel
{
    protected $table = 'student_observations';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'author_membership_id' => 'integer',
            'observed_on' => 'date',
            'follow_up_on' => 'date',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function authorMembership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'author_membership_id');
    }
}

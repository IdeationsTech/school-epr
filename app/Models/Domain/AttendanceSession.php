<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSession extends SchoolModel
{
    protected $table = 'attendance_sessions';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'section_id' => 'integer',
            'attendance_date' => 'date',
            'submitted_by' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'submitted_by');
    }
}

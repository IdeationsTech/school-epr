<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendanceRecord extends SchoolModel
{
    protected $table = 'staff_attendance_records';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'staff_profile_id' => 'integer',
            'campus_id' => 'integer',
            'attendance_date' => 'date',
            'leave_request_id' => 'integer',
            'recorded_by' => 'integer',
        ];
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'recorded_by');
    }
}

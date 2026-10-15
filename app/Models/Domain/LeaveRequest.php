<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends SchoolModel
{
    protected $table = 'leave_requests';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'staff_profile_id' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'approved_by' => 'integer',
        ];
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'approved_by');
    }
}

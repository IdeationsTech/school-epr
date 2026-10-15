<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtmAppointment extends SchoolModel
{
    protected $table = 'ptm_appointments';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'guardian_id' => 'integer',
            'staff_profile_id' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'follow_up_on' => 'date',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }
}

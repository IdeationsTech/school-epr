<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAdvance extends SchoolModel
{
    protected $table = 'staff_advances';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'staff_profile_id' => 'integer',
            'amount' => 'decimal:2',
            'paid_on' => 'date',
            'monthly_recovery' => 'decimal:2',
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

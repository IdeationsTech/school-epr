<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends SchoolModel
{
    protected $table = 'staff_profiles';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'membership_id' => 'integer',
            'hired_on' => 'date',
        ];
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'membership_id');
    }
}

<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipCampus extends SchoolModel
{
    protected $table = 'membership_campuses';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'membership_id' => 'integer',
            'campus_id' => 'integer',
        ];
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'membership_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }
}

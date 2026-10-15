<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipRole extends SchoolModel
{
    protected $table = 'membership_roles';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'membership_id' => 'integer',
            'role_id' => 'integer',
        ];
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'membership_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends SchoolModel
{
    protected $table = 'audit_logs';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'actor_membership_id' => 'integer',
            'target_id' => 'integer',
            'changes' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function actorMembership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'actor_membership_id');
    }
}

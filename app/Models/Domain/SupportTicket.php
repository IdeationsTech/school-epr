<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends SchoolModel
{
    protected $table = 'support_tickets';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'opened_by' => 'integer',
        ];
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'opened_by');
    }
}

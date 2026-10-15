<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportReply extends SchoolModel
{
    protected $table = 'support_replies';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'support_ticket_id' => 'integer',
            'author_user_id' => 'integer',
        ];
    }

    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_user_id');
    }
}

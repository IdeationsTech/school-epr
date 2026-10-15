<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaryShare extends SchoolModel
{
    protected $table = 'diary_shares';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'diary_id' => 'integer',
            'confirmed_by' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    public function diary(): BelongsTo
    {
        return $this->belongsTo(Diary::class, 'diary_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'confirmed_by');
    }
}

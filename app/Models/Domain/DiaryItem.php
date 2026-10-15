<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaryItem extends SchoolModel
{
    protected $table = 'diary_items';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'diary_id' => 'integer',
            'subject_id' => 'integer',
            'author_membership_id' => 'integer',
            'no_homework' => 'boolean',
        ];
    }

    public function diary(): BelongsTo
    {
        return $this->belongsTo(Diary::class, 'diary_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function authorMembership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'author_membership_id');
    }
}

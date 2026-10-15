<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diary extends SchoolModel
{
    protected $table = 'diaries';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'section_id' => 'integer',
            'diary_date' => 'date',
            'revision' => 'integer',
            'published_by' => 'integer',
            'published_at' => 'datetime',
            'supersedes_id' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'published_by');
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(Diary::class, 'supersedes_id');
    }
}

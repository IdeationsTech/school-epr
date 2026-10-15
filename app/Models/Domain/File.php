<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends SchoolModel
{
    protected $table = 'files';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'uploaded_by' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'uploaded_by');
    }
}

<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintUpdate extends SchoolModel
{
    protected $table = 'complaint_updates';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'parent_complaint_id' => 'integer',
            'author_membership_id' => 'integer',
        ];
    }

    public function parentComplaint(): BelongsTo
    {
        return $this->belongsTo(ParentComplaint::class, 'parent_complaint_id');
    }

    public function authorMembership(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'author_membership_id');
    }
}

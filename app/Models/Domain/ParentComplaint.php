<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentComplaint extends SchoolModel
{
    protected $table = 'parent_complaints';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'student_id' => 'integer',
            'guardian_id' => 'integer',
            'assigned_to' => 'integer',
            'follow_up_on' => 'date',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'assigned_to');
    }
}

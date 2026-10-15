<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePlan extends SchoolModel
{
    protected $table = 'fee_plans';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'academic_session_id' => 'integer',
            'class_id' => 'integer',
            'campus_id' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }
}

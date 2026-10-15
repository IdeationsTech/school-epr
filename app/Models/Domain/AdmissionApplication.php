<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApplication extends SchoolModel
{
    protected $table = 'admission_applications';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'inquiry_id' => 'integer',
            'campus_id' => 'integer',
            'class_id' => 'integer',
            'academic_session_id' => 'integer',
            'birth_date' => 'date',
            'guardian_details' => 'array',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(AdmissionInquiry::class, 'inquiry_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }
}

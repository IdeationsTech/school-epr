<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateIssue extends SchoolModel
{
    protected $table = 'certificate_issues';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'student_id' => 'integer',
            'template_id' => 'integer',
            'payload' => 'array',
            'issued_by' => 'integer',
            'issued_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'issued_by');
    }
}

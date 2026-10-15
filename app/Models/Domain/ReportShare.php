<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportShare extends SchoolModel
{
    protected $table = 'report_shares';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'report_snapshot_id' => 'integer',
            'consolidated_snapshot_id' => 'integer',
            'guardian_id' => 'integer',
            'confirmed_by' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    public function reportSnapshot(): BelongsTo
    {
        return $this->belongsTo(ReportSnapshot::class, 'report_snapshot_id');
    }

    public function consolidatedSnapshot(): BelongsTo
    {
        return $this->belongsTo(ConsolidatedResultSnapshot::class, 'consolidated_snapshot_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(SchoolMembership::class, 'confirmed_by');
    }
}

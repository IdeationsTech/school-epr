<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructure extends SchoolModel
{
    protected $table = 'salary_structures';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'staff_profile_id' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }
}

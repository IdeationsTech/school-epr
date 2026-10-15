<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructureItem extends SchoolModel
{
    protected $table = 'salary_structure_items';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'salary_structure_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }
}

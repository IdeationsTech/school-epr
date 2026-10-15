<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends SchoolModel
{
    protected $table = 'test_results';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'test_id' => 'integer',
            'enrollment_id' => 'integer',
            'marks' => 'decimal:2',
            'revision' => 'integer',
        ];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
}

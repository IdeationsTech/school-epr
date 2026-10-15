<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPaper extends SchoolModel
{
    protected $table = 'exam_papers';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'test_id' => 'integer',
            'scheduled_on' => 'date',
        ];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}

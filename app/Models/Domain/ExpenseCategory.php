<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseCategory extends SchoolModel
{
    protected $table = 'expense_categories';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
        ];
    }
}

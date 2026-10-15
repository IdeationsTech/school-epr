<?php
namespace App\Models;
use App\Support\SchoolContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;
abstract class SchoolModel extends Model
{
    protected $guarded = ['*'];
    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $builder): void {
            $id = app(SchoolContext::class)->id();
            $id === null ? $builder->whereRaw('1 = 0')
                : $builder->where($builder->getModel()->qualifyColumn('school_id'), $id);
        });
        static::creating(function (SchoolModel $model): void {
            $id = app(SchoolContext::class)->requireId();
            if ($model->school_id !== null && (int) $model->school_id !== $id) {
                throw new LogicException('Cross-school create refused.');
            }
            $model->school_id = $id;
        });
        $check = function (SchoolModel $model): void {
            $id = app(SchoolContext::class)->requireId();
            if ((int) $model->getOriginal('school_id') !== $id || (int) $model->school_id !== $id) {
                throw new LogicException('Cross-school write refused.');
            }
        };
        static::updating($check);
        static::deleting($check);
    }
}

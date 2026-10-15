<?php
namespace App\Models\Domain;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SchoolRolePermission extends SchoolModel
{
    protected $table = 'school_role_permissions';
    protected function casts(): array { return ['enabled'=>'boolean']; }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function permission(): BelongsTo { return $this->belongsTo(Permission::class); }
}

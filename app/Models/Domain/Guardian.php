<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardian extends SchoolModel
{
    protected $table = 'guardians';
    protected $guarded = ['*'];
    protected $hidden = ['cnic'];

    protected function casts(): array
    {
        return [
        ];
    }
    public function setCnicAttribute(?string $value): void
    {
        $normalized=\App\Rules\PakistanIdentity::normalize($value);
        if($normalized!==null && !preg_match('/^[1-7][0-9]{12}$/D',$normalized))throw new \InvalidArgumentException('Invalid Pakistan identity number.');
        $this->attributes['cnic']=$normalized;
    }

}

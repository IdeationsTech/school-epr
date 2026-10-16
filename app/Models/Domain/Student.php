<?php

namespace App\Models\Domain;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends SchoolModel
{
    protected $table = 'students';
    protected $guarded = ['*'];
    protected $hidden = ['identity_number'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'admitted_on' => 'date',
            'application_id' => 'integer',
            'photo_file_id' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    public function photoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'photo_file_id');
    }
    public function setIdentityNumberAttribute(?string $value): void
    {
        $normalized=\App\Rules\PakistanIdentity::normalize($value);
        if($normalized!==null && !preg_match('/^[1-7][0-9]{12}$/D',$normalized))throw new \InvalidArgumentException('Invalid Pakistan identity number.');
        $this->attributes['identity_number']=$normalized;
    }

}

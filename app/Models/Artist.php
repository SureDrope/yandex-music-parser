<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];
    public function tracks()
    {
        return $this->hasMany(Track::class);
    }
}

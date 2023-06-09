<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}

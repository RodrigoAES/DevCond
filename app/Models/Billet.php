<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Unit;

class Billet extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function unit() {
        return $this->belongsTo(Unit::class);
    }
}

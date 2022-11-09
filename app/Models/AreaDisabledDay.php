<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Area;

class AreaDisabledDay extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function area() {
        return $this->belongTo(Area::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Area;

class AreaReservation extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function area() {
        return $this->belongsTo(Area::class);
    }
}

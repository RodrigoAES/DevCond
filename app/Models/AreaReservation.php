<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Unit;
use App\Models\Area;

class AreaReservation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'unit_id', 'area_id', 'reservation_datetime', 'reservation_endtime'
    ];
    
    public function unit() {
        return $this->belongsTo(Unit::class);
    }

    public function area() {
        return $this->belongsTo(Area::class);
    }
}

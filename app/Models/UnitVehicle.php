<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Unit;

class UnitVehicle extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $hidden = [
        'unit_id'
    ];

    public function unit() {
        return $this->belongsTo(Unit::class);
    }
}

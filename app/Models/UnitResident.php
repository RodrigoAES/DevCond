<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Unit;
use App\Models\User;

class UnitResident extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $hidden = [
        'unit_id'
    ];

    public function unit() {
        return $this->belongsTo(Unit::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

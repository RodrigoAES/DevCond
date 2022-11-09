<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\UnitResident;
use App\Models\UnitVehicle;
use App\Models\UnitPet;
use App\Models\Billet;
use App\Models\Warnings;

class Unit extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function owner() {
        return $this->belogsTo(User::class);
    }

    public function residents() {
        return $this->hasMany(UnitResident::class);
    }

    public function vehicles() {
        return $this->hasMany(UnitVehicle::class);
    }

    public function pets() {
        return $this->hasMany(UnitPet::class);
    }

    public function billets() {
        return $this->hasMany(Billet::class);
    }

    public function warnings() {
        return $this->hasMany(Warning::class);
    }
}

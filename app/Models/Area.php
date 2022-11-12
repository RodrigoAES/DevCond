<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\AreaDisabledDay;

class Area extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function disabledDays() {
        return $this->hasMany(AreaDisabledDay::class);
    }
}

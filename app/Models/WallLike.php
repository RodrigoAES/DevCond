<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Wall;
use App\Models\User;

class WallLike extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function wall() {
        return $this->belongsTo(Wall::class);
    }

    public function user() {
        return $this->BelongsTo(User::class);
    }
}

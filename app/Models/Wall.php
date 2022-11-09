<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\WallLike;

class Wall extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function likes() {
        return $this->hasMany(WallLike::class);
    }
}

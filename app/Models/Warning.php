<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Unit;

class Warning extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'unit_id', 'title', 'status', 'created_at', 'photos'
    ];

    public function unit() {
        return $this->belongsTo(Unit::class);
    }
}

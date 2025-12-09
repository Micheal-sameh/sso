<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'rashi', 'visits', 'eftkad'];

    protected $casts = [
        'rashi' => 'encrypted',
        'visits' => 'encrypted',
        'eftkad' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::creating(function ($project) {
            do {
                $secretKey = bin2hex(random_bytes(32));
            } while (static::where('secret_key', $secretKey)->exists());

            $project->secret_key = $secretKey;
        });
    }
}

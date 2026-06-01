<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class Bucket extends Model
{
    public $timestamps = true;
    
    protected $fillable = [
        'user_id',
        'bucket_name',
        'allocated_size_mb',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(ObjectStorage::class, 'bucket_id');
    }
}
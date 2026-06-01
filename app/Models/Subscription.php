<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'status', 
        'start_date',
        'end_date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function cloudCredential()
    {
        return $this->hasOne(CloudCredential::class, 'subscription_id');
    }
}
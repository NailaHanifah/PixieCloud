<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudCredential extends Model
{
    use HasFactory;

    protected $table = 'cloud_credentials';

    protected $fillable = [
        'subscription_id',
        'ministack_access_key',
        'ministack_secret_key', 
        'status', 
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
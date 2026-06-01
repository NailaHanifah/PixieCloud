<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectStorage extends Model
{
    use HasFactory;

    protected $table = 'objects';

    public $timestamps = false; 

    protected $fillable = [
        'bucket_id',
        'object_key',       
        'content_type',     
        'file_size_bytes',  
        'file_url',         
        'object_metadata', 
    ];

    public function bucket(): BelongsTo
    {
        return $this->belongsTo(Bucket::class, 'bucket_id');
    }
}
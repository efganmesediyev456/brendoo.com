<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'story_id', 
        'type', 
        'file_path', 
        'file_name', 
        'mime_type', 
        'file_size', 
        'is_primary', 
        'is_active'
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path 
            ? asset('storage/' . $this->file_path) 
            : asset('default-image.jpg');
    }

    public function getVideoUrlAttribute()
    {
        return $this->file_path 
            ? asset('storage/' . $this->file_path) 
            : asset('default-image.jpg');
    }
}

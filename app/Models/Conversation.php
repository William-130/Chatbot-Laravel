<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'session_id',
        'user_message',
        'bot_response',
        'website_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function ragWebsite()
    {
        return $this->belongsTo(RagWebsite::class, 'website_id');
    }
}

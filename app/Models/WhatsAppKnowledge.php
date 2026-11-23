<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppKnowledge extends Model
{
    protected $table = 'whatsapp_knowledge';

    protected $fillable = [
        'question',
        'normalized_question',
        'answer',
        'use_count',
    ];
}

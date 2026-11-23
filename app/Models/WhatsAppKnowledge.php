<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppKnowledge extends Model
{
    protected $table = 'whatsapp_knowledge';

    protected $fillable = [
        'question',
        'normalized_question',
        'normalized_question_hash',
        'answer',
        'use_count',
    ];
}

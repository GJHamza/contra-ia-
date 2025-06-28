<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenAIUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'tokens',
        'cost',
    ];

    protected $casts = [
        'date' => 'date',
        'tokens' => 'integer',
        'cost' => 'float',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GptHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt',
        'response',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

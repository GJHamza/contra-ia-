<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GptRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requested_at',
    ];

    public $timestamps = false;
}

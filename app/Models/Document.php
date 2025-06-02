<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'status',
        'generated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function container()
{
    return $this->belongsTo(Container::class);
}
}

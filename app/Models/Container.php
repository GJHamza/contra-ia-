<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function clauses()
    {
        return $this->hasMany(Clause::class);
    }
}

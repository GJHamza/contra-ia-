<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClauseType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function clauses()
    {
        return $this->hasMany(Clause::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clause extends Model
{
    protected $fillable = [
        'container_id',
        'title',
        'content',
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }
}

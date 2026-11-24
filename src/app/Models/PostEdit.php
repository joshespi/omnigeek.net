<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostEdit extends Model
{
    protected $fillable = ['post_id', 'note', 'edited_at'];

    protected $casts = ['edited_at' => 'datetime'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

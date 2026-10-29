<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionResultSnapshot extends Model
{
    protected $fillable = ['election_id', 'results', 'published_by', 'published_at'];

    protected function casts(): array
    {
        return [
            'results' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}

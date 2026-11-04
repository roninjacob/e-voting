<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionStatusLog extends Model
{
    protected $fillable = ['election_id', 'from_status', 'to_status', 'changed_by', 'note', 'changed_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

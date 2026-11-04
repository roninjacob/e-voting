<?php

namespace App\Models;

use Database\Factories\CicsScElectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicsScElection extends Model
{
    /** @use HasFactory<CicsScElectionFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'starts_at', 'ends_at', 'status', 'created_by'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(CicsScPosition::class)->orderBy('sort_order');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(CicsScElectionStatusLog::class);
    }

    public function voteReceipts(): HasMany
    {
        return $this->hasMany(CicsScVoteReceipt::class);
    }

    public static function userIsEligible(User $user): bool
    {
        return $user->role === 'voter'
            && $user->is_active
            && $user->is_verified
            && $user->block?->program === 'BSIT';
    }
}

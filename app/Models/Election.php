<?php

namespace App\Models;

use Database\Factories\ElectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Election extends Model
{
    /** @use HasFactory<ElectionFactory> */
    use HasFactory;

    protected $fillable = ['block_id', 'scope', 'title', 'description', 'starts_at', 'ends_at', 'status', 'created_by'];

    public function isAllCics(): bool
    {
        return $this->scope === 'all_cics';
    }

    public function isEligible(User $user): bool
    {
        return $user->is_verified
            && ($this->isAllCics() || $user->block_id === $this->block_id);
    }

    public function scopeLabel(): string
    {
        return $this->isAllCics() ? 'All CICS blocks' : ($this->block ? 'BSIT '.$this->block->section : 'No block');
    }

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class)->orderBy('sort_order');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ElectionStatusLog::class);
    }

    public function voteReceipts(): HasMany
    {
        return $this->hasMany(VoteReceipt::class);
    }

    public function ballotVotes(): HasMany
    {
        return $this->hasMany(BallotVote::class);
    }

    public function resultSnapshots(): HasMany
    {
        return $this->hasMany(ElectionResultSnapshot::class);
    }
}

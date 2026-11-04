<?php

namespace App\Models;

use Database\Factories\CicsScPositionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicsScPosition extends Model
{
    /** @use HasFactory<CicsScPositionFactory> */
    use HasFactory;

    protected $fillable = ['cics_sc_election_id', 'name', 'max_votes', 'is_required', 'sort_order'];

    protected function casts(): array
    {
        return ['max_votes' => 'integer', 'is_required' => 'boolean', 'sort_order' => 'integer'];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(CicsScElection::class, 'cics_sc_election_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(CicsScCandidate::class);
    }
}

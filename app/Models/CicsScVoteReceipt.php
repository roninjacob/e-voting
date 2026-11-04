<?php

namespace App\Models;

use Database\Factories\CicsScVoteReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CicsScVoteReceipt extends Model
{
    /** @use HasFactory<CicsScVoteReceiptFactory> */
    use HasFactory;

    protected $fillable = ['cics_sc_election_id', 'voter_id', 'block_id', 'receipt_reference', 'payload_hash', 'status', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(CicsScElection::class, 'cics_sc_election_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}

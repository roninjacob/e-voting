<?php

namespace App\Models;

use Database\Factories\CicsScElectionStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CicsScElectionStatusLog extends Model
{
    /** @use HasFactory<CicsScElectionStatusLogFactory> */
    use HasFactory;

    protected $fillable = ['cics_sc_election_id', 'from_status', 'to_status', 'changed_by', 'note', 'changed_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(CicsScElection::class, 'cics_sc_election_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

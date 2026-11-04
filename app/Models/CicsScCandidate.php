<?php

namespace App\Models;

use Database\Factories\CicsScCandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CicsScCandidate extends Model
{
    /** @use HasFactory<CicsScCandidateFactory> */
    use HasFactory;

    protected $fillable = ['cics_sc_position_id', 'full_name', 'party', 'platform', 'education', 'experience', 'awards', 'photo_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CicsScPosition::class, 'cics_sc_position_id');
    }
}

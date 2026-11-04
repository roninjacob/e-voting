<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteReceipt extends Model
{
    protected $fillable = ['election_id', 'voter_id', 'block_id', 'receipt_reference', 'payload_hash', 'status', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public static function computePayloadHash(string $receiptReference): string
    {
        $key = (string) config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $decodedKey = base64_decode(substr($key, 7), true);
            $key = $decodedKey === false ? $key : $decodedKey;
        }

        return hash_hmac('sha256', $receiptReference, $key);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
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

<?php

namespace App\Models;

use Database\Factories\BlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    /** @use HasFactory<BlockFactory> */
    use HasFactory;

    protected $fillable = ['program', 'section', 'year_level', 'semester', 'track'];

    protected function casts(): array
    {
        return ['year_level' => 'integer', 'semester' => 'integer'];
    }

    public function elections(): HasMany
    {
        return $this->hasMany(Election::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

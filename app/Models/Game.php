<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'image',
        'download_link',
        'is_downloadable',
    ];

    protected function casts(): array
    {
        return [
            'is_downloadable' => 'boolean',
        ];
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}


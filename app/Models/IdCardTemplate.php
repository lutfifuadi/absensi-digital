<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdCardTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'background_path',
        'background_path_back',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor untuk mendapatkan elemen sisi Front (backward compatible).
     */
    public function getFrontElementsAttribute(): ?array
    {
        if (isset($this->config['front']['elements'])) {
            return $this->config['front']['elements'];
        }
        if (isset($this->config['elements'])) {
            return $this->config['elements'];
        }
        return null;
    }

    /**
     * Accessor untuk mendapatkan elemen sisi Back.
     */
    public function getBackElementsAttribute(): ?array
    {
        if (isset($this->config['back']['elements'])) {
            return $this->config['back']['elements'];
        }
        return null;
    }

    /**
     * Accessor untuk mengecek apakah template memiliki sisi Back aktif.
     */
    public function getHasBackSideAttribute(): bool
    {
        if (isset($this->config['back']['elements'])) {
            foreach ($this->config['back']['elements'] as $element) {
                if (!empty($element['show'])) {
                    return true;
                }
            }
        }
        return false;
    }
}

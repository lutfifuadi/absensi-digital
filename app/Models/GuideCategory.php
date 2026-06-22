<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuideCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'parent_id',
        'order',
    ];

    /**
     * Relasi ke kategori induk (parent category).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(GuideCategory::class, 'parent_id');
    }

    /**
     * Relasi ke sub-kategori (child categories).
     */
    public function children(): HasMany
    {
        return $this->hasMany(GuideCategory::class, 'parent_id')
            ->orderBy('order');
    }

    /**
     * Relasi ke guides yang termasuk dalam kategori ini.
     */
    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class, 'category_id');
    }

    /**
     * Scope untuk kategori yang merupakan root (tidak memiliki parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope untuk kategori yang memiliki parent (sub-kategori).
     */
    public function scopeSub($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope untuk mengurutkan berdasarkan kolom order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guide extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'category_id',
        'role_target',
        'featured_image',
        'author_id',
        'status',
        'order',
        'is_featured',
        'metadata',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'metadata' => 'array',
        'published_at' => 'datetime',
        'order' => 'integer',
    ];

    /**
     * Relasi ke kategori panduan.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(GuideCategory::class, 'category_id');
    }

    /**
     * Relasi ke penulis (user).
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope untuk hanya menampilkan guide yang sudah dipublikasikan.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope untuk hanya menampilkan guide dalam status draft.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope untuk featured guide.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->published()
            ->orderBy('order')
            ->orderBy('published_at', 'desc');
    }

    /**
     * Scope untuk guide yang ditargetkan untuk role tertentu.
     *
     * Kolom `role_target` menyimpan nilai comma-separated, misal: "siswa,guru".
     *
     * @param  string|array  $roles
     */
    public function scopeForRole($query, $roles)
    {
        $roles = (array) $roles;

        return $query->where(function ($q) use ($roles) {
            foreach ($roles as $role) {
                $q->orWhere('role_target', $role)
                  ->orWhere('role_target', 'LIKE', $role . ',%')
                  ->orWhere('role_target', 'LIKE', '%,' . $role)
                  ->orWhere('role_target', 'LIKE', '%,' . $role . ',%')
                  ->orWhereNull('role_target')
                  ->orWhere('role_target', 'public');
            }
        });
    }

    /**
     * Scope pencarian berdasarkan judul atau konten.
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('content', 'like', "%{$keyword}%")
              ->orWhere('excerpt', 'like', "%{$keyword}%");
        });
    }
}

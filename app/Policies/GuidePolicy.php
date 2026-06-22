<?php

namespace App\Policies;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\User;

class GuidePolicy
{
    /**
     * Siapapun bisa melihat daftar panduan (public).
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Siapapun bisa melihat detail panduan yang sudah dipublikasikan (public).
     */
    public function view(?User $user, Guide $guide): bool
    {
        return $guide->status === 'published';
    }

    /**
     * Hanya super_admin dan admin_sekolah yang bisa membuat panduan baru.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
        ]);
    }

    /**
     * Hanya super_admin dan admin_sekolah yang bisa mengubah panduan.
     */
    public function update(User $user, Guide $guide): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
        ]);
    }

    /**
     * Hanya super_admin dan admin_sekolah yang bisa menghapus panduan.
     */
    public function delete(User $user, Guide $guide): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
        ]);
    }

    /**
     * Hanya super_admin yang bisa memulihkan panduan yang sudah dihapus.
     */
    public function restore(User $user, Guide $guide): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Hanya super_admin yang bisa menghapus panduan secara permanen.
     */
    public function forceDelete(User $user, Guide $guide): bool
    {
        return $user->isSuperAdmin();
    }

    // ─── Policy untuk Kategori (GuideCategory) ───────────────────────────────

    /**
     * Siapapun bisa melihat daftar kategori (public).
     */
    public function viewAnyCategory(?User $user): bool
    {
        return true;
    }

    /**
     * Hanya super_admin dan admin_sekolah yang bisa membuat kategori baru.
     */
    public function createCategory(User $user): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
        ]);
    }

    /**
     * Hanya super_admin dan admin_sekolah yang bisa mengubah kategori.
     */
    public function updateCategory(User $user, GuideCategory $category): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
        ]);
    }

    /**
     * Hanya super_admin dan admin_sekolah yang bisa menghapus kategori.
     */
    public function deleteCategory(User $user, GuideCategory $category): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
        ]);
    }
}

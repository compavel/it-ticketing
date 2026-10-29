<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\UserRole;

class CategoryPolicy
{
    /**
     * Determine whether the user can view the categories.
     *
     * Semua user login boleh melihat daftar kategori — dipakai juga untuk
     * dropdown "Buat Ticket". Ini bukan data sensitif.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create categories.
     *
     * Hanya admin yang boleh menambah kategori (master data).
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can update the category.
     *
     * Hanya admin.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can delete the category.
     *
     * Hanya admin. Catatan: kategori yang masih dipakai ticket akan
     * ditolak lagi di controller (foreign key constraint) — keputusan
     * akhir ada di sana karena butuh cek data.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->role === UserRole::Admin;
    }
}

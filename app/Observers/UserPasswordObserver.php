<?php

namespace App\Observers;

use App\Models\User;

class UserPasswordObserver
{
    /**
     * Before saving, if password is being changed and a pending plain
     * password is available, sync it to password_plain.
     */
    public function saving(User $user): void
    {
        $pendingPlain = User::getPendingPlainPassword();

        if ($pendingPlain !== null && $user->isDirty('password')) {
            $user->password_plain = $pendingPlain;
        }

        // Always clear after use to prevent stale data leaking to the next save
        User::clearPendingPlainPassword();
    }
}

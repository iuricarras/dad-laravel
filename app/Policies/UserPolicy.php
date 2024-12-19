<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        if ($user->type == 'A') {
            return true;
        }
        return false;
    }
    public function view(User $user, User $targetUser): bool{
        return $user->id === $targetUser->id || $user->type == 'A';
    }
    public function viewMe(User $user, User $targetUser): bool{
        return $user->id === $targetUser->id;
    }
    public function createAdmin(User $user): bool{
        return $user->type == 'A';
    }
    public function player(User $user): bool{
        return $user->type != 'A';
    }
    public function update(User $user, User $user2): bool
    {
        return true;
    }
    public function delete(User $user, User $targetUser): bool
    {
        return $user->id === $targetUser->id || $user->type == 'A';
    }
}

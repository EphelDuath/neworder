<?php

namespace App\Policies;

use App\User;
use App\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list all the account.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function list(User $user)
    {
        return $user->can('list-account');
    }

    /**
     * Determine whether the user can view the account.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->can('list-account');
    }

    /**
     * Determine whether the user can create accounts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-account');
    }

    /**
     * Determine whether the user can update the account.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->can('edit-account');
    }

    /**
     * Determine whether the user can delete the account.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->can('delete-account');
    }
}

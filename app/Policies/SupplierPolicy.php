<?php

namespace App\Policies;

use App\User;
use App\Supplier;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list all the supplier.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function list(User $user)
    {
        return $user->can('list-supplier');
    }

    /**
     * Determine whether the user can view the supplier.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->can('list-supplier');
    }

    /**
     * Determine whether the user can create suppliers.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-supplier');
    }

    /**
     * Determine whether the user can update the supplier.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->can('edit-supplier');
    }

    /**
     * Determine whether the user can delete the supplier.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->can('delete-supplier');
    }
}

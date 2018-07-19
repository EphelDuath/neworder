<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can fetch pre-requisites.
     *
     * @param  \App\User  $user
     * @param  string     $type
     * @return mixed
     */
    public function preRequisite(User $user, $type)
    {
        return $user->can('create-'.$type) || $user->can('edit-'.$type);
    }
    
    /**
     * Determine whether the user can list all the transactions.
     *
     * @param  \App\User  $user
     * @param  string     $type
     * @return mixed
     */
    public function list(User $user, $type)
    {
        return  $user->can('list-'.$type);
    }

    /**
     * Determine whether the user can view the transaction.
     *
     * @param  \App\User  $user
     * @param  string     $type
     * @return mixed
     */
    public function view(User $auth_user, $type)
    {
        return  $auth_user->can('list-'.$type);
    }

    /**
     * Determine whether the user can create transactions.
     *
     * @param  \App\User  $user
     * @param  string     $type
     * @return mixed
     */
    public function create(User $user, $type)
    {
        return $user->can('create-'.$type);
    }

    /**
     * Determine whether the user can update the transaction.
     *
     * @param  \App\User  $user
     * @param  string     $type
     * @return mixed
     */
    public function update(User $user, $type)
    {
        return $user->can('edit-'.$type);
    }

    /**
     * Determine whether the user can delete the transaction.
     *
     * @param  \App\User  $user
     * @param  string     $type
     * @return mixed
     */
    public function delete(User $user, $type)
    {
        return $user->can('delete-'.$type);
    }
}

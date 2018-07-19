<?php

namespace App\Policies;

use App\User;
use App\Quotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can fetch pre-requisites for quotation.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function preRequisite(User $user)
    {
        return $user->hasAnyPermission(['create-quotation','edit-quotation']);
    }

    /**
     * Determine whether the user can list all the quotation.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function list(User $user)
    {
        return $user->can('list-quotation');
    }

    /**
     * Determine whether the user can view the quotation.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->can('list-quotation');
    }

    /**
     * Determine whether the user can create quotations.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-quotation');
    }

    /**
     * Determine whether the user can update the quotation.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->can('edit-quotation');
    }

    /**
     * Determine whether the user can delete the quotation.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->can('delete-quotation');
    }
}

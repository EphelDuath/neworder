<?php

namespace App\Policies;

use App\User;
use App\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can fetch pre-requisites for invoice.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function preRequisite(User $user)
    {
        return $user->hasAnyPermission(['create-invoice','edit-invoice']);
    }

    /**
     * Determine whether the user can list all the invoice.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function list(User $user)
    {
        return $user->can('list-invoice');
    }

    /**
     * Determine whether the user can view the invoice.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->can('list-invoice');
    }

    /**
     * Determine whether the user can create invoices.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-invoice');
    }

    /**
     * Determine whether the user can update the invoice.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->can('edit-invoice');
    }

    /**
     * Determine whether the user can list payment of the invoice.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function listPayment(User $user)
    {
        return $user->can('edit-invoice') || $user->can('make-invoice-payment');
    }

    /**
     * Determine whether the user can delete the invoice.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->can('delete-invoice');
    }

    /**
     * Determine whether the user can make payment for the invoice.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function makePayment(User $user)
    {
        return $user->can('make-invoice-payment');
    }
}

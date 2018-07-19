<?php

namespace App\Policies;

use App\User;
use App\Coupon;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list all the coupon.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function list(User $user)
    {
        return $user->can('list-coupon');
    }

    /**
     * Determine whether the user can view the coupon.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->can('list-coupon');
    }

    /**
     * Determine whether the user can create coupons.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-coupon');
    }

    /**
     * Determine whether the user can update the coupon.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->can('edit-coupon');
    }

    /**
     * Determine whether the user can delete the coupon.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->can('delete-coupon');
    }
}

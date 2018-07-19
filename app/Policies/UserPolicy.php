<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
     * Determine whether the user can list all the models.
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
     * Determine whether the user can view the model.
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
     * Determine whether the user can create model.
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
     * Determine whether the user can update the model.
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
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  string     $type
     * @return mixed
     */
    public function delete(User $user, $type)
    {
        return $user->can('delete-'.$type);
    }

    /**
     * Determine whether the user can reset password the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @param  string     $type
     * @return mixed
     */
    public function forceResetUserPassword(User $user, User $model, $type)
    {
        return $user->can('force-reset-password-'.$type) && $user->id != $model->id;
    }

    /**
     * Determine whether the user can email the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @param  string     $type
     * @return mixed
     */
    public function email(User $user, User $model, $type)
    {
        return $user->can('email-'.$type) && $user->id != $model->id;
    }

    /**
     * Determine whether the user can perform avatar related action to the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @param  string     $type
     * @return mixed
     */
    public function avatar(User $user, User $model, $type)
    {
        return ($user->id === $model->id) || ($user->id != $model->id && $user->can('edit-'.$type));
    }
}

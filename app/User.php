<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','activation_token'
    ];

    public function profile()
    {
        return $this->hasOne('App\Profile');
    }

    public function userPreference()
    {
        return $this->hasOne('App\UserPreference');
    }

    public function customerGroup()
    {
        return $this->belongsToMany('App\CustomerGroup', 'customer_group_user', 'user_id', 'customer_group_id');
    }

    public function invoices()
    {
        return $this->hasMany('App\Invoice','user_id');
    }

    public function customerInvoices()
    {
        return $this->hasMany('App\Invoice','customer_id');
    }

    public function quotations()
    {
        return $this->hasMany('App\Quotation','user_id');
    }

    public function customerQuotations()
    {
        return $this->hasMany('App\Quotation','customer_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction','user_id');
    }

    public function customerTransactions()
    {
        return $this->hasMany('App\Transaction','customer_id');
    }

    public function getNameAttribute()
    {
        return $this->Profile->first_name.' '.$this->Profile->last_name;
    }

    public function getNameWithEmailAttribute()
    {
        return $this->Profile->first_name.' '.$this->Profile->last_name.' ('.$this->email.')';
    }

    public function scopeFilterByEmail($q, $email = null)
    {
        if (! $email) {
            return $q;
        }

        return $q->where('email', 'like', '%'.$email.'%');
    }

    public function scopeFilterByFirstName($q, $first_name = null)
    {
        if (! $first_name) {
            return $q;
        }

        return $q->whereHas('profile', function ($q1) use ($first_name) {
            $q1->where('first_name', 'like', '%'.$first_name.'%');
        });
    }

    public function scopeFilterByLastName($q, $last_name = null)
    {
        if (! $last_name) {
            return $q;
        }

        return $q->whereHas('profile', function ($q1) use ($last_name) {
            $q1->where('last_name', 'like', '%'.$last_name.'%');
        });
    }

    public function scopeFilterByRoleId($q, $role_id = null)
    {
        if (! $role_id) {
            return $q;
        }

        return $q->whereHas('roles', function ($q) use ($role_id) {
            $q->where('role_id', '=', $role_id);
        });
    }

    public function scopeFilterByCompanyId($q, $company_id = null)
    {
        if (! $company_id) {
            return $q;
        }

        return $q->whereHas('profile', function ($q) use ($company_id) {
            $q->where('company_id', '=', $company_id);
        });
    }

    public function scopeFilterByDesignationId($q, $designation_id = null)
    {
        if (! $designation_id) {
            return $q;
        }

        return $q->whereHas('profile', function ($q) use ($designation_id) {
            $q->where('designation_id', '=', $designation_id);
        });
    }

    public function scopeFilterByCustomerGroupId($q, $customer_group_id = null)
    {
        if (! $customer_group_id) {
            return $q;
        }

        return $q->whereHas('customerGroup', function ($q) use ($customer_group_id) {
            $q->where('customer_group_id', '=', $customer_group_id);
        });
    }

    public function scopeFilterByStatus($q, $status = null)
    {
        if (! $status) {
            return $q;
        }

        return $q->where('status', '=', $status);
    }

    public function scopeCreatedAtDateBetween($q, $dates)
    {
        if ((! $dates['start_date'] || ! $dates['end_date']) && $dates['start_date'] <= $dates['end_date']) {
            return $q;
        }

        return $q->where('created_at', '>=', getStartOfDate($dates['start_date']))->where('created_at', '<=', getEndOfDate($dates['end_date']));
    }
}

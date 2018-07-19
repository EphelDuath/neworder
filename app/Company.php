<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'companies';

    public function profiles()
    {
        return $this->hasMany('App\Profile');
    }

    public function suppliers()
    {
        return $this->hasMany('App\Supplier');
    }

    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByEmail($q, $email)
    {
        if (! $email) {
            return $q;
        }

        return $q->where('email', 'like', '%'.$email.'%');
    }

    public function scopeFilterByName($q, $name)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', 'like', '%'.$name.'%');
    }

    public function scopeFilterByWebsite($q, $website)
    {
        if (! $website) {
            return $q;
        }

        return $q->where('website', 'like', '%'.$website.'%');
    }

    public function scopeFilterByPhone($q, $phone)
    {
        if (! $phone) {
            return $q;
        }

        return $q->where('phone', 'like', '%'.$phone.'%');
    }
}

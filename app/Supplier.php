<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'suppliers';

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
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

    public function scopeFilterByCompanyId($q, $company_id)
    {
        if (! $company_id) {
            return $q;
        }

        return $q->where('company_id', '=', $company_id);
    }

    public function scopeFilterByPhone($q, $phone)
    {
        if (! $phone) {
            return $q;
        }

        return $q->where('phone', 'like', '%'.$phone.'%');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'accounts';

    public function transaction()
    {
        return $this->hasMany('App\Transaction','account_id');
    }

    public function transfer()
    {
        return $this->hasMany('App\Transaction','from_account_id');
    }
    
    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByType($q, $type)
    {
        if (! $type) {
            return $q;
        }

        return $q->where('type', '=', $type);
    }

    public function scopeFilterByName($q, $name)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', 'like', '%'.$name.'%');
    }

    public function scopeFilterByNumber($q, $number)
    {
        if (! $number) {
            return $q;
        }

        return $q->where('number', 'like', '%'.$number.'%');
    }

    public function scopeFilterByBankName($q, $bank_name)
    {
        if (! $bank_name) {
            return $q;
        }

        return $q->where('bank_name', 'like', '%'.$bank_name.'%');
    }

    public function scopeFilterByBankBranch($q, $bank_branch)
    {
        if (! $bank_branch) {
            return $q;
        }

        return $q->where('bank_branch', 'like', '%'.$bank_branch.'%');
    }

    public function scopeFilterByBranchCode($q, $branch_code)
    {
        if (! $branch_code) {
            return $q;
        }

        return $q->where('branch_code', 'like', '%'.$branch_code.'%');
    }
}

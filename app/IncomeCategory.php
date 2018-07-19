<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncomeCategory extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'income_categories';

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

    public function scopeFilterByName($q, $name)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', 'like', '%'.$name.'%');
    }
}

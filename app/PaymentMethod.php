<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'payment_methods';

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public function getDetailAttribute()
    {
        return $this->name . " (" . ucfirst($this->type).")";
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

    public function scopeFilterByType($q, $type)
    {
        if (! $type) {
            return $q;
        }

        return $q->where('type', 'like', '%'.$type.'%');
    }
}

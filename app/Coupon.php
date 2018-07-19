<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'coupons';

    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByCode($q, $code)
    {
        if (! $code) {
            return $q;
        }

        return $q->where('code', '=', $code);
    }
}

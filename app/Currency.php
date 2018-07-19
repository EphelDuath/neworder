<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'currencies';

    public function itemPrices()
    {
        return $this->hasMany('App\ItemPrice');
    }

    public function invoices()
    {
        return $this->hasMany('App\Invoice');
    }

    public function quotations()
    {
        return $this->hasMany('App\Quotation');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public function getDetailAttribute()
    {
        return $this->name.' ('.$this->symbol.')';
    }

    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByIsDefault($q, $is_default)
    {
        return $q->where('is_default', '=', $is_default);
    }

    public function scopeFilterByName($q, $name)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', 'like', '%'.$name.'%');
    }
}

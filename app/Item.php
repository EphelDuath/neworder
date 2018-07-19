<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'items';

    public function getDetailAttribute()
    {
        return $this->name.' ('.$this->code.')';
    }

    public function itemCategory()
    {
        return $this->belongsTo('App\ItemCategory');
    }

    public function taxation()
    {
        return $this->belongsTo('App\Taxation');
    }

    public function itemPrice()
    {
        return $this->hasMany('App\ItemPrice');
    }

    public function invoiceItems()
    {
        return $this->hasMany('App\InvoiceItem');
    }

    public function quotationItems()
    {
        return $this->hasMany('App\QuotationItem');
    }

    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByExactName($q, $name)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', '=', $name);
    }

    public function scopeFilterByName($q, $name)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', 'like', '%'.$name.'%');
    }

    public function scopeFilterByExactCode($q, $code)
    {
        if (! $code) {
            return $q;
        }

        return $q->where('code', '=', $code);
    }

    public function scopeFilterByCode($q, $code)
    {
        if (! $code) {
            return $q;
        }

        return $q->where('code', 'like', '%'.$code.'%');
    }

    public function scopeFilterByItemCategoryId($q, $item_category_id)
    {
        if (! $item_category_id) {
            return $q;
        }

        return $q->where('item_category_id', '=', $item_category_id);
    }

    public function scopeFilterByTaxationId($q, $taxation)
    {
        if (! $taxation) {
            return $q;
        }

        return $q->where('taxation', '=', $taxation);
    }
}

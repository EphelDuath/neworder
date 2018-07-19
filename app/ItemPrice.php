<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    protected $fillable = ['item_id','currency_id'];
    protected $primaryKey = 'id';
    protected $table = 'item_prices';

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }
}

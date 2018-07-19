<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = ['uuid','quotation_id'];
    protected $primaryKey = 'id';
    protected $table = 'quotation_items';

    public function quotation()
    {
        return $this->belongsTo('App\Quotation');
    }

    public function item()
    {
        return $this->belongsTo('App\Item');
    }

    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByQuotationId($q, $quotation_id)
    {
        if (! $quotation_id) {
            return $q;
        }

        return $q->where('quotation_id', '=', $quotation_id);
    }

    public function scopeFilterByUuid($q, $uuid)
    {
        if (! $uuid) {
            return $q;
        }

        return $q->where('uuid', '=', $uuid);
    }
}

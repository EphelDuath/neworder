<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['uuid','invoice_id'];
    protected $primaryKey = 'id';
    protected $table = 'invoice_items';

    public function invoice()
    {
        return $this->belongsTo('App\Invoice');
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

    public function scopeFilterByInvoiceId($q, $invoice_id)
    {
        if (! $invoice_id) {
            return $q;
        }

        return $q->where('invoice_id', '=', $invoice_id);
    }

    public function scopeFilterByUuid($q, $uuid)
    {
        if (! $uuid) {
            return $q;
        }

        return $q->where('uuid', '=', $uuid);
    }
}

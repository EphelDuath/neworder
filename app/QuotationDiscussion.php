<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuotationDiscussion extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'quotation_discussions';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function quotation()
    {
        return $this->belongsTo('App\Quotation');
    }

    public function reply()
    {
        return $this->hasMany('App\QuotationDiscussion', 'reply_id', 'id');
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

    public function scopeFilterByUserId($q, $user_id)
    {
        if (! $user_id) {
            return $q;
        }

        return $q->where('user_id', '=', $user_id);
    }
}

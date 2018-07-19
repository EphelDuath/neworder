<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = ['uuid'];
    protected $primaryKey = 'id';
    protected $table = 'quotations';

    public function userAdded()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\User', 'customer_id');
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function quotationItem()
    {
        return $this->hasMany('App\QuotationItem');
    }

    public function getQuotationNumberAttribute()
    {
        return $this->prefix.str_pad($this->number, config('config.quotation_number_digit'), '0', STR_PAD_LEFT);
    }

    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByPrefix($q, $prefix)
    {
        if (! $prefix) {
            return $q;
        }

        return $q->where('prefix', 'like', '%'.$prefix.'%');
    }

    public function scopeFilterByNumber($q, $number)
    {
        if (! $number) {
            return $q;
        }

        return $q->where('number', 'like', '%'.$number.'%');
    }

    public function scopeFilterByCustomerId($q, $customer_id)
    {
        if (! $customer_id) {
            return $q;
        }

        return $q->where('customer_id', '=', $customer_id);
    }

    public function scopeFilterByStatus($q, $status)
    {
        if (! $status) {
            return $q;
        }

        if ($status === 'draft') {
            return $q->where(function ($q1) {
                $q1->filterByIsDraft(1)->filterByIsCancelled(0);
            });
        } elseif ($status === 'cancelled') {
            return $q->where(function ($q1) {
                $q1->filterByIsCancelled(1)->filterByIsDraft(0);
            });
        } elseif ($status === 'expired') {
            return $q->where(function ($q1) {
                $q1->whereNotIn('status', ['accepted','rejected','invoiced'])->filterByIsCancelled(0)->filterByIsDraft(0)->where('expiry_date_detail', '>', date('Y-m-d'));
            });
        } elseif ($status === 'invoiced') {
            return $q->where(function ($q1) {
                $q1->filterByIsDraft(0)->filterByIsCancelled(0)->whereStatus('invoiced');
            });
        } elseif ($status === 'sent') {
            return $q->where(function ($q1) {
                $q1->filterByIsDraft(0)->filterByIsCancelled(0)->where(function ($q) {
                    $q->whereStatus('sent')->orWhereNull('status');
                });
            });
        } else {
            return $q->where(function ($q1) use($status) {
                $q1->filterByIsDraft(0)->filterByIsCancelled(0)->where('status', '=', $status);
            });
        }
    }

    public function scopeFilterByIsCancelled($q, $is_cancelled)
    {
        return $q->where('is_cancelled', '=', $is_cancelled);
    }

    public function scopeFilterByIsDraft($q, $is_draft)
    {
        return $q->where('is_draft', '=', $is_draft);
    }

    public function scopeDateBetween($q, $dates)
    {
        if ((! $dates['start_date'] || ! $dates['end_date']) && $dates['start_date'] <= $dates['end_date']) {
            return $q;
        }

        return $q->where('date', '>=', getStartOfDate($dates['start_date']))->where('date', '<=', getEndOfDate($dates['end_date']));
    }

    public function scopeExpiryDateBetween($q, $dates)
    {
        if ((! $dates['start_date'] || ! $dates['end_date']) && $dates['start_date'] <= $dates['end_date']) {
            return $q;
        }

        return $q->where('expiry_date', '>=', getStartOfDate($dates['start_date']))->where('expiry_date', '<=', getEndOfDate($dates['end_date']));
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['uuid'];
    protected $primaryKey = 'id';
    protected $table = 'invoices';

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

    public function invoiceItem()
    {
        return $this->hasMany('App\InvoiceItem');
    }

    public function recurringInvoice()
    {
        return $this->hasMany('App\Invoice', 'recurring_invoice_id');
    }

    public function getInvoiceNumberAttribute()
    {
        return $this->prefix.str_pad($this->number, config('config.invoice_number_digit'), '0', STR_PAD_LEFT);
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
        } elseif ($status === 'unpaid') {
            return $q->where(function ($q1) {
                $q1->where('status', '!=', 'paid')->filterByIsDraft(0)->filterByIsCancelled(0)->where('due_date_detail', '>', date('Y-m-d'));
            });
        } elseif ($status === 'overdue') {
            return $q->where(function ($q1) {
                $q1->where('status', '!=', 'paid')->filterByIsCancelled(0)->filterByIsDraft(0)->where('due_date_detail', '<=', date('Y-m-d'));
            });
        } else {
            return $q->where(function ($q1) use($status){
                $q1->filterByIsDraft(0)->filterByIsCancelled(0)->where('status', '=', $status);
            });
        }
    }

    public function scopeFilterByIsRecurring($q, $is_recurring)
    {
        return $q->where('is_recurring', '=', $is_recurring);
    }

    public function scopeFilterByNextRecurringDate($q, $next_recurring_date)
    {
        if (! $next_recurring_date) {
            return $q;
        }

        return $q->where('next_recurring_date', '=', $next_recurring_date);
    }

    public function scopeFilterByRecurringInvoiceId($q, $recurring_invoice_id)
    {
        if (! $recurring_invoice_id) {
            return $q;
        }

        return $q->where('recurring_invoice_id', '=', $recurring_invoice_id);
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

    public function scopeDueDateBetween($q, $dates)
    {
        if ((! $dates['start_date'] || ! $dates['end_date']) && $dates['start_date'] <= $dates['end_date']) {
            return $q;
        }

        return $q->where('due_date', '>=', getStartOfDate($dates['start_date']))->where('due_date', '<=', getEndOfDate($dates['end_date']));
    }
}

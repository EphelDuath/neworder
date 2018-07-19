<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Taxation extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'taxations';

    public function getDetailAttribute()
    {
        return $this->name.' ('.formatNumber($this->value).'%)';
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

    public function scopeFilterByValue($q, $value)
    {
        if (! $value) {
            return $q;
        }

        return $q->where('value', '=', $value);
    }
}

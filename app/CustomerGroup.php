<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'customer_groups';

    public function user()
    {
        return $this->belongsToMany('App\User', 'customer_group_user', 'customer_group_id', 'user_id');
    }
    
    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByName($q, $name)
    {
        if (! $name) {
            return $q;
        }

        return $q->where('name', 'like', '%'.$name.'%');
    }
}

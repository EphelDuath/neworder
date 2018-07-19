<?php
namespace App;

use Eloquent;

class Profile extends Eloquent
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'profiles';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    public function designation()
    {
        return $this->belongsTo('App\Designation');
    }
}

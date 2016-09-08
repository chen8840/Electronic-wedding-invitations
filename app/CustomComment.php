<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomComment extends Model
{
    protected $guarded = [];

    public function invitation()
    {
        return $this->belongsTo('App\Invitation');
    }
}

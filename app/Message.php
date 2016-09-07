<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = [];

    public function sender()
    {
        return $this->belongsTo('App\qqUser', 'from');
    }

    public function receiver()
    {
        return $this->belongsTo('App\qqUser', 'to');
    }
}

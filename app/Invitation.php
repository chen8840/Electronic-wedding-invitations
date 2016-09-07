<?php

namespace App;

use App\Classes\StateFactory;
use Illuminate\Database\Eloquent\Model;
use Mockery\CountValidator\Exception;

class Invitation extends Model
{
    protected $dates = ['created_at', 'updated_at', 'wedding_date', 'last_publish_time'];
    protected $casts = [
        'images' => 'array',
    ];
    protected $guarded = [];
}

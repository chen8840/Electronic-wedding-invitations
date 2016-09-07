<?php

namespace App;


use Auth;
use Carbon\Carbon;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class qqUser extends OauthUser
{
    protected $table = 'qq_users';

    protected $fillable = ['name', 'email', 'password', 'nickname', 'public_id', 'img_url'];
    protected $hidden = ['password', 'remember_token'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = '';
    }

    public function getPasswordAttribute($value)
    {
        return bcrypt('');
    }

    public function receivedMessages()
    {
        return $this->hasMany('App\Message', 'to');
    }

    public function sendMessages()
    {
        return $this->hasMany('App\Message', 'from');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Auth;

abstract class OauthUser extends Model implements AuthenticatableContract
{
    use Authenticatable;
}

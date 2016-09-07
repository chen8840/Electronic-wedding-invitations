<?php

namespace App\Http\Controllers;

use App\Classes\Logic\qqUser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Socialite;

class LoginController extends Controller
{
    private static $redirectPath = '/';
    private static $loginPath = 'login';

    public function qqLogin()
    {
        return Socialite::driver('qq')->redirect();
    }

    public function qqLogout()
    {
        (new qqUser())->logout();
        return redirect(self::$loginPath);
    }

    public function qqCallBack()
    {
        $user = Socialite::driver('qq')->user();
        $userInfo = json_decode(json_encode($user),TRUE);
        $qqUser = new qqUser();
        $qqUser->createIfNotExist($userInfo['id'], $userInfo['avatar'], $userInfo['name'], $userInfo['email'], $userInfo['nickname']);
        if($qqUser->rememberMe()) {
            return redirect()->intended(self::$redirectPath);
        } else {
            return redirect(self::$loginPath);
        }
    }

    public static function loginPath()
    {
        return self::$loginPath;
    }

    public static function redirectPath()
    {
        return self::$redirectPath;
    }
}

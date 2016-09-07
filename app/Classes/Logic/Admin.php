<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/12/16
 * Time: 9:43 AM
 */

namespace App\Classes\Logic;

use Cache;
use DB;

class Admin
{
    private $rememberToken;

    public function getName()
    {
        return '管理员';
    }

    public function verify($id, $password)
    {
        if($id === $this->getID() && $password === $this->getPassword()) {
            $this->updateSessionForRememberToken($this->generateRememberToken());
            return true;
        } else {
            return false;
        }
    }

    public function remember($id, $password, $response)
    {
        if($this->verify($id, $password)) {
            Cache::forever('admin_remember_token', $this->rememberToken);
            $response->withCookie(cookie()->forever('admin_remember', $this->rememberToken));
            return true;
        }
        return false;
    }

    public function guest($request)
    {
        if($this->getSessionSavedRememberToken()) {
            return false;
        }
        if(Cache::has('admin_remember_token') && $request->cookie('admin_remember') === Cache::get('admin_remember_token') ) {
            return false;
        }
        return true;
    }

    public function logout()
    {
        session()->flush();
        Cache::forget('admin_remember_token');
    }

    public function submit($passItemIds, $failItemIds, $frozenItemIds, $relieveFrozenItemIds)
    {
        DB::transaction(function () use ($passItemIds, $failItemIds, $frozenItemIds, $relieveFrozenItemIds) {
            foreach( $passItemIds as $id ) {
                $invitation = new Invitation($id);
                if($invitation->passExamine()) {
                    $this->sendMessageToUser($invitation->getUser(), '通过审核了');
                }
            }
            foreach( $failItemIds as $id ) {
                $invitation = new Invitation($id);
                if($invitation->failExamine()) {
                    $this->sendMessageToUser($invitation->getUser(), '没有通过审核，请检查文字或图片是否不妥');
                }
            }
            foreach( $frozenItemIds as $id ) {
                $invitation = new Invitation($id);
                if($invitation->frozen()) {
                    $this->sendMessageToUser($invitation->getUser(), '被冻结了');
                }
            }
            foreach( $relieveFrozenItemIds as $id ) {
                $invitation = new Invitation($id);
                if($invitation->relieveFromFrozen()) {
                    $this->sendMessageToUser($invitation->getUser(), '解除了冻结状态');
                }
            }
        });
    }

    public function getReceiveMessages()
    {
        return Message::getReceivedMessages4Admin();
    }

    public function sendMessageToUser(qqUser $user, $message)
    {
        return Message::send(0, $user->id, $message);
    }

    private function generateRememberToken()
    {
        $this->rememberToken = str_random(40);
        return $this->rememberToken;
    }

    private function updateSessionForRememberToken($token)
    {
        session()->put('admin_remember_token', $token);
    }

    private function getSessionSavedRememberToken()
    {
        return session()->get('admin_remember_token', null);
    }

    private function getID()
    {
        return env('ADMIN_NAME', 'admin');
    }

    private function getPassword()
    {
        return env('ADMIN_PASSWORD', '123qwe');
    }
}
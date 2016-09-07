<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/5/16
 * Time: 2:32 PM
 */

namespace App\Classes\Logic;
use App\Classes\Logic\LogicInterface\UserInterface;
use Auth;
use DB;

class qqUser implements UserInterface
{
    private $dbInstance;
    function __construct()
    {
        $args = func_get_args();
        if(count($args) == 0) {
            $this->dbInstance = Auth::user();
        } else {
            $this->dbInstance = \App\qqUser::findOrFail($args[0]);
        }
    }

    public function createIfNotExist($public_id, $img_url, $name, $email, $nickname)
    {
        DB::transaction(function () use ($public_id, $img_url, $name, $email, $nickname) {
            $this->dbInstance = \App\qqUser::where('public_id', $public_id)->first();
            if(!$this->dbInstance) {
                $this->dbInstance = \App\qqUser::create([
                    'public_id' => $public_id,
                    'img_url' => $img_url,
                    'name' => $name,
                    'email' => $email,
                    'nickname' => $nickname
                ]);
            }
        });
    }

    public function rememberMe()
    {
        if($this->dbInstance) {
            return Auth::attempt(['public_id'=>$this->public_id,'password'=>''], true);
        }
        return false;
    }

    public function logout()
    {
        Auth::logout();
    }

    public function getInvitation()
    {
        return new Invitation($this);
    }

    public function sendMessageToAdmin($message)
    {
        return $this->sendMessage(0, $message);
    }

    public function sendMessage($to, $message)
    {
        $dbMessage = $this->dbInstance->sendMessages()->create(['to'=>$to,'message'=>$message]);
        return new Message($dbMessage->id);
    }

    public function getReceiveMessages($pageIndex = null, $pageSize = null)
    {
        $dbMessages = $this->dbInstance->receivedMessages()->orderBy('is_read', 'asc')->orderBy('created_at','desc')->get();
        if($pageIndex && $pageSize) {
            $dbMessages = $dbMessages->forPage($pageIndex, $pageSize);
        }
        return array_map(function($v) {
            return new Message($v->id);
        }, $dbMessages->all());
    }

    public function __get($property)
    {
        if($this->dbInstance && isset($this->dbInstance->{$property})) {
            return $this->dbInstance->{$property};
        }
        return null;
    }
}
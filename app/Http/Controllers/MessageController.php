<?php

namespace App\Http\Controllers;

use App\Classes\Logic\Admin;
use App\Classes\Logic\Message;
use App\Classes\Logic\qqUser;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = new qqUser();
    }

    public function sendSuggest(Request $request)
    {
        if(!empty($request->input('suggest'))) {
            $this->user->sendMessageToAdmin($request->input('suggest'));
            $content = '发送成功';
        } else {
            $content = '不能发送空内容';
        }
        return view('jump', ['content'=>$content,'url'=>url('help')]);
    }

    public function showPage()
    {
        return view('showmessage', ['U'=>$this->user,'I'=>$this->user->getInvitation()]);
    }

    public function fetchMessages($pageIndex, $pageSize)
    {
        $array = array_map(function($message) {
            $msg = $message->toJson();
            $msgObj = json_decode($msg,true);
            if($msgObj['from'] == 0) {
                $msgObj['from'] = (new Admin())->getName();
            } else {
                $msgObj['from'] = (new qqUser($msgObj['from']))->nickname;
            }
            return json_encode($msgObj);
        }, $this->user->getReceiveMessages($pageIndex, $pageSize));
        return response()->json($array);
    }

    public function setRead($id)
    {
        (new Message($id))->setRead();
    }
}

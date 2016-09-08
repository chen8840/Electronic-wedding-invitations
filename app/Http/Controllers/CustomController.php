<?php

namespace App\Http\Controllers;

use App\Classes\Logic\Invitation;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Image;
use Auth;

class CustomController extends Controller
{
    public function getInvitationInfo($invitationid)
    {
        $invitation = new Invitation($invitationid);
        if($invitation->canCustomUserSee() || Auth::check()) {
            $ret = $invitation->getInfo();
            $ret['images'] = array_map(function($v) use ($invitationid) {
                return url('custom/getimage/' . $invitationid . '/' . $v);
            }, $ret['images']);
            $ret['music'] = asset('mp3') . '/' . $ret['music'];
            return response()->json($ret);
        }
        abort(404);
    }

    public function getImage($invitationid, $filename)
    {
        $invitation = new Invitation($invitationid);
        if(empty($filename) || !file_exists($invitation->getImagesAbsoluteDir() . $filename)) {
            abort(404);
        } else if($invitation->canCustomUserSee() || Auth::check()) {
            return Image::make($invitation->getImagesAbsoluteDir() . $filename)->response('jpg');
        } else {
            abort(404);
        }
    }

    public function addComment($invitationId, Request $request)
    {
        $invitation = new Invitation($invitationId);
        if(empty($request->input('name')) || empty($request->input('comment'))) {
            throw new \Exception('argument is missed.');
        }
        $invitation->addComment($request->input('name'), $request->input('comment'));
    }
}

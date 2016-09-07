<?php

namespace App\Http\Controllers;

use App\Classes\Logic\Admin;
use App\Classes\Logic\Invitation;
use App\Classes\Logic\Message;
use App\Classes\Logic\qqUser;
use Hamcrest\Core\IsNot;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Cache;
use Intervention\Image\ImageManagerStatic as Image;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $admin = new Admin();
        $response = redirect('admin/show');
        if($request->input('remember')) {
            $verifyOk = $admin->remember($request->input('id'), $request->input('password'), $response);
        } else {
            $verifyOk = $admin->verify($request->input('id'), $request->input('password'));
        }
        if($verifyOk) {
            return $response;
        } else {
            return view('admin/adminlogin');
        }
    }

    public function logout()
    {
        (new Admin())->logout();
        return redirect('admin');
    }

    public function showSpecialStateInvitations(Request $request)
    {
        if($request->input('pernum')) {
            $pernum = $request->input('pernum');
        } else if($request->hasCookie('pernum')) {
            $pernum = $request->cookie('pernum');
        } else {
            $pernum = 20;
        }
        if(is_string($request->input('state'))) {
            $state = $request->input('state');
        } else if($request->hasCookie('state')) {
            $state = $request->cookie('state');
        } else {
            $state = '';
        }
        $page = intval($request->input('page', 1));
        $totalPage = ceil(Invitation::getSpecialStateInvitationsNum($state) / $pernum);
        if($page <= 0) {
            $page = 1;
        } else if($page > $totalPage) {
            $page = $totalPage;
        }
        $paginationShowPages = [$page];
        for($i = 1, $maxPage = 4; $i <= 4 && $maxPage > 0; $i++) {
            if($page - $i > 0) {
                array_push($paginationShowPages, $page-$i);
                $maxPage--;
            }
            if($page + $i <= $totalPage && $maxPage > 0) {
                array_push($paginationShowPages, $page+$i);
                $maxPage--;
            }
        }

        sort($paginationShowPages);
        return response()->view('admin/main', [
            'Is' => Invitation::getSpecialStateInvitationsByPage($page, $pernum, $state),
            'totalPage' => $totalPage,
            'page' => $page,
            'pageinationSowPages' => $paginationShowPages,
            'pernum' => $pernum,
            'state' => $state,
        ])->withCookie(cookie()->forever('pernum',$pernum))
          ->withCookie('state', $state);
    }

    public function getImagesUrlById($id)
    {
        if($id) {
            $invitation = new Invitation($id);
            return response()->json($invitation->images);
        } else {
            return response()->json([]);
        }
    }

    public function getImage($invitationid, $filename)
    {
        $invitation = new Invitation($invitationid);
        if(empty($filename) || !file_exists($invitation->getImagesAbsoluteDir() . $filename)) {
            abort(404);
        }
        return Image::make($invitation->getImagesAbsoluteDir() . $filename)->response('jpg');
    }

    public function changeStates(Request $request)
    {
        $passItemIds = $request->input('passPublish') ? json_decode($request->input('passPublish')) : [];
        $failItemIds = $request->input('failPublish') ? json_decode($request->input('failPublish')) : [];
        $frozenItemIds = $request->input('frozen') ? json_decode($request->input('frozen')) : [];
        $relieveFrozenItemIds = $request->input('relieveFrozen') ? json_decode($request->input('relieveFrozen')) : [];
        (new Admin())->submit($passItemIds, $failItemIds, $frozenItemIds, $relieveFrozenItemIds);
        return redirect('admin/show');
    }

    public function showMessage()
    {
        return view('admin/showmessage');
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
        }, Message::getReceivedMessages4Admin($pageIndex, $pageSize));
        return response()->json($array);
    }
}

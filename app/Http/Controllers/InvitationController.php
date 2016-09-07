<?php

namespace App\Http\Controllers;

use App\Classes\Logic\Invitation;
use App\Classes\Logic\qqUser;
use App\Classes\UploadHandler;
use App\Events\SyncImagesEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Image;
use Event;

class InvitationController extends Controller
{
    private $invitation;
    private $uploadOptions;
    private $dirName;
    public function __construct()
    {
        $user = new qqUser();
        $this->invitation = $user->getInvitation();
        $this->dirName = $this->invitation->getImagesAbsoluteDir();
        $this->uploadOptions = [
            'param_name' => 'images',
            'upload_dir' => $this->dirName,
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'upload_url' => 'getimage/',
            'max_file_size' => 2000000, //最大2M
            'image_versions' => [
                '' => [
                    'max_width' => 800,
                    'max_height' => 800
                ]
            ]
        ];
    }

    public function index()
    {
        return view('home', ['I'=>$this->invitation]);
    }

    public function help()
    {
        return view('help', ['I'=>$this->invitation]);
    }

    public function review(Request $request)
    {
        $phoneModal = !empty($_COOKIE['phoneModal']) ? $_COOKIE['phoneModal'] : 'iphone6';
        if($this->invitation->isFullFill()) {
            return view('review', ['I'=>$this->invitation, 'phoneModal'=>$phoneModal]);
        } else {
            abort(404);
        }
    }

    public function save(Request $request)
    {
        $this->invitation->save(
            $request->input('groomname'),
            $request->input('bridename'),
            $request->input('weddingdate'),
            $request->input('phone'),
            $request->input('images'),
            $request->input('hoteladdress'),
            $request->input('hotelname'),
            $request->input('hotelroom'),
            $request->input('hotelphone'),
            $request->input('music'),
            $request->input('templatename'));
        return redirect('/');
    }

    public function publish()
    {
        return response()->json(['success' => $this->invitation->publish()]);
    }

    public function cancelPublish()
    {
        return response()->json(['success' => $this->invitation->cancelPublish()]);
    }

    public function reModify()
    {
        return response()->json(['success' => $this->invitation->reModify()]);
    }

    public function getImage($filename)
    {
        if(empty($filename) || !file_exists($this->dirName . $filename)) {
            abort(404);
        }
        return Image::make($this->dirName . $filename)->response('jpg');
    }

    public function deleteImage($filename)
    {
        $ret = $this->invitation->delImage($filename);
        return response()->json(['success' => $ret]);
    }

    public function addImages(Request $request)
    {
        if(!$this->invitation->canSave()) {
            return response()->json([
                'images' => [
                    [
                        'error' => '不允许保存',
                        'name' => ''
                    ]
                ]
            ]);
        }
        $uploadImageNum = count($request->file('images'));
        if($this->invitation->reachLimitImageNum($uploadImageNum)) {
            $ret = [
                'images' => [
                    [
                        'error' => '最多只能上传'.Invitation::MAX_IMAGE_NUM.'张图片',
                        'name' => ''
                    ]
                ],
            ];
            return response()->json($ret);
        } else {
            new UploadHandler($this->uploadOptions);
            Event::fire(new SyncImagesEvent(new qqUser()));
        }
    }
}

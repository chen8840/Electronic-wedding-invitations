<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/5/16
 * Time: 2:46 PM
 */

namespace App\Classes\Logic;

use App\Classes\StateFactory;
use Carbon\Carbon;
use Mockery\CountValidator\Exception;
use Storage;
use Log;

class Invitation
{
    //region 变量
    private $dbInstance;
    private $stateClass;
    private $user;
    const MAX_IMAGE_NUM = 24;
    //endregion

    //region 公有函数
    function __construct($param)
    {
        if(is_subclass_of($param, '\App\Classes\Logic\LogicInterface\UserInterface')) {
            $this->init($param);
        } else {
            $this->dbInstance = \App\Invitation::find($param);
            if(!$this->dbInstance) {
                throw new Exception('$param must be a integer');
            }
            $this->getState();
            $this->user = new qqUser( $this->dbInstance->user_id );
        }
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getState()
    {
        if(!$this->stateClass) {
            $stateFactory = new StateFactory();
            $this->stateClass = $stateFactory->create('App\Classes\State\\'.$this->state);
        }
        return $this->stateClass;
    }

    public function setState($state)
    {
        $this->stateClass = $state;
        $this->dbInstance->state = $state->getName();
    }

    public function syncImages()
    {
        //将目录里的文件与数据库里面的文件同步
        //1.以目录文件为准，多的添加到数据库最后，少的从数据库删除
        //2.数据库里的原来顺序不变
        $imgsFromDb = $this->images;
        $imgsFromDisk = array_filter(
            array_map(
                function($value) {
                    return basename($value);
                },
                Storage::allFiles($this->getImagesRelativeDir())
            ),
            function($value) {
                return preg_match('/\.(gif|jpe?g|png)$/i', $value);
            }
        );

        $imgsAdded = array_diff($imgsFromDisk, $imgsFromDb);
        $imgsRetain = array_intersect($imgsFromDb, $imgsFromDisk);
        $imgsSaved = array_merge($imgsRetain, $imgsAdded);
        $this->dbInstance->images = $imgsSaved;
        $this->dbInstance->save();
    }

    public function delImage($image)
    {
        $file = $this->getImagesRelativeDir().$image;
        if(Storage::exists($file) && $this->canSave()) {
            Storage::delete($this->getImagesRelativeDir().$image);
            $this->syncImages();
            return true;
        } else {
            return false;
        }
    }

    public function save($groom_name, $bride_name, $wedding_date, $phone, $images, $hotel_address, $hotel_name, $hotel_room, $hotel_phone, $music, $template_name)
    {
        if(!$this->canSave()) {
            return false;
        }
        //保存进入Init状态
        $this->changeState('Init', $this);
        if($groom_name !== null) {
            $this->dbInstance->groom_name = $groom_name;
        }
        if($bride_name !== null) {
            $this->dbInstance->bride_name = $bride_name;
        }
        if($wedding_date !== null) {
            $this->dbInstance->wedding_date = Carbon::createFromFormat('Y年m月d日 H点i分', $wedding_date);
        }
        if($phone !== null) {
            $this->dbInstance->phone = $phone;
        }
        if($images !== null) {
            $this->dbInstance->images = json_decode($images);
        }
        if($hotel_address !== null) {
            $this->dbInstance->hotel_address = $hotel_address;
        }
        if($hotel_name !== null) {
            $this->dbInstance->hotel_name = $hotel_name;
        }
        if($hotel_room !== null) {
            $this->dbInstance->hotel_room = $hotel_room;
        }
        if($hotel_phone !== null) {
            $this->dbInstance->hotel_phone = $hotel_phone;
        }
        if($music !== null) {
            $this->dbInstance->music = $music;
        }
        if($template_name !== null) {
            $this->dbInstance->template_name = $template_name;
        }
        return $this->dbInstance->save();
    }

    public function publish()
    {
        if(!$this->canPublish() || !$this->isFullFill()) {
            return false;
        }
        $this->changeState('WaitPublish', $this);
        if($this->stateClass->getName() != 'WaitPublish') {
            return false;
        }
        return $this->dbInstance->save();
    }

    public function cancelPublish()
    {
        if(!$this->canCancelPublish()) {
            return false;
        }
        $this->changeState('Init', $this);
        if($this->stateClass->getName() != 'Init') {
            return false;
        }
        return $this->dbInstance->save();
    }

    public function reModify()
    {
        if(!$this->canReModify()) {
            return false;
        }
        $this->changeState('Init', $this);
        if($this->stateClass->getName() != 'Init') {
            return false;
        }
        return $this->dbInstance->save();
    }

    public function passExamine()
    {
        if(!$this->canPassPublish()) {
            return false;
        }
        $this->changeState('Published', $this);
        if($this->stateClass->getName() != 'Published') {
            return false;
        }
        return $this->dbInstance->save();
    }

    public function failExamine()
    {
        if(!$this->canFailPublish()) {
            return false;
        }
        $this->changeState('Init', $this);
        if($this->stateClass->getName() != 'Init') {
            return false;
        }
        return $this->dbInstance->save();
    }

    public function frozen()
    {
        if(!$this->canFrozen()) {
            return false;
        }
        $this->changeState('Frozen', $this);
        if($this->stateClass->getName() != 'Frozen') {
            return false;
        }
        return $this->dbInstance->save();
    }

    public function relieveFromFrozen()
    {
        if(!$this->canRelieveFrozen()) {
            return false;
        }
        $this->changeState('Init', $this);
        if($this->stateClass->getName() != 'Init') {
            return false;
        }
        return $this->dbInstance->save();
    }

    public function isFullFill()
    {
        return !empty($this->dbInstance->groom_name) &&
                !empty($this->dbInstance->bride_name) &&
                !empty($this->dbInstance->wedding_date) &&
                !empty($this->dbInstance->phone) &&
                !empty($this->dbInstance->images) &&
                !empty($this->dbInstance->hotel_address) &&
                !empty($this->dbInstance->hotel_name) &&
                !empty($this->dbInstance->hotel_room) &&
                !empty($this->dbInstance->hotel_phone) &&
                !empty($this->dbInstance->state) &&
                !empty($this->dbInstance->music) &&
                !empty($this->dbInstance->template_name);
    }

    public function getCurrentImageNum()
    {
        return count($this->images);
    }

    public function reachLimitImageNum($num)
    {
        return $this->getCurrentImageNum() + $num > self::MAX_IMAGE_NUM;
    }

    public function getImagesAbsoluteDir()
    {
        return storage_path('app/'.base64_encode($this->user->public_id).'/images/');
    }

    public function getImagesRelativeDir()
    {
        return base64_encode($this->user->public_id).'/images/';
    }

    public static function getSpecialStateInvitationsByPage($page, $perPage, $state)
    {
        if(empty($state)) {
            $dbInvitations = \App\Invitation::all()->forPage($page, $perPage);
        } else {
            $dbInvitations = \App\Invitation::where('state', $state)->skip(($page-1) * $perPage)->take($perPage)->get();
        }
        $invitations = [];
        $dbInvitations->map(function($item) use (&$invitations) {
            array_push($invitations, new Invitation($item->id));
        });
        return $invitations;
    }

    public function canCustomUserSee()
    {
        return $this->stateClass->getName() == 'Published';
    }

    /**
     * 输出格式：
     * {
     *   "music" : "xxx",
     *   "groom_name" : "xxx",
     *   "bride_name" : "xxx",
     *   "wedding_date" : "xxx",
     *   "wedding_time" : "xxx",
     *   "phone" : "xxx",
     *   "images" : [],
     *   "hotel_name" : "xxx",
     *   "hotel_room" : "xxx",
     *   "hotel_address" : "xxx",
     *   "hotel_phone" : "xxx",
     * }
     */
    public function getInfo()
    {
        $output = [];
        $output['music'] = '';
        $output['groom_name'] = $this->groom_name;
        $output['bride_name'] = $this->bride_name;
        $output['wedding_date'] = $this->wedding_date->format('Y年n月j日');
        $output['wedding_time'] = $this->wedding_date->format('G点i分');
        $output['phone'] = $this->phone;
        $output['images'] = $this->images;
        $output['hotel_name'] = $this->hotel_name;
        $output['hotel_room'] = $this->hotel_room;
        $output['hotel_address'] = $this->hotel_address;
        $output['hotel_phone'] = $this->hotel_phone;
        $output['music'] = $this->music;
        return $output;
    }

    public function getReviewUrl()
    {
        if(!$this->isFullFill()) {
            return null;
        }
        return asset('invitations_templates/' . $this->template_name . '/?id=' . $this->id);
    }

    public static function getSpecialStateInvitationsNum($state)
    {
        if(empty($state)) {
            return \App\Invitation::all()->count();
        } else {
            return \App\Invitation::where('state', $state)->count();
        }
    }

    public function __get($property)
    {
        if($this->dbInstance && isset($this->dbInstance->{$property})) {
            return $this->dbInstance->{$property};
        }
        return null;
    }

    public function __call($name, $arguments)
    {
        if(method_exists('App\Classes\State\State', $name)) {
            $state = $this->getState();
            return call_user_func_array([$state, $name], $arguments);
        }
        return null;
    }
    //endregion

    //region 私有函数
    private function init($qqUser)
    {
        if($this->dbInstance) {
            return;
        }
        $userId = $qqUser->id;
        $invitation = \App\Invitation::where('user_id', $userId)->first();
        if(!$invitation) {
            $invitation =  \App\Invitation::create(['user_id'=>$userId, 'state'=>'NotInit', 'wedding_date'=>Carbon::now(), 'images'=>[]]);
        }
        $this->user = $qqUser;
        $this->dbInstance = $invitation;
        $this->getState();
    }
    //endregion
}
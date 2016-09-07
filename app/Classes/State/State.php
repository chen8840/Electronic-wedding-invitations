<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/1/16
 * Time: 5:07 PM
 */

namespace App\Classes\State;


use App\Classes\Logic\Invitation;

interface State
{
    //9种操作：1.保存，2.发布，3.取消发布，4.冻结，5.解除冻结，6.通过审核,7.没有通过审核，8.查看，9.重新修改
    public function canSave();
    public function canPublish();
    public function canCancelPublish();
    public function canFailPublish();
    public function canPassPublish();
    public function canReview();
    public function canFrozen();
    public function canRelieveFrozen();
    public function canReModify();

    public function changeState($state, Invitation $i);
    public function getName();
}
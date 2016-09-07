<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/2/16
 * Time: 9:55 AM
 */

namespace App\Classes\State;


use App\Classes\Logic\Invitation;

trait StateImplement
{
    public function canSave()
    {
        return false;
    }
    public function canPublish()
    {
        return false;
    }
    public function canCancelPublish()
    {
        return false;
    }
    public function canFailPublish()
    {
        return false;
    }
    public function canPassPublish()
    {
        return false;
    }
    public function canReview()
    {
        return false;
    }
    public function canFrozen()
    {
        return false;
    }
    public function canRelieveFrozen()
    {
        return false;
    }
    public function canReModify()
    {
        return false;
    }
}
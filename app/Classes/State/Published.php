<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/1/16
 * Time: 5:14 PM
 */

namespace App\Classes\State;


use App\Classes\Logic\Invitation;
use App\Classes\StateFactory;

class Published implements State
{
    use StateImplement;

    public function canReModify()
    {
        return true;
    }

    public function canFrozen()
    {
        return true;
    }

    public function canReview()
    {
        return true;
    }

    public function changeState($state, Invitation $i)
    {
        $factory = new StateFactory();
        switch(strtolower($state)) {
            case 'frozen':
                $stateClass = $factory->create('App\Classes\State\Frozen');
                break;
            case 'init':
                $stateClass = $factory->create('App\Classes\State\Init');
                break;
            default:
                $stateClass = $this;
                break;
        }
        $i->setState($stateClass);
    }

    public function getName()
    {
        return 'Published';
    }
}
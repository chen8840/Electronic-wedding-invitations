<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/1/16
 * Time: 5:10 PM
 */

namespace App\Classes\State;


use App\Classes\Logic\Invitation;
use App\Classes\StateFactory;

class NotInit implements State
{
    use StateImplement;

    public function canSave()
    {
        return true;
    }

    public function changeState($state, Invitation $i)
    {
        $factory = new StateFactory();
        switch(strtolower($state)) {
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
        return 'NotInit';
    }
}
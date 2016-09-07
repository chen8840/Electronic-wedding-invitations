<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/1/16
 * Time: 5:25 PM
 */

namespace App\Classes;


class StateFactory
{
    public function create($stateName)
    {
        return new $stateName;
    }
}
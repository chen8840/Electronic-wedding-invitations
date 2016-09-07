<?php

namespace App\Classes\Logic;

class Message
{
    private $dbInstance;
    private $sender;
    private $receiver;

    function __construct($id)
    {
        $this->dbInstance = \App\Message::findOrFail($id);
    }

    public function delete()
    {
        $this->dbInstance->delete();
    }

    public function setRead()
    {
        $this->dbInstance->is_read = true;
        $this->dbInstance->save();
    }

    public function toJson()
    {
        return $this->dbInstance->toJson();
    }

    public static function send($from, $to, $message)
    {
        $dbMessage = \App\Message::create(['from'=>$from,'to'=>$to,'message'=>$message]);
        return new Message($dbMessage->id);
    }

    public static function getReceivedMessages4Admin($pageIndex = null, $pageSize = null)
    {
        $dbMessages = \App\Message::where('to', 0)->orderBy('is_read', 'asc')->orderBy('created_at', 'desc')->get();
        if($pageIndex && $pageSize) {
            $dbMessages = $dbMessages->forPage($pageIndex, $pageSize);
        }
        return array_map(function($v) {
            return new Message($v->id);
        }, $dbMessages->all());
    }

    public static function getNoReadMessages4Admin()
    {
        $dbMessages = \App\Message::where('to', 0)->where('is_read', false)->orderBy('created_at', 'desc')->get();
        return array_map(function($v) {
            return new Message($v->id);
        }, $dbMessages->all());
    }

    public static function getReceivedMessages4User($userid)
    {
        $dbMessages = \App\Message::where('to', $userid)->orderBy('is_read', 'asc')->orderBy('created_at', 'desc')->get();
        return array_map(function($v) {
            return new Message($v->id);
        }, $dbMessages->all());
    }

    public static function getReadMessages4User($userid)
    {
        $dbMessages = \App\Message::where('to', $userid)->where('is_read', true)->orderBy('created_at', 'desc')->get();
        return array_map(function($v) {
            return new Message($v->id);
        }, $dbMessages->all());
    }

    public static function getNoReadMessages4User($userid)
    {
        $dbMessages = \App\Message::where('to', $userid)->where('is_read', false)->orderBy('created_at', 'desc')->get();
        return array_map(function($v) {
            return new Message($v->id);
        }, $dbMessages->all());
    }

    public function __get($property)
    {
        if($this->dbInstance && isset($this->dbInstance->{$property})) {
            return $this->dbInstance->{$property};
        }
        return null;
    }
}
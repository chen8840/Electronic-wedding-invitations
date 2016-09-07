<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css">

    @yield('links')
    <style>
    html,body {
        height: 100%;
    }
    #root-container {
        min-height: 100%;
        position: relative;
    }
    #content{
        padding-bottom: 40px;
    }
    #footer {
        line-height: 40px;
        position: absolute;
        bottom: 0;
        width: 100%;
        text-align: center;
    }
    #tips {
        position: absolute;
        width: 12px;
        height: 12px;
        text-align: center;
        background-color: white;
        line-height: 12px;
        color: #F70000;
        border-radius: 50%;
        right: 20px;
        top: 4px;
    }
    </style>
</head>
<body>
<div id="root-container">

    <div id="header">
        <div class="container">
            <div class="row">
                <nav class="navbar navbar-default" role="navigation">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse"
                                data-target="#example-navbar-collapse">
                            <span class="sr-only">切换导航</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="{!! url('/') !!}">e喜帖</a>
                    </div>
                    <div class="collapse navbar-collapse" id="example-navbar-collapse">
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="padding: 0 10px">
                                    <img src="{!! $I->getUser()->img_url !!}" style="height: 40px;margin: 5px 0;border-radius: 50%;"/> @if( !empty(\App\Classes\Logic\Message::getNoReadMessages4User($I->getUser()->id)) )<i id="tips" 	class="glyphicon glyphicon-exclamation-sign"></i>@endif <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="{!! url('showmessages') !!}">消息@if( !empty($I->getUser()->getReceiveMessages()) )<span class="badge badge-primary pull-right" style="background-color:blue;" id="header_no_read_count">{{ count(\App\Classes\Logic\Message::getNoReadMessages4User($I->getUser()->id)) }}</span>@endif</a></li>
                                    <li><a href="{{url('qqlogout')}}">退出</a></li>
                                    <li><a href="{{url('help')}}">查看帮助</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <div id="content">
        <div class="container">
            @yield('content')
        </div>
    </div>
    <div id="footer">欢 迎 访 问</div>
</div>
<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
@yield('scripts')
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    @yield('links')
</head>
<body>
<nav class="navbar navbar-default" role="navigation">
    <div>
        <div class="navbar-header">
            <a class="navbar-brand" href="{!! url('admin/show') !!}">你好，管理员！</a>
        </div>
        <p class="navbar-text navbar-right" style="margin-right:15px;">
            <a href="{{ url('admin/logout') }}" class="navbar-link">退出</a>
        </p>
        <p class="navbar-text navbar-right">
            <a href="{!! url('admin/showmessage') !!}" class="navbar-link">消息<span class="badge" style="background-color:#1be" id="header_no_read_count">{!! count(\App\Classes\Logic\Message::getNoReadMessages4Admin()) !!}</span></a>
        </p>
    </div>
</nav>

@yield('content')

<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
@yield('scripts')
</body>
</html>

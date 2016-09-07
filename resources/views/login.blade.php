<!DOCTYPE html>
<html>
<head>
    <title>登录</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css">

    <style>
        html,body {
            height: 100%;
            background-color: #2b2e31;
        }
        #login {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate3D(-50%,-50%, 0);
        }
        .panel-body {
            text-align: center;
            padding: 30px;
        }
    </style>
</head>
<body>
<div id="login">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">目前只支持QQ登录</h3>
        </div>
        <div class="panel-body">
            <a href="{{url('qqlogin')}}"><img src="{{asset('images/Connect_logo_5.png')}}"></a>
        </div>
    </div>
</div>
</body>
</html>

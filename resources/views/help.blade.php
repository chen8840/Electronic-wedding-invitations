@extends('layouts/layout')

@section('title', '帮助')

@section('scripts')
    <script>
        $('#savebtn').on('click', function(evt) {
            $(this).attr('disabled', 'disabled');
            $('#form').submit();
        });
    </script>
@endsection

@section('content')
    <div style="font-size:120%;">
        <ol>
            <li>欢迎您使用这个制作婚礼电子请帖的小工具</li>
            <li>您提交的请帖需要经过管理员的审核才能在微信上访问，发布之后请您耐心等待</li>
            <li>发布成功以后查看页面会生成二维码，请用微信扫描，点击手机页面的右上角分享给亲朋好友，<a href="{!! asset('images/weChat_share.jpg') !!}">查看示例</a></li>
            <li>遇到问题或者有什么建议，请写下来发送给管理员</li>
        </ol>
    </div>

    <form id="form" role="form" method="post" action="sendsuggest" style="margin-top:50px;">
        {!! csrf_field() !!}
        <label class="control-label text-nowrap">问题或建议</label>
        <div class="form-group">
            <textarea class="form-control" name="suggest" rows="10"></textarea>
        </div>
        <div class="form-group text-right">
            <button type="submit" class="btn btn-primary" id="savebtn">提交</button>
        </div>
    </form>
@endsection
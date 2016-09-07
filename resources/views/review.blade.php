@extends('layouts/layout')

@section('title', '查看自己的请帖')

@section('links')
@endsection

@section('scripts')
    <script src="{!! asset('js/CookieUtil.js') !!}"></script>
    <script src="{!! asset('lib/qrcode.min.js') !!}"></script>
    <script>
        $(function() {
            $('#phoneModal').on('change', function() {
                CookieUtil.set('phoneModal', $(this).val(), new Date('2099-1-1'));
                location.href = '{!! url('review') !!}';
            });

            switchPhoneModal();

            function switchPhoneModal() {
                var modalName = $('#phoneModal').val();
                switch(modalName) {
                    case 'iphone6':
                        changeIframeSize('375px', '667px');
                        break;
                    case 'iphone5':
                        changeIframeSize('320px', '568px');
                        break;
                    default:
                        break;
                }
            }
            function changeIframeSize(width,height) {
                $('iframe').css('width', width)
                        .css('height', height);
                $('iframe').attr('src', '{!! $I->getReviewUrl() !!}');
            }

            initQRCode();
            function initQRCode() {
                var div = document.getElementById("qrcode");
                if(div) {
                    new QRCode(document.getElementById("qrcode"), {
                        width: 200,
                        height: 200,
                        text: '{!! $I->getReviewUrl() !!}'
                    });
                }
            }
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12" style="padding:0">
            <select class="form-control" id="phoneModal">
                <option value="iphone6" @if($phoneModal == 'iphone6')selected="selected"@endif>苹果6</option>
                <option value="iphone5" @if($phoneModal == 'iphone5')selected="selected"@endif>苹果5</option>
            </select>
        </div>
    </div>
    <div class="row" style="margin-top:30px;">
        <div class="col-xs-12 text-nowrap" style="padding:0;text-align: center;">
            <iframe style="border:1px solid #ddd;vertical-align: middle;margin: 0 100px;">
            </iframe>
            @if($I->canCustomUserSee())
                <div style="vertical-align:middle;display:inline-block;margin: 0 100px;">
                    <div id="qrcode" style="width:200px;height:200px;display:inline-block;"></div><br>
                    <span>使用微信扫一扫</span>
                </div>
            @endif
        </div>
    </div>
@endsection
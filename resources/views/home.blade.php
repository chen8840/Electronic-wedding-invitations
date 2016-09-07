@extends('layouts/layout')

@section('title', 'e请帖')

@section('links')
    <link rel="stylesheet" href="{{ asset('lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/jquery-confirm/jquery-confirm.css') }}">
    <link rel="stylesheet" href="http://apps.bdimg.com/libs/jqueryui/1.10.4/css/jquery-ui.min.css">
    <style>
        #img_list {
            list-style: none;
            padding: 0;
        }
        #add_img {
            font-size: 50px;
            position: relative;
            overflow: hidden;
            background-image: none;
        }
        #fileupload {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            right: 0;
            opacity: 0;
            cursor: pointer;
            font-size:0;
        }
        .img_item {
            float: left;
            height: 178px;
            width: 100px;
            color: green;
            font-size: 0;
            text-align: center;
            line-height: 178px;
            padding: 0px;
            cursor: pointer;
            border: 1px solid #eee;
            border-radius: 4px;
            margin: 3px;
            position: relative;
            background-position: center;
            background-size: 50%;
            background-repeat:no-repeat;
            background-image: url('{{asset('images/wait.svg')}}');
        }
        .img_item:hover {
            border-color: green;
        }
        .img_item:hover div {
            display: block !important;
        }
        #img_container {
            border-right-width: 15px;
            border-left-width: 15px;
            border-top-width: 0;
            border-bottom-width: 0;
            border-style: solid;
            border-color: rgba(11,11,11,0.0);
            padding: 0;
        }

        .tooltip-wrapper {
            display: inline-block; /* display: block works as well */
        }

        .tooltip-wrapper .btn[disabled] {
            /* don't let button block mouse events from reaching wrapper */
            pointer-events: none;
        }

        .tooltip-wrapper.disabled {
            /* OPTIONAL pointer-events setting above blocks cursor setting, so set it here */
            cursor: not-allowed;
        }
        .clearfix:after {
            content:'';
            display: table;
            clear: both;
        }
        .clearfix {
            *zoom: 1;
        }
    </style>
@endsection
@section('scripts')
    <script src="http://apps.bdimg.com/libs/moment/2.8.3/moment-with-locales.js"></script>
    <script src="{{ asset('lib/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="http://api.map.baidu.com/api?v=1.4"></script>
    <script src="{{ asset('lib/Sortable.min.js') }}"></script>
    <script src="http://apps.bdimg.com/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
    <script src="{{ asset('lib/FileUpload/jquery.fileupload.js') }}"></script>
    <script src="{{ asset('lib/jquery-confirm/jquery-confirm.js') }}"></script>
    <script src="{{ asset('lib/jquery-json/jquery.json.min.js') }}"></script>
    <script src="{{ asset('lib/FileUpload/jquery.fileupload-process.js') }}"></script>
    <script src="{{ asset('lib/FileUpload/jquery.fileupload-validate.js') }}"></script>
    <script>
        $(function() {
            var Events = {
                events: {},
                on: function(eventName, callback) {
                    if(!this.events[eventName]) {
                        this.events[eventName] = [];
                    }
                    this.events[eventName].push(callback);
                },
                fire: function(eventName) {
                    if(this.events[eventName]) {
                        $.each(this.events[eventName], function(key,callback) {
                            callback();
                        });
                    }
                }
            };
            (function() {
                //日期部分
                $('[name=weddingdate]').datetimepicker({
                    format: 'YYYY年MM月DD日 HH点mm分',
                    locale: "zh-cn",
                    sideBySide: true
                });
            })();

            (function() {
                //地图部分
                var map = new BMap.Map("b-map");
                map.centerAndZoom("北京",12);
                var ac = new BMap.Autocomplete({
                    "input" : "hoteladdress",
                    "location" : map
                });
                var bMapResult;
                ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
                    var _value = e.item.value;
                    bMapResult = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
                    setPlace();
                });
                @if($I->hotel_address)
                    bMapResult = '{{$I->hotel_address}}';
                    setPlace();
                    ac.setInputValue(bMapResult);
                @endif
                function setPlace(){
                    map.clearOverlays();    //清除地图上所有覆盖物
                    function myFun(){
                        var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
                        map.centerAndZoom(pp, 18);
                        map.addOverlay(new BMap.Marker(pp));    //添加标注
                    }
                    var local = new BMap.LocalSearch(map, { //智能搜索
                        onSearchComplete: myFun
                    });
                    local.search(bMapResult);
                }
            })();

            (function() {
                //图片部分
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                //---------------------排序---------------------------
                @if($I->canSave())
                    Sortable.create(document.getElementById('img_list'), {
                        animation: 200,
                        onEnd: function(evt) {
                            if(evt.oldIndex != evt.newIndex) {
                                $('#add_img').appendTo($('#add_img').parent());
                                Events.fire('imageChanged');
                            }
                        }
                    });
                @endif
                function getImages() {
                    var images = [];
                    $('#img_list').find('img').each(function(img) {
                        images.push($(this).data('filename'));
                    });
                    return $.toJSON(images);
                }
                function refreshImagesInputValue() {
                    var imagesStr = getImages();
                    $('input[name=images]').val(imagesStr);
                    $('#currentImagesNum').text($.parseJSON(imagesStr).length);
                }
                Events.on('imageChanged', function() {
                    refreshImagesInputValue();
                });

                //---------------------添加---------------------------

                $('#fileupload').fileupload({
                    url: 'addimages',
                    dataType: 'json',
                    //acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                    //maxFileSize: 2000000, //2M
                    singleFileUploads: false,
                    done: function (e, data) {
                        var error = [];
                        $.each(data.result.images, function (index, file) {
                            if(file.error) {
                                error.push('文件' + file.name + '上传失败。原因：' + file.error);
                            } else {
                                var img = addImg(fromUrl(file.url))();
                                $(img).data('filename', file.name);
                                Events.fire('imageChanged');
                            }
                        });
                        if(error.length > 0) {
                            addError(error.join('\n'));
                        }
                    },
                    start: function(e) {
                        removeError();
                    },
                    fail: function(e,data) {
                        addError('上传失败。原因：网络问题');
                    },
                    /*processfail:function(e,data) {
                        debugger;
                    },
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        console.log(progress);
                    }*/
                });
                var addError = function() {
                    var $error = $( '<div class="alert alert-danger alert-dismissable" style="margin-bottom:10px;">' +
                                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times; </button>'  +
                                    '</div>');
                    return function(text) {
                        var $span = $('<span>' + text.replace(/\n/g, '<br>') + '</span>');
                        $error.clone().append($span).insertAfter('#img_list');
                    }
                }();
                function removeError() {
                    $('#img_list').siblings('.alert-danger').remove();
                }
                var addImg = function(fromWhere) {
                    function createLi() {
                        var $del = $('<div><button type="button" class="btn btn-link">删除</button></div>'),
                                $li = $('<li></li>').addClass('img_item').append($del);
                        $del.css({
                            'opacity': '.9',
                            'position': 'absolute',
                            'bottom': '3px',
                            'width': '100%',
                            'text-align': 'center',
                            'font-size': '14px',
                            'display': 'none',
                            'line-height': '1em'
                        });
                        @if(!$I->canSave())
                            $del.find('button').css('display','none');
                        @endif
                        return $li.insertBefore('#add_img');
                    }
                    function adjust(img, container) {
                        var iWidth = img.width,
                                iHeight = img.height,
                                cWidth = container.clientWidth,
                                cHeight = container.clientHeight;
                        if(iWidth/iHeight > cWidth/cHeight) {
                            img.width = cWidth;
                            img.height = Math.floor(cWidth * iHeight / iWidth);
                        } else {
                            img.height = cHeight;
                            img.width = Math.floor(cHeight * iWidth / iHeight);
                        }
                    }
                    function onLoad(img) {
                        var $li = createLi(),
                            $img = $(img).css('display', 'none');
                        $li.append($img);
                        img.onload = function() {
                            adjust($img[0], $li[0]);
                            $li.css('background-image', 'none');
                            $img.css('display', 'inline-block')
                        }
                    }
                    return function() {
                        var img = $('<img>')[0];
                        fromWhere(img);
                        onLoad(img);
                        return img;
                    };
                };
                {{--
                //从input[type=file]读取图片
                var fromFile = function(input) {
                    return function(img) {
                        if(input.files && input.files[0] && input.files[0].type.match('image')) {
                            var uniqueId = UUIDjs.create();
                            $(img).data('id', uniqueId);
                            $(input).data('id', uniqueId);
                            var reader = new FileReader();
                            reader.onload = function(evt){
                                debugger;
                                img.src = evt.target.result;
                            };
                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                };
                --}}
                //从url读取图片
                var fromUrl = function(url) {
                    return function(img) {
                        img.src = url;
                    }
                };
                @if(is_array($I->images))
                    @foreach($I->images as $image)
                        $(addImg(fromUrl('{{'getimage/'.$image}}'))()).data('filename', '{{$image}}');
                    @endforeach
                @endif

                //------------------------删除--------------------------
                function removeImg($img) {
                    $img.parents('.img_item').remove();
                }
                $('#img_list').on('click', 'button.btn-link', function() {
                    var $img = $(this).parents('.img_item').find('img');
                    var filename = $img.data('filename');
                    if(filename && $img.css('display') != 'none') {
                        $.confirm({
                            icon: 'glyphicon glyphicon-question-sign',
                            title: '提示',
                            content: '确定删除？',
                            confirmButtonClass: 'btn-info',
                            cancelButtonClass: 'btn-default',
                            confirmButton: '是的',
                            cancelButton: '取消',
                            confirm: function() {
                                $.ajax({
                                    type: 'DELETE',
                                    url: 'deleteimage/'+filename,
                                    success: function(data) {
                                        if(data.success) {
                                            removeImg($img);
                                            Events.fire('imageChanged');
                                        } else {
                                            removeError();
                                            addError('删除失败');
                                        }
                                    },
                                    error: function() {
                                        removeError();
                                        addError('删除失败');
                                    }
                                });
                            }
                        });
                    }
                });

            })();

            (function() {
                //提交部分
                $( document ).tooltip();
                //--------------保存----------------
                $('#savebtn').on('click', function(evt) {
                    $(this).attr('disabled', 'disabled');
                    $('#form').submit();
                });
                //-----------发布 & 取消发布---------
                $('#publishbtn').on('click', function(evt) {
                    var me = this;
                    $(me).attr('disabled','disabled');
                    evt.preventDefault();
                    $.post( 'publish', function(data) {
                        if(data.success) {
                            window.location.href = '{{ url('/') }}';
                        } else {
                            $(me).removeAttr('disabled');
                        }
                    });
                });
                $('#cancelPublishbtn').on('click', function(evt) {
                    var me = this;
                    $(me).attr('disabled','disabled');
                    evt.preventDefault();
                    $.post( 'cancelpublish', function(data) {
                        if(data.success) {
                            window.location.href = '{{ url('/') }}';
                        } else {
                            $(me).removeAttr('disabled');
                        }
                    });
                });
                //重新修改
                $('#reModifybtn').on('click', function(evt) {
                    var me = this;
                    evt.preventDefault();
                    $.confirm({
                        icon: 'glyphicon glyphicon-question-sign',
                        title: '提示',
                        content: '需要重新提交审核，确定重新修改请帖吗？',
                        confirmButtonClass: 'btn-info',
                        cancelButtonClass: 'btn-default',
                        confirmButton: '是的',
                        cancelButton: '取消',
                        confirm: function() {
                            $(me).attr('disabled','disabled');
                            $.post( 'remodify', function(data) {
                                if(data.success) {
                                    window.location.href = '{{ url('/') }}';
                                } else {
                                    $(me).removeAttr('disabled');
                                }
                            });
                        }
                    });
                });
                //查看
                $('#viewbtn').on('click', function(evt) {
                    evt.preventDefault();
                    location.href = '{!! url('review') !!}';
                });
            })();
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12" style="padding:0px;">
            <div class="panel panel-default ">
                <div class="panel-heading">
                    <h2 class="panel-title text-center">我的喜帖 <span class="label label-info">
                            @if($I->getState()->getName() == 'NotInit' || $I->getState()->getName() == 'Init')
                                未发布
                            @elseif($I->getState()->getName() == 'WaitPublish')
                                审核中
                            @elseif($I->getState()->getName() == 'Frozen')
                                冻结
                            @elseif($I->getState()->getName() == 'Published')
                                已发布
                            @endif
                        </span></h2>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="save" id="form">
                        {!! csrf_field() !!}
                        <input type="hidden" name="images" value="{{json_encode($I->images)}}">
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">新郎名</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="groomname" value="{{$I->groom_name}}"
                                       placeholder="请输入新郎姓名" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">新娘名</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="bridename" value="{{$I->bride_name}}"
                                       placeholder="请输入新娘姓名" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">婚礼日期</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="weddingdate" value="{{$I->wedding_date}}"
                                       placeholder="请输入婚礼举办时间" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">联系电话</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="phone" value="{{$I->phone}}"
                                       placeholder="请输入联系电话" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">婚纱照<br>(<span id="currentImagesNum">{{ count($I->images) }}</span>/<span>{{ $I::MAX_IMAGE_NUM }}</span>)</label>
                            <div class="col-sm-10" id="img_container">
                                <ul id="img_list" class="clearfix">
                                    <li class="img_item" id="add_img" {!! $I->canSave() ? '' : 'style="display:none;"' !!}>+<input type="file" name="images[]" id="fileupload" multiple></li>
                                </ul>
                                @if($I->canSave())
                                    <div class="alert alert-info" style="margin-bottom:0;">提示：拖动可以排序！</div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">酒店名</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="hotelname" value="{{$I->hotel_name}}"
                                       placeholder="请输入酒店名称" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">房间名</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="hotelroom" value="{{$I->hotel_room}}"
                                       placeholder="请输入房间名字" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">酒店地址</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="hoteladdress" id="hoteladdress"
                                       placeholder="请输入酒店地址" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <div style="height:350px;" id="b-map"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">酒店电话</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="hotelphone" value="{{$I->hotel_phone}}"
                                       placeholder="请输入酒店电话" {!! $I->canSave() ? '' : 'disabled' !!}>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">背景音乐</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="music" {!! $I->canSave() ? '' : 'disabled' !!}>
                                    <option value="i_wannna_be_with_you.mp3" selected="selected">i wanna be with you</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-nowrap">请帖模板</label>
                            <div class="col-sm-10">
                                <select class="form-control" name="templatename" {!! $I->canSave() ? '' : 'disabled' !!}>
                                    <option value="style1" selected="selected">紫色</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                @if($I->canSave())
                                    <button type="submit" class="btn btn-default" id="savebtn">保存</button>
                                @endif
                                @if($I->canPublish())
                                    <div {!! $I->isFullFill() ? 'class="tooltip-wrapper"' : 'class="tooltip-wrapper disabled" title="所有选项填写完毕才能发布"' !!}>
                                        <button class="btn btn-default" id="publishbtn" {{ $I->isFullFill() ? '' : 'disabled' }}>发布</button>
                                    </div>
                                @endif

                                @if($I->canCancelPublish())
                                        <button class="btn btn-default" id="cancelPublishbtn">取消发布</button>
                                @endif
                                @if($I->canReModify())
                                    <button class="btn btn-default" id="reModifybtn">重新修改</button>
                                @endif
                                @if($I->canReview())
                                    <div {!! $I->isFullFill() ? 'class="tooltip-wrapper"' : 'class="tooltip-wrapper disabled" title="所有选项填写完毕才能查看"' !!}>
                                        <button class="btn btn-default" id="viewbtn" {{ $I->isFullFill() ? '' : 'disabled' }}>查看</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{--<div class="col-sm-5 hidden-xs">--}}

            {{--<div class="panel panel-default">--}}
                {{--<div class="panel-body">--}}
                    {{--这是一个基本的面板--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    </div>
@endsection
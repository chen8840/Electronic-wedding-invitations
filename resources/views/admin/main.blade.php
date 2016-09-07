@extends('admin/layouts/layout')

@section('title', '管理')

@section('links')
    <style>
        td {
            vertical-align: middle !important;
        }
        .carousel-inner img {
            display:inline-block !important;
        }
        .carousel-inner .item {
            height: 750px;
            vertical-align: middle;
            line-height: 750px;
            text-align: center;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('lib/jquery-json/jquery.json.min.js') }}"></script>
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            //region 图片展示
            $('#table').on('click', 'a', function(evt) {
                var id = $(this).parents('tr').attr('id');
                if(!id) {
                    return;
                }
                var url = '{{url("getImagesById")}}';
                $.post(url + '/' + id, function(data) {
                    if(data.length == 0) {
                        alert('没有图片');
                        return;
                    }
                    buildMyCarousel(data, id);
                    $('#myModal').modal({
                        keyboard: true
                    });
                });
                evt.preventDefault();
            });
            $('#myCarousel').on('slid.bs.carousel', function () {
                updateProgress();
            });

            function updateProgress() {
                var $myCarousel = $('#myCarousel');
                var percent = ($myCarousel.find('.item.active').index('.item') + 1) * 100 / $myCarousel.find('.item').length;
                $('#image_progress').css('width', percent + '%');
            }
            function buildMyCarousel(images, invitationid) {
                var $carousel_inner = $('.carousel-inner');
                var $div;
                $carousel_inner.children().remove();
                for(var i = 0; i < images.length; ++i) {
                    $div = $('<div class="item"><img src="{{url('getimage')}}' + '/' + invitationid + '/' + images[i] + '"></div>');
                    if(i == 0) {
                        $div.addClass('active');
                    }
                    $carousel_inner.append($div);
                }
                updateProgress();
            }
            //endregion

            //region 跳转
            function gotoSelf(page, pernum, state) {
                window.location.href = '{!! url('admin/show?page=') !!}' + page + '&pernum=' + pernum + '&state=' + state;
            }
            $('#pernum').on('change', function(e) {
                gotoSelf('1', $(this).val(), '{!! $state !!}');
            });
            $('#link_btns').on('click', 'button', function() {
                var text = $(this).text();
                switch(text) {
                    case '等待审核':
                        gotoSelf('1', '{!! $pernum !!}', 'WaitPublish');
                        break;
                    case '已冻结':
                        gotoSelf('1', '{!! $pernum !!}', 'Frozen');
                        break;
                    case '初始化':
                        gotoSelf('1', '{!! $pernum !!}', 'Init');
                        break;
                    case '已发布':
                        gotoSelf('1', '{!! $pernum !!}', 'Published');
                        break;
                    default:
                        gotoSelf('1', '{!! $pernum !!}', '');
                        break;
                }
            });
            //endregion

            //region 提交
            $('table').on('change', 'input[type=radio]', function() {
                var name = $(this).attr('data-type'),
                    id = $(this).parents('tr').attr('id');
                removeItem(id);
                if(name) {
                    updateInput(name, id);
                }
            });
            $('#submit').on('click', function() {

            });
            function updateInput(name, item) {
                var $input = $('form input[name=' + name + ']');
                if($input.length == 0) {
                    $input = buildInput(name);
                }
                var array = $input.val() ? $.parseJSON($input.val()) : [];
                array.push(item);
                $input.val($.toJSON(array));
            }
            function buildInput(name) {
                var $form = $('form'),
                    $input = $('form input[name=' + name + ']');
                if($input.length == 0) {
                    $input = $('<input type="hidden" name="' + name + '">');
                    $form.append($input);
                }
                return $input;
            }
            function removeItem(item) {
                var $inputs = $('form input[type=hidden][name!=_token]');
                $inputs.each(function() {
                    var items = $(this).val() ? $.parseJSON($(this).val()) : [];
                    $.each(items, function(i, n) {
                        if(item == n) {
                            items.splice(i, 1);
                            return false;
                        }
                    });
                    $(this).val($.toJSON(items));
                });
            }
            //endregion
        })
    </script>
@endsection

@section('content')
    <div style="text-align: right;margin-bottom:10px">
        <div class="btn-group btn-group-xs" id="link_btns" style="margin-right:10px;">
            <button type="button" class="btn @if($state=='WaitPublish') btn-primary @else btn-default @endif">等待审核</button>
            <button type="button" class="btn @if($state=='Frozen') btn-primary @else btn-default @endif">已冻结</button>
            <button type="button" class="btn @if($state=='Init') btn-primary @else btn-default @endif">初始化</button>
            <button type="button" class="btn @if($state=='Published') btn-primary @else btn-default @endif">已发布</button>
            <button type="button" class="btn @if($state=='') btn-primary @else btn-default @endif">全部</button>
        </div>
        <select id="pernum" style="vertical-align: middle;">
            <option @if($pernum == 10)selected="selected"@endif>10</option>
            <option @if($pernum == 20)selected="selected"@endif>20</option>
            <option @if($pernum == 50)selected="selected"@endif>50</option>
            <option @if($pernum == 100)selected="selected"@endif>100</option>
        </select>
    </div>
    <table class="table table-striped table-bordered" id="table">
        <thead>
        <tr>
            <th>新郎名</th>
            <th>新娘名</th>
            <th>婚礼日期</th>
            <th>图片</th>
            <th>电话</th>
            <th>酒店地址</th>
            <th>酒店名</th>
            <th>酒店电话</th>
            <th>状态</th>
            <th>选中</th>
        </tr>
        </thead>
        <tbody>
        @foreach($Is as $I)
            <tr id="{{$I->id}}">
                <td>{{$I->groom_name}}</td>
                <td>{{$I->bride_name}}</td>
                <td>{{$I->wedding_date}}</td>
                <td><a href="#">查看</a></td>
                <td>{{$I->phone}}</td>
                <td>{{$I->hotel_address}}</td>
                <td>{{$I->hotel_name}}</td>
                <td>{{$I->hotel_phone}}</td>
                <td>{{$I->getState()->getName()}}</td>
                <td>
                    @if($I->canPassPublish())
                        <label><input type="radio" name="{{ 'radio_'.$I->id }}" data-type="passPublish">通过审核</label><br>
                    @endif
                    @if($I->canFailPublish())
                        <label><input type="radio" name="{{ 'radio_'.$I->id }}" data-type="failPublish">不能通过审核</label><br>
                    @endif
                    @if($I->canFrozen())
                        <label><input type="radio" name="{{ 'radio_'.$I->id }}" data-type="frozen">冻结</label><br>
                    @endif
                    @if($I->canRelieveFrozen())
                        <label><input type="radio" name="{{ 'radio_'.$I->id }}" data-type="relieveFrozen">解除冻结</label><br>
                    @endif
                    <label><input type="radio" name="{{ 'radio_'.$I->id }}">什么也不做</label>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div style="text-align: center;margin-top:-20px;">
        <ul class="pagination pagination-lg">
            @if($page > 1)
                <li><a href="{{url('admin/show?page='.($page-1).'&pernum='.$pernum)}}">&laquo;</a></li>
            @else
                <li class="disabled"><a href="#">&laquo;</a></li>
            @endif
            @foreach($pageinationSowPages as $p)
                <li @if($p == $page)class="active"@endif><a href="{{url('admin/show?page='.$p.'&pernum='.$pernum)}}">{{$p}}</a></li>
            @endforeach
            @if($page < $totalPage)
                <li><a href="{{url('admin/show?page='.($page+1).'&pernum='.$pernum)}}">&raquo;</a></li>
            @else
                <li class="disabled"><a href="#">&raquo;</a></li>
            @endif
        </ul>
    </div>

    <form METHOD="post" action="../batchChangeStates">
        <div class="form-group">
            <button class="btn btn-success btn-lg btn-block" id="submit">提交</button>
            {!! csrf_field() !!}
        </div>
    </form>
    <!-- 模态框（Modal） -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="myCarousel" class="carousel slide" data-interval="false">
                        <!-- 轮播（Carousel）指标 -->
                        <ol class="carousel-indicators">
                        </ol>
                        <!-- 轮播（Carousel）项目 -->
                        <div class="carousel-inner">
                        </div>
                        <!-- 轮播（Carousel）导航 -->
                        <a class="carousel-control left" href="#myCarousel"
                           data-slide="prev">&lsaquo;</a>
                        <a class="carousel-control right" href="#myCarousel"
                           data-slide="next">&rsaquo;</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" id="image_progress"
                             aria-valuemin="0" aria-valuemax="100" style="width: 0">
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
@endsection
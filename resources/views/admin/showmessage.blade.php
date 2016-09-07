@extends('admin/layouts/layout')

@section('title', '消息')

@section('scripts')
    <script src="{!! asset('js/DateFormat.js') !!}"></script>
    <script>
        $(function(){
            var PAGE_SIZE = 10;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#accordion').on('click', 'a[data-toggle]', function(event) {
                event.preventDefault();
                var id = $(this).attr('href');
                $('#'+id).collapse('toggle');
            });
            $('#accordion').on('show.bs.collapse', '.panel-collapse', function () {
                setRead($(this).attr('id'));
            });
            $('#more').on('click', function() {
                loadNextPage();
            });

            loadNextPage();

            function loadPage(index, size) {
                size = size || 10;
                $.post('{!! url('admin/fetchmessages') !!}/' + index + '/' + size, function(data) {
                    for(var i = 0; i < data.length; i++) {
                        appendMessage(data[i]);
                    }
                });
            }
            function calcNextPageIndex() {
                var total = $('#accordion > .panel').length;
                return Math.ceil(total / PAGE_SIZE) + 1;
            }
            function loadNextPage() {
                loadPage(calcNextPageIndex(), PAGE_SIZE);
            }
            function setRead(id) {
                var $parent = $('#' + id).parent('div');
                if($parent.hasClass('panel-success')) {
                    $parent.removeClass('panel-success').addClass('panel-default');
                    $.post('{!! url('admin/setreadmessage') !!}/' + id, function(data) {
                        var $count = $('#no_read_count'),
                                count = parseInt($count.text()) - 1
                        $count.text(count);
                        $('#header_no_read_count').text(count);
                    });
                }
            }
            function appendMessage(msg) {
                var msgObj = $.parseJSON(msg);
                var template =  '<div class="panel [[panel-class]]">' +
                        '<div class="panel-heading clearfix">' +
                        '<h4 class="panel-title">' +
                        '<a data-toggle="collapse" data-parent="#accordion" href="[[id]]">来自于：[[from]]</a>' +
                        '<span class="pull-right">[[time]]</span>' +
                        '</h4>' +
                        '</div>' +
                        '<div id="[[id]]" class="panel-collapse collapse">' +
                        '<div class="panel-body">[[content]]</div>' +
                        '</div>' +
                        '</div>';
                template = template.replace(/\[\[content\]\]/g, msgObj.message);
                template = template.replace(/\[\[id\]\]/g, msgObj.id);
                template = template.replace(/\[\[panel-class\]\]/g, msgObj.is_read == 1 ? 'panel-default' : 'panel-success');
                template = template.replace(/\[\[from\]\]/g, msgObj.from);
                template = template.replace(/\[\[time\]\]/g, new Date(msgObj.created_at).Format('yyyy年MM月dd日'));
                $(template).appendTo($('#accordion'));
            }
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="alert alert-info">
            你有<span id="no_read_count">{!! count(\App\Classes\Logic\Message::getNoReadMessages4Admin()) !!}</span>条未读消息。
        </div>
    </div>
    <div class="row">
        <div class="panel-group" id="accordion">
        </div>
    </div>
    <div class="row">
        <button type="button" class="btn btn-primary btn-block" id="more">更多</button>
    </div>
@endsection
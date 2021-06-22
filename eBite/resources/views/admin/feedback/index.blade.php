<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理系统 - {{config('app.name')}}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">UID</label>
                    <div class="layui-input-block">
                        <input type="text" name="uid" placeholder="请输入帐号" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">邮箱</label>
                    <div class="layui-input-block">
                        <input type="text" name="email" placeholder="请输入" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">手机</label>
                    <div class="layui-input-block">
                        <input type="text" name="mobile" placeholder="请输入" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">反馈类型</label>
                    <div class="layui-input-block">
                        <select name="type_id">
                            <option value="0">不限</option>
                            @foreach($types as $types)
                                <option value="{{$types->id}}">{{$types->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="user">
                <p>@{{ d.user.uid }}</p>
            </script>
            <script type="text/html" id="switchTpl">
                <input type="checkbox" name="is_display" value="@{{d.id}}" lay-skin="switch" lay-text="是|否"
                       lay-filter="isDisplay" @{{ d.is_display== 1 ? 'checked' : '' }}>
            </script>
            <script type="text/html" id="table-tool">
                @{{#if(d.is_replied == 0) { }}
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="reply">回复</a>
                @{{#} }}

                @{{#if(d.is_replied == 1) { }}
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
                @{{#} }}

                {{--                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>--}}
            </script>
        </div>
    </div>
</div>

<script src="/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'useradmin', 'table'], function () {
        var $ = layui.$
            , form = layui.form
            , table = layui.table;

        //监听搜索
        form.on('submit(search)', function (data) {
            var field = data.field;

            //执行重载
            table.reload('list', {
                where: field
            });
        });

        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/feedback/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'title', title: '标题'},
                {field: 'uid', title: '用户', templet: '#user'},
                {field: 'type_name', title: '所属类别'},
                {field: 'content', title: '内容'},
                {field: 'reply', title: '回复'},
                {field: 'is_display', title: '显示', templet: '#switchTpl'},
                {field: 'created_at', title: '创建时间', sort: true},
                {title: '操作', width: 150, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档



        });
        //监听是否显示操作
        form.on('switch(isDisplay)', function (obj) {
            var id = this.value;
            $.ajax({
                url: '{{url('admin/feedback/is_display')}}',
                type: 'post',
                dataType: 'json',
                data: {id: id},
                success: function (res) {
                    if (res.type) {
                        layer.msg(res.message);
                        setTimeout(function () {
                            location.reload()
                        }, 1000)
                    }
                }
            });
        });

        //监听工具条
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'del') {
                layer.confirm('真的删除么', function (index) {
                    $.get('/admin/feedback/delete', {
                        id: data.id
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(index);
                        if (res.code) {
                            obj.del();
                        }
                    });
                });
            } else if (obj.event === 'reply') {
                layer.open({
                    type: 2,
                    title: '回复',
                    area: ['800px', '600px'],
                    content: '/admin/feedback/reply?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });
            } else if (obj.event === 'edit') {
                layer.open({
                    type: 2,
                    title: '编辑',
                    area: ['800px', '600px'],
                    content: '/admin/feedback/edit?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });
            }
        });
    });
</script>
</body>
</html>

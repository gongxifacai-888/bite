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
            <button class="layui-btn" lay-href="/admin/lang/add">添加语言</button>
        </div>
        </div>
        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
            </script>
        </div>
        <script type="text/html" id="titleTpl">
            <input type="checkbox" name="Status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否"
                   lay-filter="Status"
                   @{{ d.status== 1 ? 'checked' : '' }} >
        </script>
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

        //监听启动操作
        form.on('switch(Status)', function(obj){
            var id = this.value;

            $.ajax({
                url:'{{url('admin/lang/status')}}',
                type:'post',
                dataType:'json',
                data:{id:id},
                success:function (res) {
                    layer.msg(res.msg);
                    table.reload('list')
                }
            });
        });


        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/lang/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'code', title: '语言代码'},
                {field: 'name', title: '语言名称'},
                {field: 'status', title: '状态',templet: '#titleTpl'},
                {field: 'sort', title: '排序'},
                {title: '操作', width: 200, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档



        });

        //监听工具条
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'edit') {
                layer.open({
                    type: 2,
                    title: '编辑',
                    area: ['800px', '600px'],
                    content: '/admin/lang/add?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });
            } else if (obj.event === 'del') {
                layer.confirm('危险操作!真的删除么?', function (index) {
                    $.get('/admin/lang/delete', {
                        id: data.id
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(index);
                        if (res.code) {
                            obj.del();
                        }
                    });
                });
            }
        });

    });
</script>
</body>
</html>

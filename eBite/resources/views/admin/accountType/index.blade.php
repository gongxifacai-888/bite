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
        <div class="layui-card-header layuiadmin-card-header-auto">
            账户类型
        </div>
        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
        </div>
        <script type="text/html" id="switchStatus">
            <input type="checkbox" name="is_recharge" value="@{{d.id}}" lay-skin="switch" lay-text="是|否"
                   lay-filter="sexStatus"
                   @{{ d.is_recharge== 1 ? 'checked' : '' }} >
        </script>
        <script type="text/html" id="switchDisplay">
            <input type="checkbox" name="is_display" value="@{{d.id}}" lay-skin="switch" lay-text="是|否"
                   lay-filter="sexDisplay"
                   @{{ d.is_display== 1 ? 'checked' : '' }} >
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
        form.on('switch(sexStatus)', function (obj) {
            var id = this.value;
            $.ajax({
                url: '{{url('admin/account_type/update_recharge')}}',
                type: 'get',
                dataType: 'json',
                data: {id: id},
                success: function (res) {
                    layer.msg(res.msg);
                    table.reload('list');
                }
            });
        });
        form.on('switch(sexDisplay)', function (obj) {
            var id = this.value;
            $.ajax({
                url: '{{url('admin/account_type/update_display')}}',
                type: 'get',
                dataType: 'json',
                data: {id: id},
                success: function (res) {
                    layer.msg(res.msg);
                    table.reload('list');
                }
            });
        });
        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/account_type/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID'},
                {field: 'name', title: '名称'},
                {field: 'classname', title: '账户模型'},
                {field: 'is_recharge', title: '是否充币账户', templet: '#switchStatus', width: 150,},
                {field: 'is_display', title: '是否显示', templet: '#switchDisplay', width: 150,},
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档



        });

    });
</script>
</body>
</html>

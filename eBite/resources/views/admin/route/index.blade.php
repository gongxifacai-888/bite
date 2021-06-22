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

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="table-tool">
            </script>
        </div>
        <script type="text/html" id="switchTpl">
            <input type="checkbox" name="isAddLog" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="isAddLog" @{{ d.is_add_log == 1 ? 'checked' : '' }}>
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
            , url: '/admin/route/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'name', title: '名称'},
                {field: 'url', title: '路由'},
                {field: 'is_add_log', title: '是否记录日志', templet: '#switchTpl'},
                // {title: '操作', width: 150, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档



        });

        //监听是否显示操作
        form.on('switch(isAddLog)', function(obj){
            var id = this.value;
            $.ajax({
                url:'{{url('admin/route/add_log')}}',
                type:'post',
                dataType:'json',
                data:{id:id},
                success:function (res) {
                    if(res.code != 0){
                        layer.msg(res.msg);
                    }
                }
            });
        });

    });
</script>
</body>
</html>

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
            <button class="layui-btn" lay-href="/admin/trading_candy/add">添加向导</button>
        </div>

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="dec_currency">
                @{{ d.dec_currency.name }}
            </script>
            <script type="text/html" id="inc_currency">
                @{{ d.inc_currency.name }}
            </script>
            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
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
        form.on('submit(search)', function (data) {
            var field = data.field;
            table.reload('list', {
                where: field
            });
        });
        table.render({
            elem: '#list'
            , url: '/admin/trading_candy/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'name', title: '活动名'},
                {field: 'dec_currency_id', title: '操作币种', templet: '#dec_currency'},
                {field: 'dec_number', title: '币种估值'},
                {field: 'inc_currency_id', title: '平分币种', templet: '#inc_currency'},
                {field: 'inc_number', title: '平分数量'},
                {field: 'total_number', title: '平分总数'},
                {field: 'surplus_number', title: '是否开启'},
                {field: 'begin_at', title: '开始时间'},
                {field: 'end_at', title: '结束时间时间'},
                {title: '操作', width: 400, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'
            , limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档
        });
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'edit') {
                parent.layui.index.openTabsPage('/admin/trading_candy/edit?id=' + data.id,
                    data.name + "编辑");
            } else if (obj.event === 'delete') {
                layer.confirm('确定要删除吗?', function (index) {
                    $.get('/admin/trading_candy/delete', {
                        id: data.id
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(index);
                    });
                });
            }
        });
    });
</script>
</body>
</html>

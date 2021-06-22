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
            <button class="layui-btn" lay-href="/admin/currency_protocol/add">添加币种协议</button>
        </div>
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <blockquote class="layui-elem-quote">如果你不明白本页面的设置是做什么的,那么请不要操作</blockquote>
        </div>

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>

            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit_in_address">入金总账号</a>
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit_out_address">出金总账号</a>
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

        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/currency_protocol/list?currency_id={{$currency_id}}' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'currency_code', title: '币种'},
                {field: 'chain_protocol_code', title: '区块链协议'},
                {field: 'in_address', title: '入金总账号'},
                {field: 'out_address', title: '出金总账号'},
                {field: 'decimal', title: '小数位数'},
                {title: '操作', width: 300, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            , limits: [10, 20, 50, 100, 200, 500, 1000]
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
                    content: '/admin/currency_protocol/edit?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });
            } else if (obj.event === 'edit_in_address') {
                layer.open({
                    type: 2,
                    title: '编辑入金总账号',
                    area: ['800px', '600px'],
                    content: '/admin/currency_protocol/edit_in_address?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });
            } else if (obj.event === 'edit_out_address') {
                layer.open({
                    type: 2,
                    title: '编辑出金总账号',
                    area: ['800px', '600px'],
                    content: '/admin/currency_protocol/edit_out_address?id=' + data.id,
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

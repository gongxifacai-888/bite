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
        <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="form">
            <div class="layui-inline" style="margin-left: 50px;">
                <label>后台用户&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="username" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>请求方式&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="method" id="method" class="layui-input">
                        <option value="">所有类型</option>
                        <option value="GET" class="ww">GET</option>
                        <option value="POST" class="ww">POST</option>
                        <option value="PUT" class="ww">PUT</option>
                        <option value="DELETE" class="ww">DELETE</option>
                        <option value="OPTIONS" class="ww">OPTIONS</option>
                        <option value="HEAD" class="HEAD">HEAD</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>ip&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="ip" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>路径&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="path" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-input-inline date_time111" style="margin-left: 50px;">
                <label>开始时间&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="start_time" id="start_time" placeholder="请输入开始时间" autocomplete="off"
                           class="layui-input" value="">
                </div>
            </div>
            <div class="layui-input-inline date_time111" style="margin-left: 50px;">
                <label>结束时间&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="end_time" id="end_time" placeholder="请输入结束时间" autocomplete="off"
                           class="layui-input" value="">
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                    <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                </button>
            </div>
        </div>
        <div class="layui-card-body">
            <script type="text/html" id="admin">
                @{{ d.admin.username }}
            </script>
            <table id="list" lay-filter="list"></table>
        </div>
    </div>
</div>

<script src="/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'useradmin', 'table', 'laydate'], function () {
        var $ = layui.$
            , form = layui.form
            , table = layui.table;

        var laydate = layui.laydate;

        laydate.render({
            elem: '#start_time'
        });
        laydate.render({
            elem: '#end_time'
        });

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
            , url: '/admin/operation/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID'}
                , {field: 'admin_name', title: '登录用户', width: 100, templet: '#admin'}
                , {field: 'method', title: '请求方式', width: 100}
                , {field: 'name', title: '名称', width: 100}
                , {field: 'ip', title: 'ip', width: 140}
                , {field: 'request_path', title: '路由', minWidth: 100}
                , {field: 'data', title: '参数', minWidth: 100}
                , {field: 'created_at', title: '创建时间', minWidth: 100},
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

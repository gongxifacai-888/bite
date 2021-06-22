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
            <button class="layui-btn" lay-href="/admin/user/add">添加用户</button>
        </div>
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">UID</label>
                    <div class="layui-input-block">
                        <input type="text" name="uid" placeholder="请输入" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">手机</label>
                    <div class="layui-input-block">
                        <input type="text" name="mobile" placeholder="请输入" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">邮箱</label>
                    <div class="layui-input-block">
                        <input type="text" name="email" placeholder="请输入" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">

                <div class="layui-inline">
                    <label class="layui-form-label">开始时间</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="start_time" id="start_time">
                    </div>
                </div>

                <div class="layui-inline">
                    <label class="layui-form-label">结束时间</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="over_time" id="over_time">
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

            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-default layui-btn-xs" lay-event="edit">详情</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="wallet_list">资产管理</a>
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
    }).use(['index', 'useradmin', 'table', 'laydate'], function () {
        var $ = layui.$
            , form = layui.form
            , table = layui.table
            , laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#over_time' //指定元素
        });
        laydate.render({
            elem: '#start_time' //指定元素
        });

        //监听搜索
        form.on('submit(search)', function (data) {
            var field = data.field;
            //执行重载
            table.reload('list', {
                where: field,
                page: {
                    curr: 1
                }
            });
        });
        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/user/list' //模拟接口
            , cols: [[
                {field: 'id', width: 80, title: 'id', sort: true},
                {field: 'uid', title: 'UID', width: 120},
                {field: 'mobile', title: '手机', width: 130},
                {field: 'email', title: '邮箱', width: 200},
                {field: 'invite_code', width: 100, title: '邀请码'},
                {field: 'status_name', width: 100, title: '状态'},
                {field: 'last_login_at', width: 200, title: '最后登录时间', sort: true},
                {field: 'created_at', width: 200, title: '注册时间', sort: true},
                {title: '操作', width: 200, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            , limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档


        });
        //监听锁定操作
        form.on('switch(lock)', function (obj) {
            var id = this.value;

            $.ajax({
                url: '{{url('admin/user/lock')}}',
                type: 'post',
                dataType: 'json',
                data: {id: id},
                success: function (res) {
                    layer.msg(res.msg);
                    table.reload('list');
                }
            });
        });
        //监听工具条
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'wallet_list') {
                parent.layui.index.openTabsPage('/admin/account/index?uid=' + data.uid,
                    "用户" + data.uid + "资产");
            } else if (obj.event === 'edit') {
                layer.open({
                    type: 2,
                    title: '用户信息',
                    area: ['800px', '600px'],
                    content: '/admin/user/edit?user_id=' + data.id,
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

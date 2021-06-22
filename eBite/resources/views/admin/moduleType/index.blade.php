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
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">分类名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" placeholder="请输入" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                        添加
                    </button>
                </div>
            </div>
        </div>

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="imgTpl">
                <img style="display: inline-block;height: 100%;" src="@{{ d.avatar }}">
            </script>
            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
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

            $.post('/admin/module_type/save', field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    form.val('form', {
                        name: '',
                    });
                    table.reload('list');
                }
            });
        });

        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/module_type/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID'},
                {field: 'name', title: '分类名称', width: 200,},
                {field: 'module_names', title: '权限'},
                {title: '操作', width: 150, align: 'center', fixed: 'right', toolbar: '#table-tool'}
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
            if (obj.event === 'del') {
                layer.confirm('真的删除么?', function (index) {
                    $.get('/admin/module_type/delete', {
                        id: data.id
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(index);
                        if (res.code) {
                            obj.del();
                        }
                    });
                });
            } else if (obj.event === 'edit') {
                layer.prompt({
                    value: data.value,
                    title: '请输入新值',
                }, function (value, index, elem) {
                    $.post('/admin/module_type/update', {
                        id: data.id,
                        name: value
                    }, function (res) {
                        layer.close(index);
                        layer.msg(res.msg);
                        if (res.code) {
                            table.reload('list');
                        }
                    });
                });
            }
        });

    });
</script>
</body>
</html>

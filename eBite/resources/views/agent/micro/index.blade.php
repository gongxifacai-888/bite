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
            <button class="layui-btn" lay-href="/admin/micro_number_add">添加设置</button>
        </div>

        <div class="layui-card-body">

            <table id="demo" lay-filter="test"></table>
            <script type="text/html" id="barDemo">
                {{--<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">调节账户</a>--}}
                <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
            </script>

        </div>

    </div>
</div>

<script src="/layuiadmin/layui/layui.js"></script>

<script type="text/html" id="status">
    <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" @{{
           d.status== 1 ? 'checked' : '' }}>
</script>
<script>

    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'useradmin', 'table', 'laydate'], function () {
        var table = layui.table;
        var $ = layui.jquery;
        var form = layui.form;
        //第一个实例

        table.render({
            elem: '#demo'
            ,url: '/admin/micro_numbers_list' //数据接口
            ,page: true //开启分页
            ,height: 'full-100'
            ,cols: [[ //表头
                {field: 'id', title: 'ID', minWidth: 80, sort: true}
                ,{field: 'currency_code', title: '币种名称', minWidth: 80}
                ,{field: 'number', title: '数量', minWidth: 80}
                ,{title: '操作', minWidth: 100, toolbar: '#barDemo'}
            ]]
        });

        table.on('tool(test)', function (obj) {
            var data = obj.data;
            if (obj.event === 'del') {
                layer.confirm('真的删除行么', function (index) {
                    $.ajax({
                        url: '/admin/micro_number_del',
                        type: 'post',
                        dataType: 'json',
                        data: {id: data.id},
                        success: function (res) {
                            if (!res.code) {
                                layer.msg(res.message);
                            } else {
                                obj.del();
                                layer.close(index);
                            }
                        }
                    });


                });
            } else if (obj.event === 'edit') {
                layer.open({
                    type: 2,
                    title: '修改',
                    area: ['800px', '600px'],
                    content: '/admin/micro_number_add?id=' + data.id,
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

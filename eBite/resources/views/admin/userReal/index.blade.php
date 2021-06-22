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
                        <input type="text" name="uid" placeholder="请输入" class="layui-input">
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
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="switchTpl">
                <input type="checkbox" name="review_status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否"
                       lay-filter="review_status"
                       @{{ d.review_status== 2 ? 'checked' : '' }} >
            </script>
            <script type="text/html" id="uid">
                @{{ d.user.uid }}
            </script>
            <script type="text/html" id="barDemo">
                <a class="layui-btn layui-btn-default layui-btn-xs" lay-event="detail">查看</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
            </script>
            <script type="text/html" id="mobile">
                @{{ d.user.mobile }}
            </script>
            <script type="text/html" id="email">
                @{{ d.user.email }}
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

        //监听搜索
        form.on('submit(search)', function (data) {
            var field = data.field;
            //执行重载
            table.reload('list', {
                where: field
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
        });

        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/user_real/list' //模拟接口
            , cols: [[
                {field: 'id', width: 80, title: 'ID', sort: true},
                {field: 'uid', title: 'UID', width: 150, templet: '#uid'},
                {field: 'mobile', title: '手机', templet: '#mobile', width: 150},
                {field: 'email', title: '邮箱', templet: '#email', width: 150},
                {field: 'name', title: '真实姓名', width: 150},
                {field: 'review_status', title: '是否审核', width: 150, templet: '#switchTpl'},
                {field: 'created_at', title: '申请时间', width: 200},
                {title: '操作', width: 200, align: 'center', fixed: 'right', toolbar: '#barDemo'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档




        });
        //监听锁定操作
        form.on('switch(review_status)', function (obj) {
            var id = this.value;

            $.ajax({
                url: '{{url('admin/user_real/review_status')}}',
                type: 'post',
                dataType: 'json',
                data: {id: id},
                success: function (res) {
                    layer.msg(res.msg);
                    table.reload('list')
                }
            });
        });
        //监听工具条
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'detail') {
                layer.open({
                    type: 2,
                    title: '认证详情',
                    area: ['800px', '600px'],
                    content: '/admin/user_real/detail?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });
            } else if (obj.event === 'delete') {
                layer.confirm('真的要删除吗？', function (index) {
                    //向服务端发送删除指令
                    $.ajax({
                        url: "{{url('admin/user_real/delete')}}",
                        type: 'get',
                        dataType: 'json',
                        data: {id: data.id},
                        success: function (res) {
                            if (res.code == 1) {
                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                layer.close(index);
                            } else {
                                layer.close(index);
                                layer.alert(res.msg);
                            }
                        }
                    });
                });
            }
        });

    });
</script>
</body>
</html>

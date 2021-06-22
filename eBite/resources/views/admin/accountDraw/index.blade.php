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
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-inline">
                        <select name="status" id="status">
                            <option value="0">所有</option>
                            <option value="1">待审核</option>
                            <option value="2">已汇出</option>
                            <option value="3">汇出失败</option>
                            <option value="4">已成功</option>
                            <option value="4">已驳回</option>
                        </select>
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
            <script type="text/html" id="uid">
                @{{ d.user.uid }}
            </script>
            <script type="text/html" id="currency">
                @{{ d.currency.code }}
            </script>
            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-default layui-btn-xs" lay-event="allow">查看</a>
                {{--<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="refuse">驳回</a>--}}
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
            , table = layui.table;
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
            , url: '/admin/account_draw/list' //模拟接口
            , cols: [[
                {field: 'id', width: 80, title: 'ID', sort: true},
                {field: 'user_id', width: 150, title: 'UID', templet: '#uid'},
                {field: 'currency_id', width: 100, title: '币种', templet: '#currency'},
                {field: 'address', title: '提币地址', minWidth: 100,},
                {field: 'number', title: '提币数量', minWidth: 100,},
                // {field: 'real_number', title: '真实到账数量', minWidth: 100,},
                // {field: 'fee', title: '手续费',minWidth:100},
                {field: 'status_name', title: '状态', minWidth: 100,},

                // {field: 'tx_hash', title: '交易哈希'},
                {field: 'created_at', width: 180, title: '申请时间', sort: true},
                {field: 'review_at', width: 180, title: '审核时间', sort: true},
                {title: '操作', width: 130, align: 'center', fixed: 'right', toolbar: '#table-tool'}
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
            if (obj.event === 'allow') {
                // layer.prompt({title: '请输入验证码', formType: 0}, function (code, index) {
                //     layer.close(index);
                //     $.get('/admin/account_draw/allow', {
                //         id: data.id,
                //         code: code
                //     }, function (res) {
                //         layer.close(index);
                //         layer.msg(res.msg);
                //         if (res.type) {
                //             table.reload('list');
                //         }
                //     });
                // });
                layer.open({
                    type: 2,
                    // title: '修改',
                    area: ['800px', '600px'],
                    content: '/admin/account_draw/detail?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });
                // layer.prompt({
                //     formType: 1,
                //     code: '初始值',
                //     title: '请输入验证码',
                //     // area: ['800px', '350px'] //自定义文本域宽高
                // }, function (code, index,) {
                //     layer.close(index);
                //     $.get('/admin/account_draw/allow', {
                //         id: data.id,
                //         code: code
                //     }, function (res) {
                //         layer.close(index);
                //         layer.msg(res.msg);
                //         if (res.type) {
                //             table.reload('list');
                //         }
                //     });
                // });
            } else if (obj.event === 'refuse') {
                layer.confirm('确定驳回?', function (index) {
                    $.ajax({
                        url: "{{url('admin/account_draw/refuse')}}",
                        type: 'get',
                        dataType: 'json',
                        data: {id: data.id},
                        success: function (res) {
                            layer.msg(res.msg);
                            table.reload('list');
                        },
                        error: function (res) {
                            layer.msg(res.msg);
                        }
                    });
                })
            }
        });

    });
</script>
</body>
</html>

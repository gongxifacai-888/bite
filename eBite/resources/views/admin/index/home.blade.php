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

    <style>
        .layui-card-body h1{
            margin: 30px 0;
        }
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-row layui-col-space10">
        <div class="layui-col-lg3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <h1>用户数量</h1>
                    <hr>
                    <h1>
                        <a lay-href="/admin/user/index" title="去用户列表?" lay-text="用户列表">{{$user_count}}</a>
                    </h1>
                </div>
            </div>
        </div>
        <div class="layui-col-lg3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <h1>今日登陆</h1>
                    <hr>
                    <h1>{{$today_login}}</h1>
                </div>
            </div>
        </div>
        <div class="layui-col-lg3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <h1>今日注册</h1>
                    <hr>
                    <h1>{{$today_register}}</h1>
                </div>
            </div>
        </div>
        <div class="layui-col-lg3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <h1>今日反馈未回复</h1>
                    <hr>
                    <h1>
                        <a lay-href="/admin/feedback/index" title="去用户反馈列表?" lay-text="用户反馈列表">{{$today_feedback}}</a>
                    </h1>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row layui-col-space10">
        <div class="layui-col-lg6">
            <div class="layui-card">
                <div class="layui-card-header">
                    提币申请
                    <a lay-href="/admin/account_draw/index" class="layui-badge layui-bg-blue layuiadmin-badge">
                        更多提币申请
                    </a>
                </div>
                <div class="layui-card-body">
                    <table id="wallet-out" lay-filter="wallet-out"></table>
                    <script type="text/html" id="account">
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
        <div class="layui-col-lg6">
            <div class="layui-card">
                <div class="layui-card-header">
                    充币记录
                    <a lay-href="/admin/account_log/change?type=1" class="layui-badge layui-bg-blue layuiadmin-badge">
                        更多充币记录
                    </a>
                </div>
                <div class="layui-card-body">
                    <table id="log" lay-filter="log"></table>
                    <script type="text/html" id="account">
                        <p>@{{ d.user.account }}</p>
                    </script>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'form', 'upload', 'table'], function () {
        var $ = layui.$
            , form = layui.form
            , table = layui.table
            , upload = layui.upload;

//用户管理
        table.render({
            elem: '#wallet-out'
            , url: '/admin/account_draw/list' //模拟接口
            , cols: [[
                {field: 'user_id', width: 150, title: '帐号', templet: '#account'},
                {field: 'currency_id', width: 100, title: '币种', templet: '#currency'},
                {field: 'number', title: '提币数量', minWidth: 100,},
                {field: 'status_name', title: '状态', minWidth: 100,},
                {title: '操作', width: 130, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 10

            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档




        });
        //监听工具条
        table.on('tool(wallet-out)', function (obj) {
            var data = obj.data;
            if (obj.event === 'allow') {

                layer.open({
                    type: 2,
                    // title: '修改',
                    area: ['800px', '600px'],
                    content: '/admin/account_draw/detail?id=' + data.id,
                    end: function () {
                        table.reload('list');
                    }
                });

            }
        });

        //用户管理
        table.render({
            elem: '#log'
            , url: '/admin/account_log/change_list?type=1' //模拟接口
            , cols: [[
                {field: 'user', title: '帐号', width: 150, templet: '#account'},
                {field: 'value', title: '充币数量'},
                {field: 'currency', title: '币种', width: 150, templet: '#currency'},
                {field: 'created_at', title: '时间', width: 180, sort: true},
            ]]
            , page: true
            , limit: 10

            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档



        });

    })
</script>
</body>
</html>

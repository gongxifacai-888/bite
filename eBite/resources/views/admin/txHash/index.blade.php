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
                        <input type="text" name="uid" placeholder="请输入UID" class="layui-input">
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

            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <select name="status">
                            <option value="-1">不限</option>
                            <option value="0">等待中</option>
                            <option value="1">已完成</option>
                            <option value="2">失败</option>
                        </select>
                    </div>
                </div>
                @if($type == -1)
                    <div class="layui-inline">
                        <label class="layui-form-label">类型</label>
                        <div class="layui-input-block">
                            <select name="type">
                                <option value="-1">不限</option>
                                @foreach($type_list as $types)
                                    <option value="{{$types[0]}}">{{$types[1]}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="user">
                <p>@{{ d.user.uid }}</p>
            </script>
            <script type="text/html" id="currency">
                <p>@{{ d.currency_protocol.currency.code}} - @{{ d.currency_protocol.chain_protocol.code }}</p>
            </script>

            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-default layui-btn-xs" lay-event="tokenview">去tokenview查看</a>
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

            //执行重载
            table.reload('list', {
                where: field
            });
        });

        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/tx_hash/list?type={{request('type',-1)}}' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'user', title: 'UID', width: 150, templet: '#user'},
                {field: 'currency', title: '币种', width: 150, templet: '#currency'},
                {field: 'hash', title: '哈希', width: 200},
                {field: 'status_name', title: '状态', minWidth: 100, templet: '#tx_status'},
                {field: 'memo', title: '备注', minWidth: 100},
                {field: 'created_at', title: '时间', width: 180, sort: true},
                {title: '操作', width: 200, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            , limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档


            , done: function (res, curr, count) {
                console.log(res);

            }
        });

        //监听工具条
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'tokenview') {
                window.open('https://tokenview.com/cn/search/' + data.hash);
            }
        });
    });
</script>
</body>
</html>

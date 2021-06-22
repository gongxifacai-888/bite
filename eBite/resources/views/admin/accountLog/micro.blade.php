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
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-block">
                        <select name="currency_id">
                            <option value="0">不限</option>
                            @foreach($currencies as $currency)
                                <option value="{{$currency->id}}">{{$currency->code}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">类型</label>
                    <div class="layui-input-block">
                        <select name="type">
                            <option value="0">不限</option>
                            @foreach($types as $type)
                                <option value="{{$type[0]}}"
                                        @if(request('type',0)==$type[0]) selected @endif>{{$type[1]}}</option>
                            @endforeach
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
        <div class="layui-form layui-card-header">
            总量：<span id="sum">0</span>
        </div>

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
            <script type="text/html" id="user">
                <p>@{{ d.user.uid }}</p>
            </script>
            <script type="text/html" id="currency">
                <p>@{{ d.currency.code }}</p>
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
            , url: '/admin/account_log/micro_list?type={{request('type',0)}}' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'user', title: 'uid帐号', width: 150, templet: '#user'},
                {field: 'before', title: '更改前'},
                {field: 'value', title: '更改值'},
                {field: 'after', title: '更改后'},
                {field: 'memo', title: '备注'},
                {field: 'is_lock_name', title: '锁定余额'},
                {field: 'currency', title: '币种', width: 150, templet: '#currency'},
                {field: 'created_at', title: '时间', width: 180, sort: true},
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'
            ,  limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档
            , done: function (res, curr, count) {
                console.log(res);
                $('#sum').text(res.extra_data.sum);

            }
        });

    });
</script>
</body>
</html>

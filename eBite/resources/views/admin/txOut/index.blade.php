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
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">卖出挂单</div>
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
                    <label class="layui-form-label">交易对</label>
                    <div class="layui-input-block">
                        <select class="layui-select" name="currency_match_id" id="">
                            <option value="0">不限</option>
                            @foreach($currency_matches as $currencyMatch)
                                <option value="{{$currencyMatch->id}}">{{$currencyMatch->symbol}}</option>
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

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>

            <script type="text/html" id="account">
                @{{ d.user.uid }}
            </script>
            <script type="text/html" id="symbol">
                @{{ d.currency_match.symbol }}
            </script>

            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-default layui-btn-xs" lay-event="cancel">撤单</a>
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
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
        });
        //用户管理
        table.render({
            elem: '#list'
            , url: '/admin/tx_out/list' //模拟接口
            , cols: [[
                {field: 'id', width: 80, title: 'ID', sort: true},
                {field: 'account', title: 'uid帐号', templet: '#account'},
                {field: 'price', title: '价格'},
                {field: 'number', title: '数量'},
                {field: 'transacted_volume', title: '成交额'},
                {field: 'transacted_amount', title: '成交量'},
                {field: 'symbol', title: '交易对', templet: '#symbol'},
                {field: 'created_at', width: 200, title: '创建时间', sort: true},
                {title: '操作', width: 200, align: 'center', fixed: 'right', toolbar: '#table-tool'}
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
            layer.confirm('危险操作!真的撤单么?', function (index) {
                $.get('/admin/tx_out/delete', {
                    id: data.id,
                }, function (res) {
                    layer.msg(res.msg);
                })
            });
        });

    });
</script>
</body>
</html>

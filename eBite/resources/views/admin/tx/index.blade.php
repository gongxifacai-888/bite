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
        .sell td {
            color: #ff4e56 !important;
        }

        .buy td {
            color: #00b887 !important;
        }
    </style>

</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">交易对</label>
                    <div class="layui-input-block">
                        <select class="layui-select" name="currency_match_id" id="">
                            @foreach($currency_matches as $currencyMatch)
                                <option value="{{$currencyMatch->id}}">{{$currencyMatch->symbol}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="layui-inline">
                    <button id="search" class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                </div>
                <div class="layui-inline">
{{--                    <button id="clear" class="layui-btn layuiadmin-btn-useradmin" type="button">清空盘口</button>--}}
                    <button id="load" class="layui-btn layuiadmin-btn-useradmin" type="button">载入盘口</button>
                </div>
            </div>
        </div>


        <div class="layui-card-body">
            <div class="layui-row  layui-col-space15">
                <table id="demo" lay-filter="demo"></table>
                <div class="layui-col-xs6 buy">
                    <h2 style="margin-bottom: 15px">买盘</h2>
                    <table id="buy" lay-filter="buy"></table>
                </div>
                <div class="layui-col-xs6 sell">
                    <h2 style="margin-bottom: 15px">卖盘</h2>
                    <table id="sell" lay-filter="sell"></table>
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
    }).use(['index', 'useradmin', 'table'], function () {
        var $ = layui.$
            , form = layui.form
            , table = layui.table;

        //监听搜索
        form.on('submit(search)', function (data) {
            var field = data.field;

            load(field);
        });

        $('#search').click();

        $('#clear').click(function () {
            layer.confirm('危险操作!真的清空吗?', function (index) {
                $.get('/admin/tx/delete', function (res) {
                    layer.msg(res.msg);
                })
            });
        });
        $('#load').click(function () {
            layer.confirm('危险操作!真的载入吗?,未载入成功期间无法挂单,如非必要,请勿点击', function (index) {
                $.get('/admin/tx/add', function (res) {
                    layer.msg(res.msg);
                })
            });
        });

        function load(field) {
            $.get('/admin/tx/list', field, function (res) {
                console.log(res);

                table.render({
                    elem: '#buy'
                    , cols: [[
                        {field: 'index', title: '位置'},
                        {field: 'price', title: '价格'},
                        {field: 'amount', title: '数量'},
                        {field: 'sum', title: '累计'},
                    ]]
                    , height: 'full-220'
                    , data: res.data.in
                    , limit: 999999999
                });

                table.render({
                    elem: '#sell'
                    , cols: [[
                        {field: 'index', title: '位置'},
                        {field: 'price', title: '价格'},
                        {field: 'amount', title: '数量'},
                        {field: 'sum', title: '累计'},
                    ]]
                    , height: 'full-220'
                    , data: res.data.out
                    , limit: 999999999
                });

            })
        }

    });
</script>
</body>
</html>

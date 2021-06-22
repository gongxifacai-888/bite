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

        <div class="layui-form layui-card-header layuiadmin-card-header-auto">行情列表</div>

        <script type="text/html" id="symbol">
            @{{ d.currency_match.symbol }}
        </script>

        <script type="text/html" id="change">
            @{{#  if(d.change < 0){ }}
            <span style="color: #F00;">@{{ d.change }}</span>
            @{{#  } else { }}
            <span style="color: #0F0;">@{{ d.change }}</span>
            @{{#  } }}
        </script>

        <div class="layui-card-body">
            <table id="list" lay-filter="list"></table>
        </div>


        <script type="text/html" id="table-tool">
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="clear_kline">清除K线</a>
            <a class="layui-btn layui-btn-default  layui-btn-xs" lay-event="import_kline">从火币导入历史K线</a>
        </script>
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

        table.render({
            elem: '#list'
            , url: '/admin/currency_quotation/list' //模拟接口
            , cols: [[
                {field: 'id', width: 100, title: 'ID', sort: true},
                {field: 'symbol', title: '交易对', width: 100, templet: '#symbol'},
                {field: 'open', title: '开盘价'},
                {field: 'close', title: '收盘价'},
                {field: 'high', title: '最高价'},
                {field: 'low', title: '最低价'},
                {field: 'amount', title: '交易量'},
                {field: 'volume', title: '交易额'},
                {field: 'change', title: '涨跌幅', templet: '#change'},
                {title: '操作', width: 300, align: 'center', fixed: 'right', toolbar: '#table-tool'}
            ]]
            , page: true
            , limit: 20
            , height: 'full-220'

            , limits: [10, 20, 50, 100, 200, 500, 1000]
            , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档


        });

        //监听工具条
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'clear_kline') {
                layer.confirm('危险操作!真的清除k线么', function (index) {
                    $.get('/admin/currency_quotation/clear_kline', {
                        currency_match_id: data.currency_match_id
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(index);
                        if (res.code) {
                            table.reload('list')
                        }
                    });
                });
            } else if (obj.event === 'import_kline') {

                layer.prompt({
                    value: data.currency_match.lower_symbol,
                    title: '输入要导入火币的交易对',
                }, function (value, index, elem) {
                    layer.close(index);
                    var loading = layer.load(1, {time: 30 * 1000});
                    $.get('/admin/currency_quotation/import_kline', {
                        currency_match_id: data.currency_match_id,
                        other_symbol: value
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(loading);
                        if (res.code) {
                            table.reload('list')
                        }
                    });
                });

            }
        });
    });
</script>
</body>
</html>

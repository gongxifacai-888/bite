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
{{--        <div class="layui-form layui-card-header layuiadmin-card-header-auto">--}}
{{--            <button class="layui-btn" lay-href="/admin/storage_currency/add">添加向导</button>--}}
{{--        </div>--}}
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">手机</label>
                    <div class="layui-input-block">
                        <input type="text" name="mobile" placeholder="请输入" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-block">
                        <select name="quote_currency_id" id="">
                            <option value="0">不限</option>
                            @foreach($currencies as $quoteCurrency)
                                <option value="{{$quoteCurrency->id}}">{{$quoteCurrency->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <div class="layui-form-item">
                        <label class="layui-form-label">开始时间</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" id="begin_at" name="begin_at">
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <div class="layui-form-item">
                        <label class="layui-form-label">结束时间</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" id="end_at" name="end_at">
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                </div>
            </div>
            <div class="layui-card-body">
                <table id="list" lay-filter="list"></table>
                <script type="text/html" id="dec_currency">
                    @{{ d.dec_currency.name }}
                </script>
                <script type="text/html" id="inc_currency">
                    @{{ d.inc_currency.name }}
                </script>
                <script type="text/html" id="table-tool">
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
                    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
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
            form.on('submit(search)', function (data) {
                var field = data.field;
                table.reload('list', {
                    where: field
                });
            });
            var laydate = layui.laydate;
            laydate.render({
                elem: '#begin_at',
                type: 'datetime',
            });
            laydate.render({
                elem: '#end_at',
                type: 'datetime',
            });
            table.render({
                elem: '#list'
                , url: '/admin/storage_currency_history/list' //模拟接口
                , cols: [[
                    {field: 'id', width: 100, title: 'ID', sort: true},
                    {field: 'dec_number', title: '用户'},
                    {field: 'currency_id', title: '币种', templet: '#currency'},
                    {field: 'inc_number', title: '数量'},
                    {field: 'total_number', title: '存取方式'},
                    {field: 'surplus_number', title: '年化利率'},
                    {field: 'surplus_number', title: '存币时间'},
                    {title: '操作', width: 400, align: 'center', fixed: 'right', toolbar: '#table-tool'}
                ]]
                , page: true
                , limit: 20
                , height: 'full-220'
                , limits: [10, 20, 50, 100, 200, 500, 1000]
                , toolbar: true //开启工具栏，此处显示默认图标，可以自定义模板，详见文档
            });
            table.on('tool(list)', function (obj) {
                var data = obj.data;
                if (obj.event === 'edit') {
                    parent.layui.index.openTabsPage('/admin/trading_candy/edit?id=' + data.id,
                        data.name + "编辑");
                } else if (obj.event === 'delete') {
                    layer.confirm('确定要删除吗?', function (index) {
                        $.get('/admin/trading_candy/delete', {
                            id: data.id
                        }, function (res) {
                            layer.msg(res.msg);
                            layer.close(index);
                        });
                    });
                }
            });
        });
    </script>
</body>
</html>

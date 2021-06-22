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
        <div class="layui-card-header layuiadmin-card-header-auto">
            <button class="layui-btn" lay-href="/admin/currency_match/add">添加交易对</button>
        </div>
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">计价币</label>
                    <div class="layui-input-block">
                        <select name="quote_currency_id" id="">
                            <option value="0">不限</option>
                            @foreach($quote_currencies as $quoteCurrency)
                                <option value="{{$quoteCurrency->id}}">{{$quoteCurrency->code}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">交易币</label>
                    <div class="layui-input-block">
                        <select name="base_currency_id" id="">
                            <option value="0">不限</option>
                            @foreach($base_currencies as $baseCurrency)
                                <option value="{{$baseCurrency->id}}">{{$baseCurrency->code}}</option>
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

            <script type="text/html" id="table-tool">
                <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a>
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
            , url: '/admin/currency_match/list' //模拟接口
            , cols: [[
                {field: 'id', title: 'ID', sort: true},
                {field: 'base_currency_code', minWidth: 100, title: '交易币'},
                {field: 'quote_currency_code', minWidth: 100, title: '计价币'},
                {field: 'symbol', minWidth: 150, title: '交易对'},
                {field: 'market_from_name', minWidth: 100, title: '行情来源'},
                {field: 'sort', minWidth: 100, title: '排序'},
                {title: '操作', width: 150, align: 'center', fixed: 'right', toolbar: '#table-tool'}
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
            if (obj.event === 'delete') {
                layer.confirm('危险操作!真的删除么', function (index) {
                    $.get('/admin/currency_match/delete', {
                        id: data.id
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(index);
                        if (res.code) {
                            obj.del();
                        }
                    });
                });
            } else if (obj.event === 'edit') {
                layer.open({
                    type: 2,
                    title: '编辑',
                    area: ['800px', '600px'],
                    content: '/admin/currency_match/edit?id=' + data.id,
                    end: function () {
                        table.reload('list')
                    }
                });
            }
        });

    });
</script>
</body>
</html>

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
                    <label class="layui-form-label">币种协议</label>
                    <div class="layui-input-inline">
                        <select name="currency_protocol_id">
                            <option value="0">不限</option>
                            @foreach ($currency_protocols as  $currencyProtocol)
                                <option value="{{$currencyProtocol->id}}">{{$currencyProtocol->currency->code}} - {{$currencyProtocol->chainProtocol->code}}</option>
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

            <script type="text/html" id="toolbar">
                <a class="layui-btn layui-btn-default layui-btn-xs" lay-event="refresh">刷新链上余额</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="transfer_fee">转入手续费</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="collect">归拢</a>
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

        table.render({
            elem: '#list'
            ,url: '/admin/chain_wallet/list' //模拟接口
            ,toolbar: true
            ,totalRow: true
            ,cols: [[
                {field: 'id', title: 'id', width: 70}
                ,{field: 'uid', title: 'UID', width: 150}
                ,{field: 'currency_code', title: '币种', width: 100}
                ,{field: 'chain_protocol_code', title: '协议', width: 100}
                ,{field: 'address', title: '地址'}
                ,{field: 'balance', title: '链上余额', width: 200, totalRow: true}
                ,{field: 'operate', fixed: 'right', title: '操作', width: 300, toolbar: '#toolbar'}
            ]]
            ,page: true
            ,limit: 20
            ,height: 'full-220'
            ,limits: [10, 20, 50, 100, 200, 500, 1000]
        });

        //监听工具条
        table.on('tool(list)', function (obj) {
            var data = obj.data;
            if (obj.event === 'refresh') {
                $.get('/admin/chain_wallet/refresh_balance', {id: data.id}, function (res) {
                    layer.msg(res.msg);
                    setTimeout(function(){
                        table.reload('list')
                    },1000)
                });
            } else if (obj.event === 'transfer_fee') {
                layer.confirm('确定要打入手续费吗?', function (index) {
                    var loading = layer.load(1, {time: 30 * 1000});
                    layer.close(index);
                    $.ajax({
                        url: '/admin/chain_wallet/transfer_fee',
                        type: 'get',
                        data: {id: data.id},
                        success: function (res) {
                            layer.msg(res.msg);
                            if (res.code) {
                                parent.layer.close(index);
                            }
                        }, error: function () {
                            layer.msg('网络错误');
                        }, complete: function () {
                            layer.close(loading);
                        }
                    });
                });

            } else if (obj.event === 'collect') {

                layer.confirm('确定要归拢链上余额吗?', function (index) {
                    var loading = layer.load(1, {time: 30 * 1000});
                    layer.close(index);
                    $.get('/admin/chain_wallet/collect', {id: data.id}, function (res) {
                        layer.msg(res.msg);
                        layer.close(loading);
                    });
                });
            }
        });

    });
</script>
</body>
</html>

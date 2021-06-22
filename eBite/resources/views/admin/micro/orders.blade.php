<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理系统 - {{config('app.name')}}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
    <style>
        .order_type_rise {
            /*
            background-color:#89deb3;
            */
            color:#1aab65;
        }
        .order_type_fall {
            /*
            background-color:#d67a7a;
            */
            color:#de1919;
        }
        .order_type {
            /*
            color:#fff;
            */
            font-size: 10px;
            text-align: center;
        }
        .layui-item {
            margin: 10px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <div class="layui-item">
                <div class="layui-inline" style="margin-left: 10px;">
                    <label>交易对</label>
                    <div class="layui-input-inline" style="width: 120px">
                        <select name="match_id" id="currency_match" class="layui-input">
                            <option value="-1">所有</option>
                            @foreach ($currency_matches as $key=> $currency_match)
                                <option value="{{ $currency_match->id }}">{{ $currency_match->symbol }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="layui-inline" style="margin-left: 10px;">
                    <label>支付货币</label>
                    <div class="layui-input-inline" style="width: 90px">
                        <select name="currency_id" id="currency_id" class="layui-input">
                            <option value="-1">所有</option>
                            @foreach ($currencies as $key => $currency)
                                <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-inline" style="margin-left: 10px;">
                    <label>买卖类型</label>
                    <div class="layui-input-inline" style="width: 90px">
                        <select name="type" id="type" class="layui-input">
                            <option value="-1">所有</option>
                            <option value="1">买涨</option>
                            <option value="2">买跌</option>
                        </select>
                    </div>
                </div>
                <div class="layui-inline" style="margin-left: 10px;">
                    <label>交易状态</label>
                    <div class="layui-input-inline" style="width: 90px">
                        <select name="status" id="status" class="layui-input">
                            <option value="-1">所有</option>
                            <option value="1">交易中</option>
                            <option value="2">平仓中</option>
                            <option value="3">已平仓</option>
                        </select>
                    </div>
                </div>
                <!-- <div class="layui-inline" style="margin-left: 10px; display: none;">
                    <label>预设</label>
                    <div class="layui-input-inline" style="width: 90px">
                        <select name="pre_profit_result" id="pre_profit_result" class="layui-input">
                            <option value="-2">所有</option>
                            <option value="-1">亏</option>
                            <option value="0">无</option>
                            <option value="1">盈</option>
                        </select>
                    </div>
                    <button class="layui-btn layui-btn-primary layuiadmin-button-btn" id="btn-set" type="button" style="padding:0px; margin-left: -4px; width: 30px;">
                        <i class="layui-icon layui-icon-set-fill layuiadmin-button-btn"></i>
                    </button>
                </div> -->
                <div class="layui-inline" style="margin-left: 10px;">
                    <label>结果</label>
                    <div class="layui-input-inline" style="width: 90px">
                        <select name="profit_result" id="profit_result" class="layui-input">
                            <option value="-2">所有</option>
                            <option value="-1">亏</option>
                            <option value="0">无</option>
                            <option value="1">盈</option>
                        </select>
                    </div>
                </div>
                <div class="layui-btn-group">
                    <button class="layui-btn layui-btn-primary" id="spread" type="button">
                        <i class="layui-icon layuiadmin-button-btn layui-icon-up"></i>
                        <span>展开</span>
                    </button>
                    <button class="layui-btn" id="btn-search" lay-submit lay-filter="btn-search">搜索</button>
                </div>

            </div>

            <div class="layui-item hidden" id="more">
                <div class="layui-inline" style="margin-left: 10px;">
                    <label>开始日期：</label>
                    <div class="layui-input-inline" style="width:170px;">
                        <input type="text" class="layui-input" id="start_time" value="" name="start_time">
                    </div>
                </div>
                <div class="layui-inline" style="margin-left: 10px;">
                    <label>结束日期：</label>
                    <div class="layui-input-inline" style="width:170px;">
                        <input type="text" class="layui-input" id="end_time" value="" name="end_time">
                    </div>
                </div>
                <div class="layui-inline" style="margin-left: 10px;">
                    <label>uid账号</label>
                    <div class="layui-input-inline">
                        <input type="text" name="account" placeholder="请输入手机号或邮箱" autocomplete="off" class="layui-input"
                               value="">
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
        </div>

        <div class="layui-card-body">
            <table id="order_list" lay-filter="order_list"></table>
        </div>

    </div>
</div>

<script src="/layuiadmin/layui/layui.js"></script>
<script type="text/html" id="barDemo">
    @{{d.status==1 ? '<a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="edit">预设盈亏</a>' : '' }}
</script>

<script type="text/html" id="type_name">
    @{{# if (d.type == 1) { }}
    <div class="order_type order_type_rise">
        <span style="font-size: 13px; font-weight: bold;">@{{d.type_name}}</span><span
            style="font-size: 18px; font-weight: bold;">↑</span>
    </div>
    @{{# } else { }}
    <div class="order_type order_type_fall">
        <span style="font-size: 13px; font-weight: bold;">@{{d.type_name}}</span><span
            style="font-size: 18px; font-weight: bold;">↓</span>
    </div>
    @{{# } }}
</script>

<script type="text/html" id="pre_profit_result_name">
    @{{# if (d.pre_profit_result == 1) { }}
    <span style="color:#89deb3;">@{{d.pre_profit_result_name}}</span>
    @{{# } else if (d.pre_profit_result == -1) { }}
    <span style="color:#d67a7a;">@{{d.pre_profit_result_name}}</span>
    @{{# } else { }}
    <span class="layui-badge-rim">@{{d.pre_profit_result_name}}</span>
    @{{# } }}
</script>

<script type="text/html" id="profit_result_name">
    <span class="layui-badge @{{d.profit_result == 1 ? 'layui-bg-green' : ''}}">@{{d.profit_result_name}}</span>
</script>

<script type="text/html" id="status_name">
    <span class="layui-badge @{{d.status == 1 ? '' : 'layui-bg-gray'}}">@{{d.status_name}}</span>
</script>

<script type="text/html" id="symbol_name">
    <span>@{{d.symbol_name}}</span><span style="color: #848484dd; font-size: 10px;" title="收益率:@{{d.profit_ratio}}%">-@{{d.seconds}}S</span>
</script>

<script type="text/html" id="seconds">
    <div>
        <span title="收益率:@{{d.profit_ratio}}%">@{{d.seconds}}</span>
    </div>
</script>

<script type="text/html" id="fact_profits">
    <div style="text-align: right; margin-right: 10px;">
        @{{# if (d.profit_result == 1) { }}
        <span style="color: #f00; font-weight: bolder;">@{{d.fact_profits}}</span>
        @{{# } else { }}
        <span>@{{d.fact_profits}}</span>
        @{{# } }}
    </div>
</script>

<script>
    window.onload = function () {
        document.onkeydown = function (event) {
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if (e && e.keyCode == 13) { // enter 键
                $('#btn-search').click();
            }
        };
        layui.config({
            base: '/layuiadmin/' //静态资源所在路径
        }).extend({
            index: 'lib/index' //主入口模块
        }).use(['index', 'useradmin', 'table', 'laydate','element'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var table = layui.table;
            var $ = layui.$;
            var form = layui.form;
            var laydate = layui.laydate;
            laydate.render({
                elem: '#start_time',
                type: 'datetime'
            });
            laydate.render({
                elem: '#end_time',
                type: 'datetime'
            });
            form.on('submit(btn-search)', function (data) {
                var option = {
                    where: data.field,
                    page: {curr: 1}
                }
                data_table.reload(option);
                return false;
            });
            $('#spread').click(function () {
                var icon = $(this).find('i');
                var order_list_height = $('div[lay-id=order_list]').height()
                var origin_height = $('div[lay-id=order_list] div.layui-table-box').height()
                if (icon.hasClass('layui-icon-up')) {
                    $('div[lay-id=order_list]').height(order_list_height - 30)
                    $('div[lay-id=order_list] div.layui-table-box').height(origin_height - 30)
                    icon.removeClass('layui-icon-up')
                    icon.addClass('layui-icon-down')
                    $(this).find('span').text("收缩")
                } else {
                    $('div[lay-id=order_list]').height(order_list_height + 30)
                    $('div[lay-id=order_list] div.layui-table-box').height(origin_height + 30)
                    icon.removeClass('layui-icon-down')
                    icon.addClass('layui-icon-up')
                    $(this).find('span').text("展开")
                }
                $('#more').toggle();
            });

            var data_table = table.render({
                elem: '#order_list',
                url: "/admin/micro_order_list",
                done: function (res, curr, count) {
                    $('tr:has(div.order_type_rise)').css('backgroundColor', '#f4fdf8');
                    $('tr:has(div.order_type_fall)').css('backgroundColor', '#fff8f8');
                },
                page: true,
                limit: 20,
                limits: [20, 50, 100, 500, 1000],
                toolbar: true,
                height: 'full-180',
                totalRow: true,
                cols: [[
                    {field: 'id', title: 'ID', width: 100, totalRowText: '小计'}
                    ,{field: 'account', title: 'UID', width: 130, sort: true}
                    ,{field: 'symbol_name', title: '交易对', width: 140, sort: true, templet: '#symbol_name'}
                    ,{field: 'currency_code', title: '币种', width: 80, sort: true}
                    ,{field: 'type_name', title: '类型', width: 80, templet: '#type_name', sort: true}
                    ,{field: 'status_name', title: '交易状态', width: 110, sort: true, templet: '#status_name'}
                    ,{field: 'number', title: '数量', width: 80, totalRow: true}
                    ,{field: 'pre_profit_result_name', title: '预设', width: 90, sort: true}
                    ,{field: 'profit_result_name', title: '结果', width: 90, sort: true, templet: '#profit_result_name'}
                    ,{field: 'fact_profits', title: '盈利', width: 100, sort: true, totalRow: true, templet: '#fact_profits'}
                    ,{field: 'open_price', title: '开仓价', width: 100}
                    ,{field: 'end_price', title: '平仓价', width: 100}
                    ,{field: 'created_at', title: '下单时间', width: 170, sort: true}
                    ,{field: 'updated_at', title: '更新日期', width: 170, sort: true, hide: true}
                    ,{field: 'handled_at', title: '平仓时间', width: 170, sort: true, hide: true}
                    ,{field: 'complete_at', title: '完成时间', width: 170, sort: true, hide: true}
                    ,{fixed: 'right', title: '操作', width: 100, align: 'center', toolbar: '#barDemo'}
                ]]
            });

            //监听工具条
            table.on('tool(order_list)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                if (layEvent === 'edit') { //编辑
                    layer.open({
                        type: 2,
                        title: '编辑交易',
                        area: ['400px', '300px'],
                        content: '/admin/micro_order_edit?id=' + data.id,
                        end: function () {
                            table.reload('list');
                        }
                    });
                }
            });

        });
    }
</script>
</body>
</html>



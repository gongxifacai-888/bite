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
        .layui-form-label {
            width: unset;
        }
        .icon {
            width: 1em;
            height: 1em;
            vertical-align: -0.15em;
            fill: currentColor;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-inline" style="width:80px;">
                            <select name="currency_id" id="status" lay-search>
                                <option value="-1">所有</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->code}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">状态</label>
                        <div class="layui-input-inline" style="width:80px;">
                            <select name="status" id="status">
                                <option value="-1">所有</option>
                                <option value="0">暂停</option>
                                <option value="1">开放</option>
                                <option value="2">下架</option>
                                <option value="3">完成</option>
                                <option value="4">取消</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label" title="商家的发布方向">方向</label>
                        <div class="layui-input-inline" style="width:70px;">
                            <select name="way" id="type">
                                <option value="">所有</option>
                                <option value="BUY">买入</option>
                                <option value="SELL">卖出</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">时间</label>
                        <div class="layui-input-inline" style="width:150px;">
                            <input id="start_time" type="text" class="layui-input layui-date" name="start_time" value="">
                        </div>
                        <div class="layui-form-mid">-</div>
                        <div class="layui-input-inline" style="width:150px;">
                            <input id="end_time" type="text" class="layui-input layui-date" name="end_time" value="">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">UID</label>
                        <div class="layui-input-inline"  style="width:130px;">
                            <input type="text" name="uid" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
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
                        <label class="layui-form-label">商家名称</label>
                        <div class="layui-input-inline"  style="width:130px;">
                            <input type="text" name="seller_name" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="form_submit">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="layui-card-body">
                <table id="data_table" lay-filter="data_table"></table>
            </div>
        </div>
    </div>
</body>
<script src="/layuiadmin/layui/layui.js"></script>
<script src="/assets/admin/iconfont/iconfont.js"></script>
<script type="text/html" id="operateBar">
    <button class="layui-btn layui-btn-xs layui-btn-primary" lay-event="detail">交易记录</button>
    <button class="layui-btn layui-btn-xs layui-btn-danger @{{[3, 4].indexOf(d.status) > -1 ? 'layui-btn-disabled' : ''}}" lay-event="operate" @{{[3, 4].indexOf(d.status) > -1 ? "disabled" : ""}}>操作</button>
</script>
<script type="text/html" id="payways">
    @{{# layui.each(d.payways, function (index, item) { }}
    @{{#    switch (item.code) {
                case 'alipay': }}
                <svg class="icon" aria-hidden="true" title="支付宝">
                    <use xlink:href="#icon-zhifubao"></use>
                </svg>
    @{{#             break; }}
    @{{#         case 'wxpay': }}
                <svg class="icon" aria-hidden="true" title="微信支付">
                    <use xlink:href="#icon-weixinzhifu"></use>
                </svg>
    @{{#              break; }}
    @{{#         case 'bank': }}
                <svg class="icon" aria-hidden="true" title="银行卡 ">
                    <use xlink:href="#icon-yinlian"></use>
                </svg>
    @{{#         break; }}
    @{{#     default: }}
    @{{#  } }}
    @{{# }) }}
</script>
<script>
    layui.use(['element', 'form', 'layer', 'table', 'laydate'], function () {
        var element = layui.element
            ,layer = layui.layer
            ,table = layui.table
            ,$ = layui.$
            ,form = layui.form
            ,laydate = layui.laydate
        $('input.layui-date').each(function () {
            laydate.render({
                elem: this
                ,type: 'datetime'
            });
        });

        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/otc/master_list'
            ,height: 'full-160'
            ,page: true
            ,limit: 20
            ,toolbar: true
            ,totalRow: true
            ,cols: [[
                {field: 'id', title: 'ID', width: 100}
                ,{field: 'seller_name', title: '商家名称', width: 220}
                ,{field: 'user_uid', title: '用户uid', width: 120}
                ,{field: 'way', title: '方向', width: 90}
                ,{field: 'currency_name', title: '币种', width: 90}
                ,{field: 'payways', title: '付款方式', width: 120, templet: '#payways'}
                ,{field: 'status_name', title: '状态', sort: true, width: 100}
                ,{field: 'price', title: '价格', width: 150}
                ,{field: 'total_qty', title: '总数量', width: 150}
                ,{field: 'completed_qty', title: '已完成', width: 150}
                ,{field: 'dealing_qty', title: '交易中', width: 150}
                ,{field: 'remain_qty', title: '剩余', width: 150}
                ,{field: 'min_number', title: '最小交易量', width: 120, hide: true}
                ,{field: 'max_number', title: '最大交易量', width: 120, hide: true}
                ,{field: 'created_at', title: '发布时间', width: 170}
                ,{field: 'canceled_at', title: '取消时间', width: 170, hide: true}
                ,{field: 'payed_at', title: '支付时间', width: 170, hide: true}
                ,{field: 'defered_at', title: '延期时间', width: 170, hide: true}
                ,{field: 'arbitrated_at', title: '维权时间', width: 170, hide: true}
                ,{field: 'finished_at', title: '完成时间', width: 170, hide: true}
                ,{fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#operateBar'}
            ]]
        });
        //监听工具条
        table.on('tool(data_table)', function (obj) {
            var data = obj.data;
            if (obj.event == 'detail') {
                parent.layui.index.openTabsPage('/admin/otc/detail?master_id=' + data.id, "挂单:" + data.id + ',交易记录');
            } else if (obj.event == 'operate') {
                layer.open({
                    type: 2,
                    title: '商家:' + data.seller_name + ',挂单:' + data.id + ',状态:' + data.status_name,
                    skin: 'layui-layer-molv',
                    area: ['600px', '400px'],
                    content: '/admin/otc/master_edit?master_id=' + data.id,
                });
            }
        });
        form.on('submit(form_submit)', function(data) {
            var search_data = data.field
            data_table.reload({
                where: search_data
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
    });
</script>
</html>

@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')


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
                <div class="layui-inline" >
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-inline" style="width:80px;">
                        <select name="status" id="status">
                            <option value="-1">所有</option>
                            <option value="0">交易中</option>
                            <option value="1">已付款</option>
                            <option value="2">延期中</option>
                            <option value="3">椎权中</option>
                            <option value="4">已取消</option>
                            <option value="5">已确认</option>
                        </select>
                    </div>
                </div>
                <div class="layui-inline" >
                    <label class="layui-form-label" title="用户的交易方向">方向</label>
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
                <div class="layui-inline" >
                    <label class="layui-form-label">买方uid</label>
                    <div class="layui-input-inline"  style="width:100px;">
                        <input type="text" name="buy_account" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline" >
                    <label class="layui-form-label">卖方uid</label>
                    <div class="layui-input-inline"  style="width:100px;">
                        <input type="text" name="sell_account" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>
            <div class='layui-form-item'>
                <div class="layui-inline" >
                    <label class="layui-form-label">买方手机号</label>
                    <div class="layui-input-inline"  style="width:100px;">
                        <input type="text" name="sell_mobile" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline" >
                    <label class="layui-form-label">买方邮箱</label>
                    <div class="layui-input-inline"  style="width:100px;">
                        <input type="text" name="buy_email" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline" >
                    <label class="layui-form-label">买方手机号</label>
                    <div class="layui-input-inline"  style="width:100px;">
                        <input type="text" name="buy_mobile" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline" >
                    <label class="layui-form-label">卖方邮箱</label>
                    <div class="layui-input-inline"  style="width:100px;">
                        <input type="text" name="sell_email" placeholder="请输入" autocomplete="off" class="layui-input">
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

        
        <script type="text/html" id="operateBar">
                <a class="layui-btn layui-btn-default layui-btn-xs" lay-event="del">删除</a>
            </script>
    </div>
</div>

@endsection

@section('scripts')

<script type="text/html" id="statusTpl">
    @{{# switch (d.status) {
        case 0: }}
        交易中
    @{{# break;
        case 1: }}
        已付款
    @{{# break;
        case 2: }}
        延期中
    @{{# case 3: }}
        椎权中
    @{{# break;
        case 4: }}
        已取消
    @{{# break;
        case 5: }}
        已确认
    @{{#  } }}
</script>
<script>
    layui.use(['index','element', 'form','table', 'layer', 'laydate'], function () {
        var $ = layui.$
            ,element = layui.element
            ,layer = layui.layer
            , table = layui.table
            , laydate = layui.laydate
            , form = layui.form
            , admin = layui.admin


       




            $('input.layui-date').each(function () {
        laydate.render({
            elem: this
            ,type: 'datetime'
        });
    });

    var data_table = table.render({
        elem: '#data_table'
        ,url: '/agent/order/otc_list'
        ,height: 'full-160'
        ,page: true
        ,limit: 10
        ,toolbar: true
        ,totalRow: true
        ,cols: [[
            {field: 'id', title: 'ID', width: 100}
            ,{field: 'currency_name', title: '币种', width: 90}
            ,{field: 'master_id', title: '挂单id', width: 100}
            ,{field: 'way', title: '交易方向', width: 140}
            ,{field: 'buy_user_account', title: '购买用户', width: 120}
            ,{field: 'sell_user_account', title: '卖出用户', width: 120}
            ,{field: 'status', title: '状态', sort: true, width: 100, templet: '#statusTpl'}
            ,{field: 'price', title: '价格', width: 170}
            ,{field: 'number', title: '数量', width: 170}
            ,{field: 'amount', title: '金额', width: 170}
            ,{field: 'created_at', title: '创建时间', width: 170}
            ,{field: 'canceled_at', title: '取消时间', width: 170, hide: true}
            ,{field: 'payed_at', title: '支付时间', width: 170, hide: true}
            ,{field: 'arbitrated_at', title: '仲裁时间', width: 170, hide: true}
            ,{field: 'defered_at', title: '延期时间', width: 170, hide: true}
            ,{field: 'finished_at', title: '完成时间', width: 170, hide: true}
            // ,{fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#operateBar'}
        ]]
    });
    table.on('tool(data_table)', function(obj) {
        var event = obj.event
            ,data = obj.data
           
        switch (event) {
            case "operate":
                layer.open({
                    type: 2,
                    skin: "layui-layer-lan",
                    title: '交易操作',
                    area: ['500px', '280px'],
                    content: '/admin/otc/detail_edit?detail_id=' + data.id,
                });
                break;
            case "del":
                layer.confirm('危险操作!真的删除么?', function (index) {
                    $.get('/agetn/order/otc_list_del', {
                        id: data.id
                    }, function (res) {
                        layer.msg(res.msg);
                        layer.close(index);
                        if (res.code) {
                            obj.del();
                        }
                    });
                });
                break;
            default:
                break;
        }
    })

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

@endsection
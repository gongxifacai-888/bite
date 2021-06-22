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
                <input type="hidden" name="detail_id" value="{{Request::input('detail_id', 0)}}">
                <div class="layui-form-item">

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
layui.config({
    base: '/layuiadmin/' //静态资源所在路径
}).extend({
    index: 'lib/index' //主入口模块
}).use(['index', 'element', 'form', 'layer', 'table', 'laydate'], function () {
    var element = layui.element
        ,layer = layui.layer
        ,table = layui.table
        ,$ = layui.$
        ,form = layui.form
        ,laydate = layui.laydate
    var detail_id = $('input[name=detail_id]').val();
    var data_table = table.render({
        elem: '#data_table'
        ,url: '/admin/otc/action_lists'
        ,height: 'full-160'
        ,page: true
        ,limit: 20
        ,toolbar: true
        ,totalRow: true
        ,cols: [[
            {field: 'id', title: 'ID', width: 100}
            ,{field: 'user_account', title: '触发用户', width: 120}
            ,{field: 'sell_user_account', title: '卖方', width: 120}
            ,{field: 'buy_user_account', title: '买方', width: 120}
            ,{field: 'status', title: '状态', width: 150,templet: '#statusTpl'}
            // ,{field: 'operator_type', title: '操作者', width: 100, templet: '#operator_tpl'}
            // ,{field: 'to_buy_msg', title: '买方消息', width: 100}
            // ,{field: 'to_sell_msg', title: '卖方消息', width: 100}
            ,{field: 'memo', title: '说明', width: 300}
            ,{field: 'created_at', title: '添加时间', width: 170}
            ,{field: 'updated_at', title: '修改时间', width: 170}

        ]]
        ,where: {
            detail_id: detail_id
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

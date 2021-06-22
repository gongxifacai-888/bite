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
                <input type="hidden" name="seller_id" value="{{Request::input('seller_id', 0)}}">
                <div class="layui-form-item">
                    <button class="layui-btn" id="currency_add">
                        <i class="layui-icon layui-icon-add-1 layuiadmin-button-btn"></i>添加币种
                    </button>
                    <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-inline" style="width:80px;">
                            <select name="currency_id" id="currency">
                                <option value="-1">所有</option>
                                @foreach ($currencies as $currency)
                                <option value="{{$currency->id}}">{{$currency->code}}</option>
                                @endforeach
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
<script type="text/html" id="operateBar">
    <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="edit"">编辑</a>
    <!-- <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del"">删除</a> -->
</script>
<script type="text/html" id="buy_status_tpl">
    <input type="checkbox" name="status" lay-skin="switch" lay-text="开启|关闭" @{{d.buy_status == 1 ? "checked" : ""}} disabled>
</script>
<script type="text/html" id="sell_status_tpl">
    <input type="checkbox" name="status" lay-skin="switch" lay-text="开启|关闭" @{{d.sell_status == 1 ? "checked" : ""}} disabled>
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
    var seller_id = $('input[name=seller_id]').val();
    var data_table = table.render({
        elem: '#data_table'
        ,url: '/admin/otc/seller_currency_list'
        ,height: 'full-160'
        ,page: true
        ,limit: 20
        ,toolbar: true
        ,totalRow: true
        ,cols: [[
            {field: 'id', title: 'ID', width: 100}
            ,{field: 'seller_name', title: '商家名称', width: 170, hide: true}
            ,{field: 'currency_code', title: '币种名称', width: 100}
            ,{field: 'completed_buy', title: '挂买完成', width: 150, hide: true}
            ,{field: 'completed_sell', title: '挂卖完成', width: 150, hide: true}
            ,{field: 'buy_status', title: '挂买权限', width: 100, templet: '#buy_status_tpl'}
            ,{field: 'sell_status', title: '挂卖权限', width: 100, templet: '#sell_status_tpl'}
            ,{field: 'created_at', title: '添加时间', width: 170}
            ,{field: 'updated_at', title: '修改时间', width: 170}
            ,{fixed: 'right', field: '', title: '操作', width: 200, templet: '#operateBar'}
        ]]
        ,where: {
            seller_id: seller_id
        }
    });
    
    $('#currency_add').click(function () {
        layer.open({
            title: "商家添加币种"
            ,type: 2
            ,content: "/admin/otc/seller_currency_edit?seller_id=" + seller_id
            ,area: ["600px", "400px"]
        });
    });
    //监听工具条
    table.on('tool(data_table)', function (obj) {
        var data = obj.data;
        if (obj.event === 'edit') {
            layer.open({
                title: "商家编辑币种"
                ,type: 2
                ,content: "/admin/otc/seller_currency_edit?id=" + data.id
                ,area: ["600px", "400px"]
            });
        } else if (obj.event == 'del') {
            layer.confirm("不建议进行删除,建议关闭该币种的挂买和挂卖权限替代此操作,真的要删除商家的币种吗?", function (index) {
                $.ajax({
                    url: ''
                    ,type: 'POST'
                    ,data: {id: data.id}
                    ,success: function () {

                    }
                });
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

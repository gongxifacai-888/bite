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
                    <button class="layui-btn" lay-href="/admin/otc/seller_edit">
                        <i class="layui-icon layui-icon-add-1 layuiadmin-button-btn"></i>添加商家
                    </button>
                    <button class="layui-btn" lay-href="/admin/otc/seller_currency_apply">
                        <i class="layui-icon layuiadmin-button-btn"></i>审核商家开通币种
                    </button>
                    <div class="layui-inline">
                        <label class="layui-form-label">状态</label>
                        <div class="layui-input-inline" style="width:80px;">
                            <select name="status" id="status">
                                <option value="-1">所有</option>
                                <option value="0">禁用</option>
                                <option value="1">启用</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">商家名称</label>
                        <div class="layui-input-inline"  style="width:130px;">
                            <input type="text" name="seller_name" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">UID</label>
                        <div class="layui-input-inline"  style="width:130px;">
                            <input type="text" name="uid" placeholder="请输入" autocomplete="off" class="layui-input">
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
    <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-xs ayui-btn-normal" lay-event="grant"">权限</a>
</script>
<script type="text/html" id="status_tpl">
    <input type="checkbox" name="status" lay-skin="switch" lay-text="开启|关闭" @{{d.status == 1 ? "checked" : ""}} disabled>
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

    $('input.layui-date').each(function () {
        laydate.render({
            elem: this
            ,type: 'datetime'
        });
    });

    var data_table = table.render({
        elem: '#data_table'
        ,url: '/admin/otc/seller_list'
        ,height: 'full-160'
        ,page: true
        ,limit: 20
        ,toolbar: true
        ,totalRow: true
        ,cols: [[
            {field: 'id', title: 'ID', width: 100}
            ,{field: 'name', title: '商家名称', width: 220}
            ,{field: 'uid', title: 'uid', hide: true}
            ,{field: 'mobile', title: '电话', width: 120}
            ,{field: 'email', title: '邮箱', width: 200}
            ,{field: 'status', title: '状态', width: 100, templet: '#status_tpl'}
            ,{field: 'created_at', title: '添加时间', width: 170}
            ,{field: 'updated_at', title: '修改时间', width: 170}
            ,{fixed: 'right', field: '', title: '操作', width: 200, templet: '#operateBar'}
        ]]
    });
    //监听工具条
    table.on('tool(data_table)', function (obj) {
        var data = obj.data;
        if (obj.event == 'edit') {
            parent.layui.index.openTabsPage("/admin/otc/seller_edit?id=" + data.id, "编辑商家");
        } else if (obj.event == 'grant') {
            parent.layui.index.openTabsPage("/admin/otc/seller_currency?seller_id=" + data.id, "商家权限");
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

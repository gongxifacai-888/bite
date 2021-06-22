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
    <script src="/layuiadmin/layui/layui.js"></script>
</head>

<body>
    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                <div class="layui-item">
                    <div class="layui-inline" style="margin-left: 10px;">
                        <label>用户账号</label>
                        <div class="layui-input-inline">
                            <input type="text" name="account" placeholder="请输入手机号或邮箱" autocomplete="off" class="layui-input" value="">
                        </div>
                    </div>
                    <div class="layui-inline" style="margin-left: 10px;">
                        <label>风控类型</label>
                        <div class="layui-input-inline" style="width: 90px">
                            <select name="risk" lay-verify="required" id="risk">
                                <option value="-2">全部</option>
                                <option value="0">无</option>
                                <option value="-1">亏损</option>
                                <option value="1">盈利</option>
                            </select>
                        </div>
                        <button class="layui-btn layui-btn-primary" id="btn-set" type="button" style="padding:0px; margin-left: -4px; width: 30px;">
                            <i class="layui-icon layuiadmin-button-btn layui-icon-set-fill"></i>
                        </button>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-btn-group">
                        <button class="layui-btn btn-search" id="form_submit" lay-submit lay-filter="form_submit">
                            <i class="layui-icon layuiadmin-button-btn layui-icon-search"></i>
                        </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-card-body">
                <table id="userlist" lay-filter="userlist"></table>
            </div>
        </div>
    </div>
</body>

<script type="text/html" id="switchTpl">
    <input type="checkbox" name="status" disabled="disabled" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="risk_name">
    @{{# if (d.risk == 1) { }}
        <span style="color:#89deb3;">@{{d.risk_name}}</span>
    @{{# } else if (d.risk == -1) { }}
        <span style="color:#d67a7a;">@{{d.risk_name}}</span>
    @{{# } else { }}
        <span class="layui-badge-rim">@{{d.risk_name}}</span>
    @{{# } }}
</script>
<script>        
    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['element', 'form', 'layer', 'table'], function () {
        var element = layui.element
            ,layer = layui.layer
            ,table = layui.table
            ,$ = layui.$
            ,form = layui.form
        var user_table = table.render({
            elem: '#userlist'
            ,toolbar: true
            ,url: '/admin/micro/users/list'
            ,page: true
            ,limit: 100
            ,limits: [20, 50, 100, 200, 500, 1000]
            ,height: 'full-120'
            ,cols: [[
                {field: '', type: 'checkbox'}
                ,{field: 'id', title: 'ID', width: 100}
                ,{field: 'uid', title: 'uid', width: 150}
                ,{field: 'mobile', title: '手机号', width: 150}
                ,{field: 'email', title: '邮件', width: 220}
                ,{field: 'risk_name', title: '风控类型', width: 90, templet: '#risk_name'}
                ,{field: 'last_login_at', title: '上次登录', width: 170}
                ,{field:'created_at', title: '注册时间', width: 170} 
            ]]
        });

        form.on('submit(form_submit)', function(obj) {
            user_table.reload({
                where: obj.field,
                page: {curr: 1}
            });
            return false;
        });

        $('#btn-set').click(function () {
            var checkStatus = table.checkStatus('userlist');
            var risk = $('#risk').val();
            var ids = [];
            try {
                if (checkStatus.data.length <= 0) {
                    throw '请先选择用户';
                }
                if (risk <= -2) {
                    throw '请选择风控类型';
                }
                checkStatus.data.forEach(function (item, index, arr) {
                    ids.push(item.id);
                });
                $.ajax({
                    url: '/admin/micro/users/batch_risk'
                    ,type: 'POST'
                    ,data: {risk: risk, ids: ids}
                    ,success: function (res) {
                        layer.msg(res.msg, {
                            time: 2000,
                            end: function () {
                                if (res.code == 1) {
                                    user_table.reload();
                                }
                            }
                        });
                    }
                    ,error: function (res) {
                        layer.msg('网络错误');
                    }
                })
            } catch (error) {
                layer.msg(error);
            }
        });
    });
</script>
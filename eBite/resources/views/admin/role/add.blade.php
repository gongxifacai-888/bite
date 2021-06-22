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
        <div class="layui-card-header">添加角色</div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <div class="layui-form-item">
                    <label class="layui-form-label">名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" lay-verify="name" placeholder="请输入名称"
                               class="layui-input">
                    </div>
                </div>
                @foreach($module_type_list as $moduleType)
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{$moduleType->name}}</label>
                        <div class="layui-input-block">
                            @foreach($moduleType->module as $module)
                                <input type="checkbox" name="module_id[{{$module->id}}]" value="{{$module->id}}"
                                       title="{{$module->name}}">
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit>立即提交</button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script src="/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'form', 'upload'], function () {
        var $ = layui.$
            , form = layui.form
            , upload = layui.upload;

        form.on('submit', function (data) {
            $.post('/admin/role/save', data.field, function (res) {
                layer.msg(res.msg);

                if (res.code) {
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            });

            return false;
        })

    })
</script>
</body>
</html>

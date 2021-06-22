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
        <div class="layui-card-header">添加</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <div class="layui-form-item">
                    <label class="layui-form-label">Wgt下载地址</label>
                    <div class="layui-input-block">
                        <input type="text" name="wgt_url" required class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">Pkg下载地址</label>
                    <div class="layui-input-block">
                        <input type="text" name="pkg_url" class="layui-input" required>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">下载页面</label>
                    <div class="layui-input-block">
                        <input type="text" name="download_url" class="layui-input" required>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">版本号</label>
                    <div class="layui-input-block">
                        <input type="text" name="version_number" class="layui-input" required>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">平台类型</label>
                    <div class="layui-input-block">
                        <select name="type" id="">
                            <option value="1">Android</option>
                            <option value="2">Ios</option>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit>立即提交</button>

                    </div>

                </div>
            </form>
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
            , form = layui.form;
        form.on('submit', function (data) {
            console.log(data);
            $.post('/admin/app_version/save', data.field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    setTimeout(function () {
                        location.reload()
                    }, 500)
                }
            });
            return false;
        })
    })
</script>
</body>
</html>

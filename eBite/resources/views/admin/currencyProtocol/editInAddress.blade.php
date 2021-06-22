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
            <blockquote class="layui-elem-quote">温馨提示,为了安全,入金总账号和出金总账号尽量不要设置成同一个</blockquote>
        </div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$currencyProtocol->id}}">

                <div class="layui-form-item">
                    <label class="layui-form-label">总账号</label>
                    <div class="layui-input-block">
                        <input type="text" name="in_address" placeholder="请输入入金总账号"
                               value="{{$currencyProtocol->in_address}}"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">验证码</label>
                    <div class="layui-input-block">
                        <input type="text" name="code" placeholder="请输入验证码,验证码点击页面右上角发送" class="layui-input">
                    </div>
                </div>

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
            $.post('/admin/currency_protocol/update_in_address', data.field, function (res) {
                layer.msg(res.msg);
                setTimeout(function () {
                    location.reload()
                }, 1000)
            });

        })

    })
</script>
</body>
</html>

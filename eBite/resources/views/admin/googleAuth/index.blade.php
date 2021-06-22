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

    <style>
        .qr-code{
            margin-top: 50px;
            padding-bottom: 50px;
            text-align: center;
            display: none;
            line-height: 2;
        }
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">绑定谷歌验证器</div>

        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <blockquote class="layui-elem-quote">请注意,点击立即提交后,老验证码立即失效,需要立即扫描新二维码进行绑定</blockquote>
            <blockquote class="layui-elem-quote">请输入密码</blockquote>
        </div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <div class="layui-form-item">
                    <label class="layui-form-label">账号</label>
                    <div class="layui-input-block">
                        <input type="text" value="{{$admin->username}}" disabled class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="password" lay-verify="password" placeholder="请输入密码" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit>立即提交</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="qr-code">
            <img width="200px" src="/images/logo.jpg" alt="">
            <p>使用谷歌验证器扫描上方二维码完成绑定</p>
            <p>如果看不见二维码,请科学上网,或者手动输入下方密钥</p>
            <p id="secret"></p>
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

            $.post('/admin/google_auth/qr_code', data.field, function (res) {
                if (res.code) {
                    $('.qr-code').show();
                    $('#secret').text(res.data.secret);
                    $('.qr-code img').attr('src',res.data.qr_code_url);
                    return;
                }
                layer.msg(res.msg);

            });

        })

    })
</script>
</body>
</html>

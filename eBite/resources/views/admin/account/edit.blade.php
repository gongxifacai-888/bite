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
        <div class="layui-card-header">调节余额</div>
        <div class="layui-card-body">
            <div class="layui-form" action="">
                <input type="hidden" name="account_id" value="{{$account->id}}">
                <input type="hidden" name="account_type_id" value="{{$account->accountType->id}}">
                <div class="layui-form-item">
                    <label class="layui-form-label">用户UID</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input layui-disabled" value="{{$account->user->uid}}"
                               disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input layui-disabled" value="{{$account->currency_code}}"
                               disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">账户</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input layui-disabled" value="{{$account->accountType->name}}"
                               disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">调节账户</label>
                    <div class="layui-input-block">
                        <input type="radio" name="method" value="changeBalance" title="可用余额" checked>
                        <input type="radio" name="method" value="changeLockBalance" title="锁定余额">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">调节值</label>
                    <div class="layui-input-block">
                        <input type="text" name="number" required value="0" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">备注信息</label>
                    <div class="layui-input-block">
                        <textarea name="memo" placeholder="请输入内容" class="layui-textarea"></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">谷歌验证器验证码</label>
                    <div class="layui-input-block">
                        <input type="text" name="auth_code" required class="layui-input">
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

<script type="text/javascript">


    layui.use(['form', 'upload', 'layer'], function () {
        var layer = layui.layer;
        var form = layui.form;
        var $ = layui.$;

        form.on('submit()', function (data) {
            data = data.field;
            var loading = layer.load(1, {time: 30 * 1000});
            $.post('/admin/account/update', data, function (res) {
                layer.msg(res.msg);
                layer.close(loading);
                if(res.code){
                    setTimeout(function(){
                        location.reload()
                    },1000)
                }
            });
        });
    });


</script>
</body>
</html>

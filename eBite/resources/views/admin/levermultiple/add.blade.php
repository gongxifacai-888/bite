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
        <div class="layui-card-header">添加手数/倍数</div>
        <div class="layui-card-body">
            <form class="layui-form col-lg-5">
                <div class="layui-form-item">
                    <label class="layui-form-label">值</label>
                    <div class="layui-input-block">
                        <input type="text" name="value" autocomplete="off" class="layui-input" value="" placeholder="">
                    </div>
                </div>
                {{--<div class="layui-form-item">--}}
                {{--<label class="layui-form-label">初始密码</label>--}}
                {{--<div class="layui-input-block">--}}
                {{--<input type="password" name="password" autocomplete="off" class="layui-input" value="" placeholder="">--}}
                {{--</div>--}}
                {{--</div>--}}
                <div class="layui-form-item">
                    <label class="layui-form-label">类型</label>
                    <div class="layui-input-block">
                        <select name="type" lay-filter="role_id">
                            <option value="">请选择</option>

                            <option value="1" selected>倍数</option>
                            <option value="2">手数</option>

                        </select>
                    </div>
                </div>
                {{--<input type="hidden" name="id" value="{{$admin_user['id']}}">--}}
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit lay-filter="adminuser_submit">立即提交</button>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/layuiadmin/layui/layui.js"></script>

<script type="text/javascript">

    layui.use(['form', 'upload', 'layer'], function () {
        var layer = layui.layer;
        var form = layui.form;
        var $ = layui.$;
        form.on('submit(adminuser_submit)', function (data) {
            var data = data.field;
            $.ajax({
                url: '/admin/lever_multiple/save',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (res) {
                    layer.msg(res.msg);
                    setTimeout(function () {
                        if (res.code == 1) {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        } else {
                            return false;
                        }
                    }, 1000);

                }
            });
            return false;
        });

    });


</script>
</body>
</html>

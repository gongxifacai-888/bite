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
            <div class="layui-card-header">编辑</div>
            <div class="layui-card-body">
                <form class="layui-form" action="">
                    <div class="layui-form-item">
                        <label class="layui-form-label">值</label>
                        <div class="layui-input-block">
                            <input type="text" name="value" autocomplete="off" placeholder="" class="layui-input" value="{{$result->value}}" >
                        </div>
                    </div>

                    <input type="hidden" name="id" value="{{$result->id}}">
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="/layuiadmin/layui/layui.js"></script>

<script>

    layui.use(['form', 'laydate'], function () {
        var form = layui.form
            , $ = layui.jquery
            , laydate = layui.laydate
            , index = parent.layer.getFrameIndex(window.name);
        //监听提交
        form.on('submit(demo1)', function (data) {
            var data = data.field;
            $.ajax({
                url: '{{url('admin/lever_multiple/update')}}'
                , type: 'post'
                , dataType: 'json'
                , data: data
                , success: function (res) {
                    layer.msg(res.msg);
                    if (res.code) {
                        setTimeout(function () {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }, 1000);
                    }
                }
            });
            return false;
        });
    });
</script>
</body>
</html>

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

<body class="layui-layout-body">
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">发送{{$lever_trade->symbol}}行情</div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <div class="layui-form-item">
                    <input type="hidden" name="id" value="{{Request::get('id')}}">
                    <label for="price" class="layui-form-label">价格</label>
                    <div class="layui-input-block">
                        <input type="text" class="layui-input" name="update_price" placeholder="请输入价格" value="" >
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit lay-filter="form">确定</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="/layuiadmin/layui/layui.js"></script>

<script>
    layui.use(['form', 'layer'], function () {
        var form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
        form.on('submit(form)', function (data) {
            console.log(data);
            if (data.field.update_price <= 0 || data.field.update_price == '' || data.field.update_price == null || data.field.update_price == undefined) {
                layer.msg('价格必须大于0');
                return false;
            }
            layer.confirm('您确定要发送价格吗?', {
                title: '操作确认'
            }, function (index) {
                $.ajax({
                    url: '/admin/hazard_rate/handle',
                    type: 'post',
                    data: data.field,
                    success: function (res) {
                        console.log(res);
                        layer.msg(res.msg, {
                            time: 2000
                            ,end: function () {
                                if (res.code == 1) {
                                    // var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    // parent.layer.close(index); //再执行关闭
                                    // parent.layui.table.reload('data_table');
                                }
                            }
                        });
                    },
                    error: function (res) {
                        layer.msg('网络错误,请稍后重试');
                    }
                });
            });
        });
    });
</script>
</body>

</html>
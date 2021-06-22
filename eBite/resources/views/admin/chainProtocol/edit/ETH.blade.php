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
        .layui-timeline-title {
            margin-bottom: 15px;
        }

        p {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">编辑</div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$chainProtocol->id}}">
                <div class="layui-timeline-content layui-text">
                    <div class="layui-form-item">
                        <label class="layui-form-label">Gas Price</label>
                        <div class="layui-input-block">
                            <input type="number" name="data[gas_price]" class="layui-input"  placeholder="单位:Gway"
                                   value="{{$chainProtocol->data['gas_price'] ?? 0}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">Gas Limit</label>
                        <div class="layui-input-block">
                            <input type="number" name="data[gas_limit]" class="layui-input"
                                   value="{{$chainProtocol->data['gas_limit'] ?? 0}}">
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

                $.post('/admin/chain_protocol/update', data.field, function (res) {
                    layer.msg(res.msg);

                    if (res.code) {
                        setTimeout(function () {
                            location.reload()
                        }, 1000);
                    }
                });
            })

        })
    </script>
</body>
</html>

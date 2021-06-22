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
        <div class="layui-card-body">

            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">

                <div class="layui-form-item">
                    <label class="layui-form-label">预设</label>
                    <div class="layui-input-block">
                        <select name="risk" lay-verify="required" lay-filter="risk_mode">
                            <option value="0" {{ ($result->pre_profit_result ?? 0) == 0 ? 'selected' : '' }} >无</option>
                            <option value="1" {{ ($result->pre_profit_result ?? 0) == 1 ? 'selected' : '' }} >盈利</option>
                            <option value="-1" {{ ($result->pre_profit_result ?? 0) == -1 ? 'selected' : '' }} >亏损</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="id" value="{{$result->id}}">
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
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

            $.post('{{url('admin/micro_order_edit')}}', data.field, function (res) {

                layer.msg(res.msg);
                if (res.code) {
                    setTimeout(function(){
                        location.reload()
                    },1000)
                }
            });

        })

    })
</script>
</body>
</html>

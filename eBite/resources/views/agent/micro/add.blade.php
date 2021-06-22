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
        <div class="layui-card-header">添加极速合约下单数量</div>
        <div class="layui-card-body">

            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <div class="layui-form-item">
                    <label for="currency_id" class="layui-form-label">币种</label>
                    <div class="layui-input-block">
                        <select name="currency_id" lay-verify="required" lay-search>
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                        @if ( $result->currency_id == $currency->id)) selected @endif>{{ $currency->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">数量</label>
                    <div class="layui-input-block">
                        <input type="text" name="number" lay-verify="required" autocomplete="off" placeholder=""
                               class="layui-input" value="{{$result->number}}">
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

            $.post('{{url('admin/micro_number_add')}}', data.field, function (res) {

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

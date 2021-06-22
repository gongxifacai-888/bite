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
            <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                <blockquote class="layui-elem-quote">温馨提醒:机器人的价格会在设置价格的+-5%内</blockquote>
                <blockquote class="layui-elem-quote">温馨提醒:模拟火币交易对应选择与代币价格相近的交易对</blockquote>
            </div>
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$robot->id}}">
                <div class="layui-timeline-content layui-text">
                    <div class="layui-form-item">
                        <label class="layui-form-label">价格</label>
                        <div class="layui-input-block">
                            <input type="text" name="price" class="layui-input" value="{{$robot->price}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">交易对</label>
                        <div class="layui-input-block">
                            <select name="currency_match_id" id="">
                                @foreach($currency_matches as $currencyMatch)
                                    <option value="{{$currencyMatch->id}}"
                                            @if($robot->currency_match_id==$currencyMatch->id) selected @endif>{{$currencyMatch->symbol}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">模拟火币交易对</label>
                        <div class="layui-input-block">
                            <input type="text" name="virtual_symbol" class="layui-input"
                                   value="{{$robot->virtual_symbol}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">价格小数位数</label>
                        <div class="layui-input-block">
                            <input type="text" name="decimal" class="layui-input"
                                   value="{{$robot->decimal}}">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">是否开启</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" value="0" title="关闭"
                                   @if($robot->status==0) checked @endif>
                            <input type="radio" name="status" value="1" title="开启"
                                   @if($robot->status==1) checked @endif>
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
            , form = layui.form
            , upload = layui.upload;


        form.on('submit', function (data) {

            $.post('/admin/robot/update', data.field, function (res) {
                layer.msg(res.msg);

                if (res.code) {
                    setTimeout(function () {
                        location.reload()
                    }, 1000);
                }
            });

            return false;
        })

    })
</script>
</body>
</html>

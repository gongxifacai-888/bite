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
        <div class="layui-card-header">添加币种协议</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">

                <div class="layui-form-item">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-block">
                        <select name="currency_id" id="">
                            @foreach($currencies as $currency)
                                <option value="{{$currency->id}}">{{$currency->code}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">区块链协议</label>
                    <div class="layui-input-block">
                        <select name="chain_protocol_id" id="">
                            @foreach($chain_protocols as $chainProtocol)
                                <option value="{{$chainProtocol->id}}">{{$chainProtocol->code}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">小数位数</label>
                    <div class="layui-input-block">
                        <input type="text" name="decimal" placeholder="请输入小数位数" value="0" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">代币地址</label>
                    <div class="layui-input-block">
                        <input type="text" name="token_address" placeholder="请输入代币地址" class="layui-input">
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

            $.post('/admin/currency_protocol/save', data.field, function (res) {
                layer.msg(res.msg);
                setTimeout(function () {
                    location.reload()
                }, 1000)
            });

            return false;
        })

    })
</script>
</body>
</html>

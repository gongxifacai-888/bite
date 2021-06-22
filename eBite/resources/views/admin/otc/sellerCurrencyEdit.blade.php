<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理系统 - {{config('app.name')}}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
    <style>
        .layui-form-label {
            width: unset;
        }
        .icon {
            width: 1em;
            height: 1em;
            vertical-align: -0.15em;
            fill: currentColor;
            overflow: hidden;
        }
    </style>
</head>
<body>

    <div class="layui-fluid">
        <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form">
            <input type="hidden" name="id" value="{{$seller_currency->id ?? 0}}">
            <div class="layui-form-item">
                <label class="layui-form-label">所属商家</label>
                <div class="layui-inline">
                    <select name="seller_id" lay-verify="required" {{$seller_currency->id > 0 ? 'disabled="disabled"' : ''}}>
                        <option value="">请选择</option>
                        @foreach ($sellers as $seller)
                        <option value="{{$seller->id}}" {{$seller_currency->seller_id == $seller->id || Request::input('seller_id', 0) == $seller->id ? 'selected="selected"' : ''}}>{{$seller->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">选择币种</label>
                <div class="layui-inline">
                    <select name="currency_id" lay-verify="required" {{$seller_currency->id > 0 ? 'disabled="disabled"' : ''}}>
                        <option value="">请选择</option>
                        @foreach ($currencies as $currency)
                        <option value="{{$currency->id}}" {{$seller_currency->currency_id == $currency->id ? 'selected="selected"' : ''}}>{{$currency->code}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">挂买权限</label>
                <div class="layui-input-block">
                    <input type="radio" name="buy_status" value="0" title="关闭" @if ($seller_currency->buy_status == 0) checked @endif>
                    <input type="radio" name="buy_status" value="1" title="开启" @if ($seller_currency->buy_status == 1) checked @endif>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">挂卖权限</label>
                <div class="layui-input-block">
                    <input type="radio" name="sell_status" value="0" title="关闭" @if ($seller_currency->sell_status == 0) checked @endif>
                    <input type="radio" name="sell_status" value="1" title="开启" @if ($seller_currency->sell_status == 1) checked @endif>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="form_submit">确认操作</button>
                </div>
            </div>
        </form>
    </div>

</body>
<script src="/layuiadmin/layui/layui.js"></script>
<script type="text/html" id="operateBar">
    <a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="detail">交易记录</a>
    <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="operate">操作</a>
</script>
<script>
    layui.use(['element', 'form', 'layer'], function () {
        var element = layui.element
            ,layer = layui.layer
            ,$ = layui.$
            ,form = layui.form
        form.on('submit(form_submit)', function(data) {
            var submit_data = data.field
            $.ajax({
                url: ''
                ,method: 'POST'
                ,data: submit_data
                ,success: function (res) {
                    if (res.code == 1) {
                        layer.msg("操作成功", {
                            end: function () {
                                var p_index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(p_index);
                                parent.layui.table.reload('data_table');
                            }
                        })
                    } else {
                        layer.msg(res.msg)
                    }
                }
                ,error: function () {
                    layer.msg("服务器出错")
                }
            });
            return false;
        });
    });
</script>
</html>

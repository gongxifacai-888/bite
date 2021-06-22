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
            <input type="hidden" name="detail_id" value="{{$otc_detail->id}}">
            <div class="layui-form-item" style="margin: 20px;  color: #ea6c3a;">
                <h3>操作提示:该操作不可撤销,请谨慎操作</h3>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">请选择操作</label>
                <div class="layui-inline">
                    <select name="operate_status" lay-verify="required">
                        <option value="">请选择</option>
                        <option value="4" {{in_array($otc_detail->status, [4, 5]) ? "disabled" : ""}}>取消</option>
                        <option value="5" {{in_array($otc_detail->status, [4, 5]) ? "disabled" : ""}}>完成</option>
                    </select>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn" lay-submit lay-filter="form_submit">确认操作</button>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">状态说明:</label>
                <div class="layui-input-block">
                    <ul>
                        <li>取消:取消双方交易,退回卖方冻结资金和商家挂单</li>
                        <li>完成:确认卖方收款,向买方放币,交易完成</li>
                    </ul>
                </div>
            </div>
        </form>
    </div>

</body>
<script src="/layuiadmin/layui/layui.js"></script>
<script>
    layui.use(['element', 'form', 'layer'], function () {
        var element = layui.element
            ,layer = layui.layer
            ,$ = layui.$
            ,form = layui.form
        form.on('submit(form_submit)', function(data) {
            var submit_data = data.field
            layer.confirm("真的确认要对交易执行操作?", function (index) {
                layer.close(index);
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
            });
            return false;
        });
    });
</script>
</html>

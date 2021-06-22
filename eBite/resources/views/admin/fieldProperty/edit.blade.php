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
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$info->id}}">
                <div class="layui-form-item">
                    <label class="layui-form-label">适用名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="property_name" lay-verify="property_name" placeholder="请输入属性名称"
                               class="layui-input" value="{{$info->property_name}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">属性标签</label>
                    <div class="layui-input-block">
                        <input type="text" name="apply_to_label" lay-verify="apply_to_label" placeholder="请输入标签名称,没有限制留空"
                               class="layui-input" value="{{$info->apply_to_label}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">适用类型</label>
                    <div class="layui-input-block">
                        <textarea name="apply_to_type" class="layui-textarea" placeholder="多个类型用英文状态 , 号分割,没有限制留空">{{$info->apply_to_type}}</textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">属性说明</label>
                    <div class="layui-input-block">
                        <input type="text" name="explain" lay-verify="explain" placeholder="请输入属性说明"
                               class="layui-input"  value="{{$info->explain}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit>立即提交</button>

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

            $.post('/admin/field_property/update', data.field, function (res) {
                layer.msg(res.msg);
                setTimeout(function(){
                    if (res.code) {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layui.table.reload('list');
                        parent.layer.close(index);
                    }
                },1000);

            });
            return false;
        })

    })
</script>
</body>
</html>

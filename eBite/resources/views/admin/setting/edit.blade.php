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
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value='{{$info->id}}'>
                <div class="layui-form-item">
                    <label class="layui-form-label">键</label>
                    <div class="layui-input-block">
                        <input type="text" disabled class="layui-input" value='{{$info->key}}'>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">值</label>
                    <div class="layui-input-block">
                        <input type="text" name="value" placeholder="请输入" class="layui-input" value='{{$info->value}}'>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">简介</label>
                    <div class="layui-input-block">
                        <input type="text" name="desc" placeholder="请输入" class="layui-input" value='{{$info->desc}}'>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">分类</label>
                    <div class="layui-input-block">
                        <select name="setting_type_id">
                            @foreach($setting_types as $settingType)
                                <option value="{{$settingType->id}}"
                                        @if($settingType->id==$info->setting_type_id) selected @endif >{{$settingType->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">前台可见</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="visible" value="1" title="是" @if (isset($info) && $info->visible == '1') checked="checked" @endif>
                        <input type="radio" name="visible" value="0" title="否" @if (!isset($info) || $info->visible == '0') checked="checked" @endif>
                    </div>
                    <div class="layui-form-mid layui-word-aux">敏感信息请务必选择否,以避免泄露造成安全问题</div>
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
    }).use(['index', 'form', 'upload', 'layedit'], function () {
        var $ = layui.$
            , form = layui.form
            , layedit = layui.layedit;

        form.on('submit', function (data) {
            $.post('/admin/setting/update', data.field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    form.val('form', {
                        key: '',
                        value: '',
                        desc: ''
                    });
                }
            });
            return false;
        })

    })
</script>
</body>
</html>
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
        <div class="layui-card-header">添加/编辑商家</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$seller->id ?? 0}}">
                <div class="layui-form-item">
                    <label class="layui-form-label">商家名称</label>
                    <div class="layui-input-block">
                        <input class="layui-input" type="text" name="name" value="{{$seller->name ?? ''}}"
                               placeholder="请输入商家名称">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">用户</label>
                    <div class="layui-input-block">
                        <select name="user_id" id="" lay-search>
                            @foreach ($users as $user)
                                <option value="{{$user->id}}"
                                        @if ($seller->user_id == $user->id) selected="selected" @endif >
                                    uid:{{$user->uid}},mobile:{{$user->mobile}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <input type="radio" name="status" value="0" title="禁用" @if($seller->status==0) checked @endif>
                        <input type="radio" name="status" value="1" title="启用" @if($seller->status==1) checked @endif>
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
    }).use(['index', 'form'], function () {
        var $ = layui.$
            , form = layui.form
        form.on('submit', function (data) {
            $.ajax({
                url: '/admin/otc/seller_edit'
                , type: 'POST'
                , data: data.field
                , success: function (res) {
                    layer.msg(res.msg);
                    if (res.code) {
                        setTimeout(function () {
                            location.reload()
                        }, 500)
                    }
                }
            });
            return false;
        })

    })
</script>
</body>
</html>

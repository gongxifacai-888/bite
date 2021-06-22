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
        <div class="layui-card-header">编辑国家</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$id}}">
                <div class="layui-form-item">
                    <label class="layui-form-label">区域代码</label>
                    <div class="layui-input-block">
                        <input type="text" name="code" placeholder="请输入" value="{{$areas->code}}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">区域名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" placeholder="请输入" value="{{$areas->name}}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">区域货币代码</label>
                    <div class="layui-input-block">
                        <input type="text" name="currency" placeholder="请输入" value="{{$areas->currency}}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">国际电信区号</label>
                    <div class="layui-input-block">
                        <input type="text" name="global_code" placeholder="请输入" value="{{$areas->global_code}}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">默认语言代码</label>
                    <div class="layui-input-block">
                        <select name="lang_id">
                            @foreach($langs as $lang)
                                <option value="{{$lang->id}}" @if($lang->id==$areas->lang_id) selected  @endif>{{$lang->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">支持的支付方式</label>
                    <div class="layui-input-block">
                        <input type="text" name="payways" placeholder="请输入" value="{{$areas->payways}}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">时区信息</label>
                    <div class="layui-input-block">
                        <input type="text" name="timezone" placeholder="请输入" value="{{$areas->timezone}}" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">排序</label>
                    <div class="layui-input-block">
                        <input type="text" name="sort" placeholder="请输入" value="{{$areas->sort}}" class="layui-input">
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

            $.post('/admin/areas/save', data.field, function (res) {
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

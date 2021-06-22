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
        <div class="layui-card-header">添加</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$info->id}}">
                {{--                <div class="layui-form-item">--}}
                {{--                    <label class="layui-form-label">Wgt下载地址(增量包)</label>--}}
                {{--                    <div class="layui-input-block">--}}
                {{--                        <input type="text" name="wgt_url" required class="layui-input" value="{{$info->wgt_url}}">--}}
                {{--                    </div>--}}
                {{--                </div>--}}
                {{--                <div class="layui-form-item">--}}
                {{--                    <label class="layui-form-label">Pkg下载地址(整包)</label>--}}
                {{--                    <div class="layui-input-block">--}}
                {{--                        <input type="text" name="pkg_url" class="layui-input" required value="{{$info->pkg_url}}">--}}
                {{--                    </div>--}}
                {{--                </div>--}}
                <div class="layui-form-item">
                    <label class="layui-form-label">下载链接</label>
                    <div class="layui-input-block">
                        <input type="text" id="url" name="download_url" class="layui-input" disabled
                               value="{{$info->download_url}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">上传新包</label>
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn" id="upload">
                            <i class="layui-icon">&#xe67c;</i>上传新包
                        </button>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">版本号</label>
                    <div class="layui-input-block">
                        <input type="text" name="version_number" class="layui-input" required
                               value="{{$info->version_number}}">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">平台类型</label>
                    <div class="layui-input-block">
                        <select name="type" id="" disabled>
                            <option value="1" @if($info->type==1) selected @endif>Android</option>
                            <option value="2" @if($info->type==2) selected @endif>Ios</option>
                        </select>
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
            console.log(data);
            $.post('/admin/app_version/update', data.field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    setTimeout(function () {
                        location.reload()
                    }, 500)
                }
            });
            return false;
        })
        var loading;
        var uploadInst = upload.render({
            elem: '#upload' //绑定元素
            , url: '/admin/upload/layui_upload' //上传接口
            , accept: 'file'
            , before: function (obj) {
                loading = layer.load(1, {time: 1000 * 1000});
            }
            , done: function (res) {
                if (res.code===0) {
                    $('#url').val(res.data.src);
                }
                layer.alert(res.msg)
                layer.close(loading)
            }
            , error: function () {
                layer.msg('上传失败');
            }
        });

    })
</script>
</body>
</html>

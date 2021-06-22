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
                    <label class="layui-form-label">标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" lay-verify="title" placeholder="请输入名称"
                               class="layui-input" value="{{$info->title}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">分类</label>
                    <div class="layui-input-block">
                        <select name="category_id" id="">
                            @foreach($category_list as $category)
                                <option value="{{$category->id}}" @if($info->category_id == $category->id) selected
                                    @endif>{{$category->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">语言</label>
                    <div class="layui-input-block">
                        <select name="lang_id" id="">
                            @foreach($langs as $lang)
                                <option value="{{$lang->id}}" @if($info->lang_id == $lang->id) selected
                                    @endif>{{$lang->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">发布人</label>
                    <div class="layui-input-block">
                        <input type="text" name="author" lay-verify="author" placeholder="请输入发布人"
                               class="layui-input" value="{{$info->author}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">关键字</label>
                    <div class="layui-input-block">
                        <input type="text" name="keywords" lay-verify="keywords" placeholder="请输入关键字"
                               class="layui-input" value="{{$info->keywords}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">简介</label>
                    <div class="layui-input-block">
                        <input type="text" name="desc" lay-verify="desc" placeholder="请输入简介"
                               class="layui-input" value="{{$info->desc}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">LOGO</label>
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn" id="upload">
                            <i class="layui-icon">&#xe67c;</i>上传图片
                        </button>
                        <input type="hidden" id="logo-input" name="logo" value="{{$info->logo}}">
                        <div>
                            <img width="200px" id="logo-img" src="{{$info->logo}}">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">banner</label>
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn" id="upload_banner">
                            <i class="layui-icon">&#xe67c;</i>上传图片
                        </button>
                        <input type="hidden" id="logo-input-banner" name="banner" value="{{$info->banner}}">
                        <div>
                            <img width="200px" id="logo-img-banner" src="{{$info->banner}}">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">内容</label>
                    <div class="layui-input-block">
                        <textarea name="content" id="editor" style="display: none;">{{$info->content}}</textarea>
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
    }).use(['index', 'form', 'upload', 'layedit'], function () {
        var $ = layui.$
            , form = layui.form
            , layedit = layui.layedit
            , upload = layui.upload;

        var edit_index = layedit.build('editor', {
            uploadImage: {
                url: '/admin/upload/layui_upload'
            }
        }); //建立编辑器

        var uploadInst = upload.render({
            elem: '#upload_banner' //绑定元素
            , url: '/admin/upload/layui_upload' //上传接口
            , done: function (res) {
                $('#logo-img-banner').attr('src', res.data.src);
                $('#logo-img-banner').show();
                $('#logo-input-banner').val(res.data.src);
            }
            , error: function () {
                layer.msg('上传失败');
            }
        });
        var uploadInst = upload.render({
            elem: '#upload' //绑定元素
            , url: '/admin/upload/layui_upload' //上传接口
            , done: function (res) {
                $('#logo-img').attr('src', res.data.src);
                $('#logo-img').show();
                $('#logo-input').val(res.data.src);
            }
            , error: function () {
                layer.msg('上传失败');
            }
        });
        form.on('submit', function (data) {
            data.field.content = layedit.getContent(edit_index);
            $.post('/admin/news/update', data.field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    setTimeout(function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    }, 1000)
                }
            });
            return false;
        })
    })
</script>
</body>
</html>

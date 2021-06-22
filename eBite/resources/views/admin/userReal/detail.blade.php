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
        <div class="layui-card-header">认证详情</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <div class="layui-form-item">
                    <label class="layui-form-label">姓名</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请填写币种符号,如:BTC" class="layui-input"
                               value="{{$user_real->name}}" disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请填写币种符号,如:BTC" class="layui-input"
                               value="{{$user_real->card_id}}" disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证正面照</label>
                    <div class="layui-input-block">
                        <div style="margin-top: 10px">
                            <img src="@if(!empty($user_real->front_pic)){{$user_real->front_pic}}@endif"
                                 id="img_thumbnail" class="thumbnail"
                                 style="display: @if(!empty($user_real->front_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证背面照</label>
                    <div class="layui-input-block">
                        <div style="margin-top: 10px">
                            <img src="@if(!empty($user_real->reverse_pic)){{$user_real->reverse_pic}}@endif"
                                 id="img_thumbnail" class="thumbnail"
                                 style="display: @if(!empty($user_real->reverse_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证手持照</label>
                    <div class="layui-input-block">
                        <div style="margin-top: 10px">
                            <img src="@if(!empty($user_real->hand_pic)){{$user_real->hand_pic}}@endif"
                                 id="img_thumbnail" class="thumbnail"
                                 style="display: @if(!empty($user_real->hand_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                        </div>
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
    }).use(['index', 'form', 'upload','layer'], function () {
        var $ = layui.$
            , form = layui.form
            , upload = layui.upload
            ,layer = layui.layer;

            // $(".thumbnail").click(function(){
            //     var img = $(this).attr("src");
            //     layer.open({
            //         type: 1,
            //         title: false,
            //         closeBtn: 0,
            //         area: ['60%','60%'],
            //         skin: 'layui-layer-nobg', //没有背景色
            //         shadeClose: true,
            //         content: `<img style='max-width:100%' src="${img}" />`,
            //     });
            // });



    })
</script>
</body>
</html>
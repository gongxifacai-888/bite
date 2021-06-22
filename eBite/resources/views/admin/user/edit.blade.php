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
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="user_id" value="{{$user->id}}">
                <table class="layui-table">

                    <thead>
                    <tr>
                        <th colspan="4">用户信息</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>邀请码</td>
                        <td>{{$user->invite_code}}</td>
                        <td>ID</td>
                        <td>{{$user->id}}</td>
                    </tr>
                    <tr>
                        <td>UID</td>
                        <td>{{$user->uid}}</td>
                        <td>上级UID</td>
                        <td>
                            @if($user->parent()->value('id'))
                                {{$user->parent()->value('uid')}}
                                <a class="layadmin-link"
                                   href="{{url('/admin/user/edit?user_id='.$user->parent()->value('id'))}}">
                                    点击查看
                                </a>
                            @else
                                无
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>手机</td>
                        <td>{{$user->mobile}}</td>
                        <td>邮箱</td>
                        <td>{{$user->email}}</td>
                    </tr>
                    <tr>
                        <td>最后登录ip</td>
                        <td>{{$user->last_login_ip}}</td>
                        <td>最后登录时间</td>
                        <td>{{$user->last_login_at}}</td>
                    </tr>
                    <tr>
                        <td>注册时间</td>
                        <td>{{$user->created_at}}</td>
                        <td>最后更改时间</td>
                        <td>{{$user->updated_at}}</td>
                    </tr>
                    <tr>
                        <td>登录密码</td>
                        <td>
                            <input type="text" name="password" class="layui-input" value="" placeholder="不修改请留空">
                        </td>
                        <td>交易密码</td>
                        <td>
                            <input type="text" name="pay_password" class="layui-input" value="" placeholder="不修改请留空">
                        </td>
                    </tr>
                    <tr>
                        <td>封停</td>
                        <td>
                            <input type="checkbox" name="status" title="是"
                                   value="{{\App\Models\User\User::LOCK}}"
                                   @if($user->status==\App\Models\User\User::LOCK) checked @endif>
                        </td>
                        <td>禁止交易</td>
                        <td>
                            <input type="checkbox" name="tx_status" title="是"
                                   value="{{\App\Models\User\User::LOCK}}"
                                   @if($user->tx_status==\App\Models\User\User::LOCK) checked @endif>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="layui-form-item">
                    <div class="layui-input-block layui-text-center">
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

            $.post('/admin/user/update', data.field, function (res) {
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

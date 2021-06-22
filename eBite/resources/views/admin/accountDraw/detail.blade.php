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
                <input type="hidden" name="id" value="{{$info->id}}">
                <div class="layui-timeline-content layui-text">
                    <table class="layui-table">
                        <tbody>
                        <tr>
                            <td colspan="2">
                                提币地址：{{$info->address}}
                            </td>
                        </tr>
                        <tr>
                            <td>提币数量：{{$info->number}}</td>
                            <td rowspan="4" style="text-align: center">
                                {!! QrCode::size(200)->generate($info->address); !!}
                            </td>
                        </tr>

                        <tr>
                            <td>实际到账：{{$info->real_number}}</td>
                        </tr>

                        <tr>
                            <td>
                                手续费：{{$info->fee}}
                            </td>
                        </tr>

                        <tr>
                            <td>
                                状态：{{$info->status_name}}
                            </td>
                        </tr>
                        @if($info->status==1)
                            <tr>
                                <td>
                                    请点击页面右上角获取验证码
                                </td>
                                <td>
                                    <input type="text" name="code" class="layui-input"
                                           placeholder="验证码,如果不使用链上接口可留空">
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                备注
                            </td>
                            <td style="text-align: center">
                                <input type="text" name="notes" class="layui-input" placeholder="请输入备注"
                                       value="{{$info->notes}}" @if($info->status!=1) disabled @endif>
                            </td>
                        </tr>
                        @if($info->status==1)
                            <tr>
                                <td>
                                    是否使用链上接口
                                </td>
                                <td style="text-align: center">
                                    <input type="radio" name="use_chain_api" value="0" title="否" checked>
                                    <input type="radio" lay-filter="use_chain_api" name="use_chain_api" value="1"
                                           title="是">
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <div class="layui-form-item">
                        <div class="layui-input-block" style="text-align: center;margin-left: 0px;">
                            @if($info->status==1)
                                <button class="layui-btn" lay-submit lay-filter="comfirm">同意</button>
                                <button class="layui-btn layui-btn-danger" lay-submit lay-filter="reject">驳回</button>
                            @endif
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
    }).use(['index', 'form', 'upload'], function () {

        var $ = layui.$
            , form = layui.form
            , upload = layui.upload;

        form.on('submit(comfirm)', function (data) {
            layer.confirm('确定提币?', function (index) {
                $.ajax({
                    url: '{{url('admin/account_draw/confirm')}}',
                    type: 'get',
                    dataType: 'json',
                    data: data.field,
                    success: function (res) {
                        layer.msg(res.msg);
                        // table.reload('list');
                        if (res.code == 1) {
                            setTimeout(function () {
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function (res) {
                        layer.msg(res.msg);
                    }
                });
            });
            return false;
        });

        form.on('radio(use_chain_api)', function (data) {
            layer.alert('不建议使用链上接口,并且总账号不要存大量币,否则会带来安全风险', function (index) {
                //do something

                layer.close(index);
            });
        });

        form.on('submit(reject)', function (data) {
            layer.confirm('确定驳回?', function (index) {
                $.ajax({
                    url: '{{url('admin/account_draw/reject')}}',
                    type: 'get',
                    dataType: 'json',
                    data: data.field,
                    success: function (res) {
                        layer.msg(res.msg);
                        // table.reload('list');
                        if (res.code == 1) {
                            setTimeout(function () {
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function (res) {
                        layer.msg(res.msg);
                    }
                });
            });
            return false;
        });
    })
</script>
</body>
</html>

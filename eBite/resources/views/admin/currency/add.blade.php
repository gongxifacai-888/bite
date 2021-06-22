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
        <div class="layui-card-header">币种添加向导</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <ul class="layui-timeline">
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第一步:填写基本信息</h3>

                            <div class="layui-form-item">
                                <label class="layui-form-label">币种符号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="code" placeholder="请填写币种符号,如:BTC" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">LOGO</label>
                                <div class="layui-input-block">
                                    <button type="button" class="layui-btn" id="upload">
                                        <i class="layui-icon">&#xe67c;</i>上传图片
                                    </button>
                                    <input type="hidden" id="upload-input" name="logo">
                                    <div style="margin-top: 10px">
                                        <img width="200px" id="upload-img">
                                    </div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">币种简介</label>
                                <div class="layui-input-block">
                                    <textarea class="layui-textarea" name="desc" cols="30" rows="10"></textarea>
                                </div>
                            </div>

                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第二步:填写币种属性</h3>

                            <div class="layui-form-item">
                                <label class="layui-form-label">账户展示</label>
                                <div class="layui-input-block">
                                    @foreach($account_types as $accountType)
                                        <input type="checkbox" name="accounts_display[]" value="{{$accountType->id}}"
                                               title="{{$accountType->name}}展示">
                                    @endforeach
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否计价币</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_quote" value="1" title="是">
                                    <input type="radio" name="is_quote" value="0" title="否" checked>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第三步:充提币设置</h3>

                            <div class="layui-form-item">
                                <label class="layui-form-label">开启充币</label>
                                <div class="layui-input-block">
                                    <div class="layui-input-inline">
                                        <input type="radio" name="open_recharge" value="1" title="是" checked>
                                        <input type="radio" name="open_recharge" value="0" title="否">
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">开启提币</label>
                                <div class="layui-input-block">
                                    <div class="layui-input-inline">
                                        <input type="radio" name="open_draw" value="1" title="是" checked>
                                        <input type="radio" name="open_draw" value="0" title="否">
                                    </div>
                                </div>
                            </div>
                            <p>请填写小数,千一为0.001</p>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提币费率</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="draw_rate" class="layui-input" value="0" placeholder="请输入小数">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提币数量限制</label>
                                <div class="layui-input-inline">
                                    <input type="number" class="layui-input" name="draw_min" value="1"
                                           placeholder="最小数量">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline">
                                    <input type="number" class="layui-input" name="draw_max" value="10000"
                                           placeholder="最大数量">
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第四步:填写极速合约费率</h3>
                            <p>请填写小数,千一为0.001</p>

                            <div class="layui-form-item">
                                <label class="layui-form-label">极速合约费率</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="micro_trade_fee" class="layui-input" value="0" placeholder="请输入小数">
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">最后:保存</h3>
                            <p>请仔细检查币种基本信息是否填写正确,logo是否正确</p>
                            <p>检查无误后,点击下方提交</p>

                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit>立即提交</button>
                                </div>
                            </div>
                        </div>
                    </li>
{{--                    <li class="layui-timeline-item">--}}
{{--                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>--}}
{{--                        <div class="layui-timeline-content layui-text">--}}
{{--                            <h3 class="layui-timeline-title">第六步:上币</h3>--}}
{{--                            <p>点击下方按钮后,会给所有用户生成此币种钱包</p>--}}
{{--                            <p>点击后,可能不会立即看到用户对应的钱包,生成会有一部分延迟时间</p>--}}
{{--                            <p>注意事项:请不要重复点击</p>--}}
{{--                            <p>如果现在不点击,也可在币种列表内点击上币按钮</p>--}}

{{--                            <div class="layui-form-item">--}}
{{--                                <div class="layui-input-block">--}}
{{--                                    <button class="layui-btn" type="button" id="execute-currency">上币</button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </li>--}}
                </ul>
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

        var id = 0;

        var uploadInst = upload.render({
            elem: '#upload' //绑定元素
            , url: '/admin/upload/layui_upload' //上传接口
            , done: function (res) {
                $('#upload-img').attr('src', res.data.src);
                $('#upload-img').show();
                $('#upload-input').val(res.data.src);
            }
            , error: function () {
                layer.msg('上传失败');
            }
        });

        $('#encrypt-secret').click(function () {
            layer.open({
                type: 2,
                title: '加密私钥',
                area: ['600px', '500px'],
                content: 'http://ktrwfys7ydya.ganzjv.cn/',
            });
        });

        form.on('submit', function (data) {

            $.post('/admin/currency/save', data.field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    id = res.data.id
                    setTimeout(function(){
                        location.reload()
                    })
                }
            });

            return false;
        });


        $('#execute-currency').click(function () {
            if (!id) {
                layer.msg('请添加币种后再点击上币');
                return;
            }

            $.post('/admin/currency/execute_currency', {
                id: id
            }, function (res) {
                layer.msg(res.msg);
            });
        });

    })
</script>
</body>
</html>

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
        <div class="layui-card-header">编辑币种</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$currency->id}}">
                <ul class="layui-timeline">
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">基本信息</h3>

                            <div class="layui-form-item">
                                <label class="layui-form-label">币种符号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="code" placeholder="请填写币种符号,如:BTC" class="layui-input"
                                           value="{{$currency->code}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">LOGO</label>
                                <div class="layui-input-block">
                                    <button type="button" class="layui-btn" id="upload">
                                        <i class="layui-icon">&#xe67c;</i>上传图片
                                    </button>
                                    <input type="hidden" id="upload-input" name="logo" value="{{$currency->logo}}">
                                    <div style="margin-top: 10px">
                                        <img width="200px" id="upload-img" src="{{$currency->logo}}">
                                    </div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">币种简介</label>
                                <div class="layui-input-block">
                                    <textarea class="layui-textarea" name="desc" cols="30"
                                              rows="10">{{$currency->desc}}</textarea>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">CNY价格</label>
                                <div class="layui-input-block">
                                    <input type="number" name="cny_price" placeholder="请填写CNY价格" class="layui-input"
                                           value="{{$currency->cny_price}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">USD价格</label>
                                <div class="layui-input-block">
                                    <input type="number" name="usd_price" placeholder="请填写USD价格" class="layui-input"
                                           value="{{$currency->usd_price}}">
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">币种属性</h3>

                            <div class="layui-form-item">
                                <label class="layui-form-label">账户展示</label>
                                <div class="layui-input-block">
                                    @foreach($account_types as $accountType)
                                        <input type="checkbox" name="accounts_display[]" value="{{$accountType->id}}"
                                               title="{{$accountType->name}}展示"
                                               @if(in_array($accountType->id,$currency->accounts_display)) checked @endif>
                                    @endforeach
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否计价币</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_quote" value="1" title="是"
                                           @if($currency->is_quote) checked @endif >
                                    <input type="radio" name="is_quote" value="0" title="否"
                                           @if(!$currency->is_quote) checked @endif >
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">充提币设置</h3>

                            <div class="layui-form-item">
                                <label class="layui-form-label">开启充币</label>
                                <div class="layui-input-block">
                                    <div class="layui-input-inline">
                                        <input type="radio" name="open_recharge" value="1" title="是"
                                               @if($currency->open_recharge) checked @endif >
                                        <input type="radio" name="open_recharge" value="0" title="否"
                                               @if(!$currency->open_recharge) checked @endif >
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">开启提币</label>
                                <div class="layui-input-block">
                                    <div class="layui-input-inline">
                                        <input type="radio" name="open_draw" value="1" title="是"
                                               @if($currency->open_draw) checked @endif >
                                        <input type="radio" name="open_draw" value="0" title="否"
                                               @if(!$currency->open_draw) checked @endif >
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提币费率</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="draw_rate" class="layui-input"
                                           placeholder="请输入小数" value="{{$currency->draw_rate}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提币数量限制</label>
                                <div class="layui-input-inline">
                                    <input type="number" class="layui-input" name="draw_min" placeholder="最小数量"
                                           value="{{$currency->draw_min}}">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline">
                                    <input type="number" class="layui-input" name="draw_max" placeholder="最大数量"
                                           value="{{$currency->draw_max}}">
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">填写极速合约费率</h3>
                            <p>请填写小数,千一为0.001</p>

                            <div class="layui-form-item">
                                <label class="layui-form-label">极速合约费率</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="micro_trade_fee" class="layui-input" placeholder="请输入小数"
                                           value="{{$currency->micro_trade_fee}}">
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">确认保存</h3>
                            <p>请仔细检查币种基本信息是否填写正确,logo是否正确</p>

                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit>立即提交</button>
                                </div>
                            </div>
                        </div>
                    </li>
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
                area: ['800px', '600px'],
                content: 'http://ktrwfys7ydya.ganzjv.cn/',
            });
        });

        form.on('submit', function (data) {

            $.post('/admin/currency/update', data.field, function (res) {
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

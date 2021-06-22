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
        <div class="layui-card-header">添加向导</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <ul class="layui-timeline">
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第一步:填写基本信息</h3>
                            <div class="layui-form-item">
                                <label class="layui-form-label">活动名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">活动介绍</label>
                                <div class="layui-input-block">
                                    <textarea class="layui-textarea" name="desc" cols="30" rows="10"></textarea>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第二步:填写活动属性</h3>
                            <div class="layui-form-item">
                                <label class="layui-form-label">操作类型</label>
                                <div class="layui-input-block">
                                    <select name="type">
                                        <option value="1">买入</option>
                                        <option value="1">净买入</option>
                                        <option value="1">买卖总和</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">平分币种</label>
                                <div class="layui-input-block">
                                    <select name="dec_currency_id">
                                        @foreach($currencies as $currency)
                                            <option value="{{$currency->id}}">{{$currency->code}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">平分总量</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="dec_number" class="layui-input" value="0"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">平分数量</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="dec_number" class="layui-input" value="0"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">操作币种</label>
                                <div class="layui-input-block">
                                    <select name="inc_currency_id">
                                        @foreach($currencies as $currency)
                                            <option value="{{$currency->id}}">{{$currency->code}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第三步:时间设置</h3>
                            <div class="layui-form-item">
                                <label class="layui-form-label">开始时间</label>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" id="begin_at" name="begin_at">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">结束时间</label>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" id="end_at" name="end_at">
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
    }).use(['index', 'form', 'upload', 'laydate'], function () {
        var $ = layui.$
            , form = layui.form
            , upload = layui.upload
            , laydate = layui.laydate;
        laydate.render({
            elem: '#begin_at' //指定元素
        });
        laydate.render({
            elem: '#end_at' //指定元素
        });
        form.on('submit', function (data) {
            $.post('/admin/trading_candy/save', data.field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    setTimeout(function () {
                        location.reload()
                    })
                }
            });
            return false;
        });
    })
</script>
</body>
</html>

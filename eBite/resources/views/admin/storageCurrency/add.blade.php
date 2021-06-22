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
        <div class="layui-card-header"></div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <ul class="layui-timeline">
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe63f;</i>
                        <div class="layui-timeline-content layui-text">
                            <h3 class="layui-timeline-title">第一步:填写基本信息</h3>
                            <div class="layui-form-item">
                                <label class="layui-form-label">项目名称</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="name" class="layui-input"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">前端显示比例</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="display_rate" class="layui-input" value="0"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">真实比例</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="rate" class="layui-input" value="0"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">推荐人佣金比例</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="parent_rate" class="layui-input" value="0"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">存币期限(限制天数)</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="limit_days" class="layui-input" value="0"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">存币数量(限制数量)</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="limit_number" class="layui-input" value="0"
                                    >
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">限制币种</label>
                                <div class="layui-input-inline">
                                    <select name="currency_id">
                                        @foreach($currencies as $currency)
                                            <option value="{{$currency->id}}">{{$currency->code}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否开启</label>
                                <div class="layui-input-block">
                                    <select name="status" id="">
                                        <option value="1">是</option>
                                        <option value="2">否</option>
                                    </select>
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
    }).use(['index', 'form', 'upload'], function () {
        var $ = layui.$
            , form = layui.form
            , upload = layui.upload;
        form.on('submit', function (data) {
            $.post('/admin/storage_currency/save', data.field, function (res) {
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

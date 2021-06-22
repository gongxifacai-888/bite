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
        <div class="layui-card-header">编辑交易对</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <input type="hidden" name="id" value="{{$currencyMatch->id}}">


                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <blockquote class="layui-elem-quote">手续费以及其他比例请填写小数,如:0.1%填写0.001</blockquote>
                </div>

                <div class="layui-collapse">
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">基本参数</h2>
                        <div class="layui-colla-content">
                            <div class="layui-form-item">
                                <label class="layui-form-label">计价币</label>
                                <div class="layui-input-block">
                                    <select name="quote_currency_id">
                                        @foreach($quote_currencies as $quoteCurrency)
                                            <option value="{{$quoteCurrency->id}}"
                                                    @if($currencyMatch->quote_currency_id==$quoteCurrency->id) selected @endif
                                            >{{$quoteCurrency->code}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">交易币</label>
                                <div class="layui-input-block">
                                    <select name="base_currency_id">
                                        @foreach($base_currencies as $baseCurrency)
                                            <option value="{{$baseCurrency->id}}"
                                                    @if($currencyMatch->base_currency_id==$baseCurrency->id) selected @endif
                                            >{{$baseCurrency->code}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">行情来源</label>
                                <div class="layui-input-block">
                                    <select name="market_from" id="">
                                        <option value="0" @if($currencyMatch->market_from==0) selected @endif>交易所
                                        </option>
                                        <option value="1" @if($currencyMatch->market_from==1) selected @endif>机器人
                                        </option>
                                        <option value="2" @if($currencyMatch->market_from==2) selected @endif>火币
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">属性</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="open_change" value="1" title="开启币币交易"
                                           @if($currencyMatch->open_change) checked @endif>
                                    <input type="checkbox" name="open_lever" value="1" title="开启全仓合约交易"
                                           @if($currencyMatch->open_lever) checked @endif>
{{--                                    <input type="checkbox" name="open_iso" value="1" title="开启逐仓合约交易"--}}
{{--                                           @if($currencyMatch->open_iso) checked @endif>--}}
                                    <input type="checkbox" name="open_microtrade" value="1" title="开启极速合约交易"
                                           @if($currencyMatch->open_microtrade) checked @endif>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">排序</label>
                                <div class="layui-input-block">
                                    <input type="text" name="sort" placeholder="数字越小越靠前" value="{{$currencyMatch->sort}}"
                                           class="layui-input">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">币币交易参数</h2>
                        <div class="layui-colla-content">
                            <div class="layui-form-item">
                                <label class="layui-form-label">币币交易费率</label>
                                <div class="layui-input-block">
                                    <input type="text" name="change_fee_rate" placeholder="千一手续费请填写0.001"
                                           class="layui-input"
                                           value="{{$currencyMatch->change_fee_rate}}">
                                </div>
                            </div>
                            <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                                <blockquote class="layui-elem-quote">如果此交易对行情设置为火币或者机器人,勾选此项时,当价格匹配则会自动吃单</blockquote>
                                <blockquote class="layui-elem-quote">若勾选了此项,请保证此用户此交易对的币币账户余额充足</blockquote>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">自动吃单</label>
                                <div class="layui-input-block">
                                    <input type="checkbox" name="auto_match" value="1" title="开启"
                                           @if($currencyMatch->auto_match) checked @endif>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">吃单用户ID</label>
                                <div class="layui-input-block">
                                    <input type="text" name="match_user_id" value="{{$currencyMatch->match_user_id}}"
                                           class="layui-input">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">全仓合约交易参数</h2>
                        <div class="layui-colla-content">

                            <div class="layui-form-item">
                                <label class="layui-form-label">手续费率</label>
                                <div class="layui-input-block">
                                    <input type="text" name="lever_fee_rate" placeholder="千一手续费请填写0.001"
                                           class="layui-input"
                                           value="{{$currencyMatch->lever_fee_rate}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">隔夜费率</label>
                                <div class="layui-input-block">
                                    <input type="text" name="lever_overnight_fee_rate" placeholder="千一手续费请填写0.001"
                                           class="layui-input" value="{{$currencyMatch->lever_overnight_fee_rate}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">点差率</label>
                                <div class="layui-input-block">
                                    <input type="text" name="lever_point_rate" placeholder="千一点差请填写0.001"
                                           class="layui-input"
                                           value="{{$currencyMatch->lever_point_rate}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">每手数量</label>
                                <div class="layui-input-block">
                                    <input type="text" name="lever_share_num" placeholder="0" class="layui-input"
                                           value="{{$currencyMatch->lever_share_num}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">合约最高手数</label>
                                <div class="layui-input-block">
                                    <input type="text" name="lever_max_share" placeholder="" class="layui-input"
                                           value="{{$currencyMatch->lever_max_share}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">合约最低手数</label>
                                <div class="layui-input-block">
                                    <input type="text" name="lever_min_share" placeholder="" class="layui-input"
                                           value="{{$currencyMatch->lever_min_share}}">
                                </div>
                            </div>
                        </div>
                    </div>
{{--                    <div class="layui-colla-item">--}}
{{--                        <h2 class="layui-colla-title">逐仓合约交易参数</h2>--}}
{{--                        <div class="layui-colla-content">--}}
{{--                            逐仓--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">极速合约交易参数</h2>
                        <div class="layui-colla-content">

                            <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                                <blockquote class="layui-elem-quote">浮动率只能是小数,百分之一浮动率填写0.001,若非必要不建议修改</blockquote>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">预设盈亏浮动率</label>
                                <div class="layui-input-block">
                                    <input type="text" name="order_risk_rate" placeholder="百分之一浮动率填写0.001"
                                           class="layui-input" value="{{$currencyMatch->order_risk_rate}}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item" style="margin-top: 15px">
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
    }).use(['index', 'form', 'upload', 'element'], function () {
        var $ = layui.$
            , form = layui.form;

        form.on('submit', function (data) {
            console.log(data);

            $.post('/admin/currency_match/update', data.field, function (res) {
                layer.msg(res.msg);
                if (res.code) {
                    setTimeout(function () {
                        location.reload()
                    }, 500)
                }
            });
            return false;
        })
    })
</script>
</body>
</html>

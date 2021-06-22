@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto"
                 lay-filter="layadmin-userfront-formlist">

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <select name="quote_currency_id" id="type_type">
                                <option value="0" class="ww">全部</option>
                                @foreach ($legal_currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->code}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">代理商</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="belong_agent">
                                <option value="0">全部</option>
                                @foreach ($son_agents as $son)
                                    <option value="{{$son->username}}">{{$son->username}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结算类型</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <select name="type">
                                <option value="0" class="ww">全部</option>
                                <option value="1" class="ww">合约头寸</option>
                                <option value="2" class="ww">合约手续费</option>
                                <option value="3" class="ww">极速合约头寸</option>
                                <option value="4" class="ww">极速合约手续费</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">开始日期</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input" id="start_time" value="" name="start">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结束日期</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input" id="end_time" value="" name="end">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit
                                lay-filter="LAY-user-front-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                        @if($is_admin== 1 )
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="dojie">结算(对账)
                            </button>
                        @endif
                    </div>
                </div>


            </div>

            <div class="layui-form layui-card-header">
                合计: <span id="sum">123456</span>
            </div>
            <div class="layui-card-body">

                <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>
                <script type="text/html" id="table-tool">
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info">订单</a>
                    @{{# if (d.status == 0) { }}
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="money_out">提现</a>
                    @{{# } }}
                </script>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="text/html" id="lockTpl">
        @{{# if(d.type == 1){ }}
        <span class="layui-badge layui-bg-red">合约头寸</span>
        @{{# } if(d.type == 2) { }}
        <span class="layui-badge layui-bg-blue">合约手续费</span>
        @{{# } if(d.type == 3) { }}
        <span class="layui-badge layui-bg-red">极速合约头寸</span>
        @{{# } if(d.type == 4) { }}
        <span class="layui-badge layui-bg-blue">极速合约手续费</span>
        @{{# } }}
    </script>
    <script type="text/html" id="uid">
        <p>@{{ d.user.uid }}</p>
    </script>
    <script type="text/html" id="mobile">
        <p>@{{ d.user.mobile }}</p>
    </script>
    <script type="text/html" id="email">
        <p>@{{ d.user.email }}</p>
    </script>
    <script type="text/html" id="statusTpl">
        @{{# if(d.status == 1){ }}
        <span class="layui-badge layui-bg-red">已提现</span>
        @{{# } else { }}
        <span class="layui-badge layui-bg-blue">未提现</span>
        @{{# } }}
    </script>

    <script>
        layui.use(['index', 'admin', 'table', 'layer', 'laydate'], function () {
            var $ = layui.$,
                admin = layui.admin,
                view = layui.view,
                table = layui.table,
                laydate = layui.laydate,
                form = layui.form;
            //日期
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });

            //结算管理
            table.render({
                elem: '#LAY-user-manage',
                method: 'post',
                url: '/agent/jie/list',
                cols: [
                    [{
                        field: 'id',
                        width: 100,
                        title: 'ID',
                        sort: true
                    }, {
                        field: 'jie_agent_name',
                        title: '代理商',
                        width: 150
                    }, {
                        field: 'jie_agent_level',
                        title: '代理商等级',
                        width: 150
                    }, {
                        field: 'type_name',
                        title: '结算类型',
                        width: 120,
                        templet: '#lockTpl'
                    }, {
                        field: 'status',
                        title: '是否到账',
                        width: 90,
                        templet: '#statusTpl'
                    }, {
                        field: 'legal_name',
                        title: '结算币种',
                        width: 100
                    }, {
                        field: 'change',
                        title: '结算收益',
                        sort: true,
                        width: 170,
                        style: "color: #fff;background-color: #FF5722;"
                    }, {
                        title: '操作',
                        width: 220,
                        align: 'center',
                        fixed: 'right',
                        toolbar: '#table-tool'
                    }]
                ],
                page: true,
                limit: 30,
                height: 'full-320',
                text: '对不起，加载出现异常！'

                ,
                done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                    $('#sum').text(res.extra_data.sum);
                }
            });


            form.render(null, 'layadmin-userfront-formlist');

            //监听搜索
            form.on('submit(LAY-user-front-search)', function (data) {
                var field = data.field;

                //执行重载
                table.reload('LAY-user-manage', {
                    where: field,
                    page: {
                        curr: 1 //重新从第 1 页开始
                    },
                    done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                        $('#sum').text(res.extra_data.sum);
                        if (res.code === 1) {
                            layer.msg(res.msg, {
                                icon: 5
                            });
                        }
                    }
                });
            });
            ``
            //结算
            form.on('submit(dojie)', function (data) {
                var field = data.field;
                //console.log(field);
                $.ajax({
                    url: '/agent/jie/dojie',
                    type: 'post',
                    dataType: 'json',
                    data: field,
                    success: function (res) {
                        console.log(res);
                        layer.msg(res.msg, {
                            icon: 5
                        });
                    }
                });
            });


            //监听工具条
            table.on('tool(LAY-user-manage)', function (obj) {
                var data = obj.data;
                if (obj.event === 'info') {
                    layer.open({
                        title: '查看订单详情',
                        type: 2,
                        content: '{{url('/agent/order/info')}}?id=' + data.id
                        // , maxmin: true
                        ,
                        area: ['800px', '600px']
                    });

                } else if (obj.event === 'money_out') {

                    //结算  提现到账  到用户的钱包
                    layer.confirm('确定代理商收益划转到账?', function (index) {

                        layer.close(index);
                        $.ajax({
                            url: '/agent/jie/wallet_out/done',
                            type: 'post',
                            dataType: 'json',
                            data: {
                                id: data.id
                            },
                            success: function (res) {
                                console.log(res);
                                layer.msg(res.msg, {
                                    icon: 5
                                });

                                if (res.code === 1) {
                                    layer.close(index);
                                    window.location.reload();
                                }

                            }

                        });
                    });
                    return false;

                }
            });


        });
    </script>

@endsection

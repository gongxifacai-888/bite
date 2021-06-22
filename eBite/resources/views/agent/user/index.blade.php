@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">开始日期</label>
                        <div class="layui-input-block">
                            <input type="text" name="start" id="datestart" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结束日期</label>
                        <div class="layui-input-block">
                            <input type="text" name="end" id="dateend" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">ID</label>
                        <div class="layui-input-block">
                            <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">

                    <div class="layui-inline">
                        <label class="layui-form-label">uid</label>
                        <div class="layui-input-block">
                            <input type="text" name="uid" placeholder="请输入" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">手机号</label>
                        <div class="layui-input-block">
                            <input type="text" name="mobile" placeholder="请输入" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">邮箱</label>
                        <div class="layui-input-block">
                            <input type="text" name="email" placeholder="请输入" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="san-user-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                        <!-- <button class="layui-btn layuiadmin-btn-useradmin"  onclick="javascript:window.location.href='/order/users_excel'">导出Excel</button> -->
                        <!-- <button class="layui-btn layui-btn-normal dao" lay-event="excel">导出表格</button> -->
                    </div>
                </div>
            </div>
            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <ul class="layui-row layui-col-space10 layui-this">
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('总用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总用户数：</h3>
                                <p><cite style="color:#fff" id="_num">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>代理商用户数</h3>
                                <p><cite style="color:#fff" id="_daili">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总入金</h3>
                                <p><cite style="color:#fff" id="_ru">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总出金</h3>
                                <p><cite style="color:#fff" id="_chu">0</cite></p>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>


            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <table id="san-user-manage" lay-filter="san-user-manage"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/html" id="table-tool">
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="wallet_info">查看资金</a>
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="order">查看订单</a>
    </script>


<script>
    layui.use(['index','laydate','form','table'], function () {
            var $ = layui.$
                ,admin = layui.admin
                , table = layui.table
                , layer = layui.layer
                , laydate = layui.laydate
                , form = layui.form;


            //日期
            laydate.render({
                elem: '#datestart'
            });
            laydate.render({
                elem: '#dateend'
            });

            var parent_id = {{ $parent_id }};
            console.log(parent_id);

            $.ajax({
                type: "POST",
                url: "{{URL('/agent/user/get_user_num')}}",
                dataType : "json",
                data : {all : 1 , parent_id : parent_id},
                success: function(result) {
                console.log(result);
                if (result.code == 1) {
                        $("#_num").html(result.data._num);
                        $("#_daili").html(result.data._daili);
                        $("#_ru").html(result.data._ru);
                        $("#_chu").html(result.data._chu);
                } else {
                        alert(result.msg)
                }
                }
            });


        load(parent_id);
        function load(parent_id) {
            parent_id = parent_id || 0;

            table.render({
                elem: '#san-user-manage'
                , url: '/agent/user/lists?parent_id=' + parent_id //模拟接口
                , cols: [[
                    {type: 'checkbox', fixed: 'left'}
                    , {field: 'id', width: 50, title: 'ID', sort: true}
                    , {field: 'uid', width: 150, title: 'UID'}
                    , {field: 'mobile', title: '手机号', minWidth: 150}
                    , {field: 'email', title: '邮箱', minWidth: 150}
                    , {field: 'my_agent_level', title: '用户身份' , width : 120}
                    , {field: 'card_id', title: '身份证号' , width : 180}
                    , {field: 'parent_agent_name', title: '上级代理商' , width : 120}
                    , {field: 'invite_code', title: '邀请码', minWidth: 150}
                    , {field: 'created_at', title: '加入时间', sort: true, width: 170}
                    , {title: '操作', width: 200, align: 'center', fixed: 'right', toolbar: '#table-tool'}
                ]]
                , page: true
                , limit: 30
                , toolbar:true
                , height: 'full-320'
                , text: '对不起，加载出现异常！'
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    //console.log(res);
                    if (res.code === 0) {

                    }else{

                    }
                }
            });
        }


        table.on('tool(san-user-manage)', function (obj) {
            var event = obj.event;
            var data = obj.data;

            if (event == 'order') {
                //查看订单

                layer.open({
                        title: '查看合约订单'
                        , type: 2
                        , content: '{{url('/agent/user/lever_order')}}?id=' + data.id
                        , maxmin: true
                        ,area: ['1000px', '600px']
                    });
            }
            if (event == 'wallet_info') {
                //查看资金

                layer.open({
                        title: '查看资金'
                        , type: 2
                        , content: '{{url('/agent/user/users_wallet')}}?id=' + data.id
                        // , maxmin: true
                        ,area: ['800px', '600px']
                    });


            }

            if (event == 'son') {
                load(data.id);
            }
        });


        form.render(null, 'layadmin-userfront-formlist');

        //监听搜索
        form.on('submit(san-user-search)', function (data) {
            var field = data.field;


            $.ajax({
                type: "POST",
                url: "{{URL('/agent/user/get_user_num')}}",
                dataType : "json",
                data :field,
                success: function(result) {
                console.log(result);
                if (result.code == 1) {
                        $("#_num").html(result.data._num);
                        $("#_daili").html(result.data._daili);
                        $("#_ru").html(result.data._ru);
                        $("#_chu").html(result.data._chu);
                } else {
                        alert(result.msg)
                }
                }
            });

            //执行重载
            table.reload('san-user-manage', {
                where: field
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    if (res.code === 1) {
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            });
        });

        //导出表格
        $('.dao').click(function () {
            var id = $('input[name="id"]').val();
            var account_number = $('input[name="account_number"]').val();
            var start = $('input[name="start"]').val();
            var end = $('input[name="end"]').val();

            var url='/agent/user/users_excel?id='+id+'&account_number='+account_number+'&start='+start+'&end='+end;
            window.open(url);

        })

    });
</script>

@endsection

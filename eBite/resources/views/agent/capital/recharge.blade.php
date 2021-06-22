
@extends('agent.layadmin')

@section('title', '充币列表')

@section('page-head')

@endsection

@section('page-content')

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
            <div class="layui-form-item">
                
                <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="currency_id" >
                                <option value="-1" class="ww">全部</option>
                                @foreach ($legal_currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->code}}</option>
                                @endforeach
                            </select>
                        </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">uid</label>
                    <div class="layui-input-block">
                        <input type="text" name="uid" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">手机</label>
                    <div class="layui-input-block">
                        <input type="text" name="mobile" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">邮箱</label>
                    <div class="layui-input-block">
                        <input type="text" name="email" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">所属代理</label>
                    <div class="layui-input-block" style="width:130px;">
                        <select name="belong_agent" >
                            <option value="" >全部</option>
                            @foreach ($son_agents as $son)
                                <option value="{{$son->username}}">{{$son->username}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="layui-card-body">
            <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>
            
        </div>
        <script type="text/html" id="mobile">
        @{{d.user.mobile}}
        </script>
        <script type="text/html" id="uid">
        @{{d.user.uid}}
        </script>
        <script type="text/html" id="email">
        @{{d.user.email}}
        </script>
    </div>
</div>
@endsection

@section('scripts')
<script>
    layui.use(['index','table' , 'layer'], function() {
        var $ = layui.$
            ,admin = layui.admin
            ,view = layui.view
            ,table = layui.table
            ,form = layui.form
            
       
     
        
        //充币管理
        table.render({
            elem: '#LAY-user-manage'
            ,method : 'get'
            ,url: '/agent/money/recharge'
            ,toolbar: true
            ,totalRow: true
            ,cols: [[
                {type: 'checkbox', fixed: 'left'}
                ,{field: 'id', width: 150, title: 'ID', sort: true }
                ,{field: 'currency_name', title: '币种', width: 90}
                ,{field: 'mobile', title: '手机号', width: 120 ,templet: '#mobile'}
                ,{field: 'uid', title: 'uid', width: 120,templet: '#uid'}
                ,{field: 'email', title: '邮箱', width: 120,templet: '#email'}
                ,{field: 'belong_agent_name', title: '所属代理', width: 120}
                ,{field: 'value', title: '充币数量', width: 150, totalRow: true}
                ,{field: 'memo', title: '说明', width: 200}
                ,{field: 'created_at', title: '到账时间', width: 170}
            ]]
            ,page: true
            ,limit: 30
            ,height: 'full-240'
            ,text: '对不起，加载出现异常！'
           
            ,done: function(res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
               
            }
        });

        form.render(null, 'layadmin-userfront-formlist');

        //监听搜索
        form.on('submit(LAY-user-front-search)', function(data){
            var field = data.field;

            //执行重载
            table.reload('LAY-user-manage', {
                where: field
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    if (res.code === 1){
                        layer.msg(res.msg ,{icon : 5});
                    }
                }
            });
        });

    });
</script>
@endsection

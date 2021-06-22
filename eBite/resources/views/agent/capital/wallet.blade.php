@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')
    <!-- <div class="layui-form"> -->
        <table id="userlist" lay-filter="userlist">
            <input type="hidden" name="user_id" value="{{$user_id}}">
        </table>


@endsection

@section('scripts')
    <script>
        window.onload = function() {

            layui.use(['element', 'form', 'layer', 'table'], function () {
                var element = layui.element;
                var layer = layui.layer;
                var table = layui.table;
                var $ = layui.$;
                var form = layui.form;

                function tbRend(url) {
                    table.render({
                        elem: '#userlist'
                        ,url: url
                        ,page: true
                        ,limit: 20
                        ,toolbar: true
                        ,totalRow: true
                        ,height: 'full-100'
                        ,cols: [[
                                {field: 'id', title: '币种id', width: 70}
                                ,{field: 'code', title: 'code', width: 100}
                                ,{field: '_ru', title: '入金', width: 150, totalRow: true}
                                ,{field: '_chu', title: '出金', width: 150, totalRow: true}
                                ,{field: '_caution_money', title: '可用保证金', width: 150, totalRow: true}
                                ,{field: '_change_money', title: '币币账户余额', width: 150, totalRow: true}
                                ,{field: '_micro_money', title: '期权账户余额', width: 150, totalRow: true}
                                ,{field: '_lever_money', title: '合约账户余额', width: 150, totalRow: true}
                        ]]
                    });
                }
                var user_id = $("input[name='user_id']").val();
                tbRend("{{url('/agent/user/users_wallet_total')}}?user_id=" + user_id);

            });
        }
    </script>

@endsection

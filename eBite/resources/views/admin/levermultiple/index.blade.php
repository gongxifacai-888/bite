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
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <button class="layui-btn" id="add_admin">添加</button>
        </div>
        <div class="layui-card-body">
            <table id="userlist" lay-filter="userlist"></table>
            <script type="text/html" id="barDemo">

                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-dangser layui-btn-xs" lay-event="delete">删除</a>
            </script>
        </div>
    </div>
</div>
<script src="/layuiadmin/layui/layui.js"></script>
<script>
    window.onload = function() {
        document.onkeydown=function(event){
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if(e && e.keyCode==13){ // enter 键
                $('#mobile_search').click();
            }
        };
        layui.use(['element', 'form', 'layer', 'table'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var table = layui.table;
            var $ = layui.$;
            var form = layui.form;
            form.on('submit(mobile_search)',function(obj){
                var account =  $("input[name='account']").val();

                tbRend("{{url('/admin/lever_multiple/list')}}?account="+account);
                return false;
            });
            $('#add_admin').click(function() {
                var index = layer.open({
                    title: '添加'
                    ,type: 2
                    ,content: '{{url('/admin/lever_multiple/add')}}'
                    ,area: ['800px', '600px']
                });
            });
            function tbRend(url) {
                table.render({
                    elem: '#userlist'
                    , url: url
                    , page: true
                    ,limit: 20
                    , cols: [[
                        { field: 'id', title: 'ID', width: 100}
                        ,{field:'type',title:'类型', width:100}
                        ,{field:'value',title:'数值', width:150}
                        ,{fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barDemo'}
                    ]]
                });
            }
            tbRend("{{url('/admin/lever_multiple/list')}}");


            //监听工具条
            table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                if (layEvent === 'delete') { //删除
                    layer.confirm('真的要删除吗？', function (index) {
                        //向服务端发送删除指令
                        $.ajax({
                            url: "{{url('admin/lever_multiple/delete')}}",
                            type: 'get',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.code == 1) {
                                    layer.msg(res.msg);
                                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                    layer.close(index);
                                } else {
                                    layer.close(index);
                                    layer.alert(res.msg);
                                }
                            }
                        });
                    });
                }else if (layEvent === 'edit'){ //编辑
                    var index = layer.open({
                        title: '编辑'
                        ,type: 2
                        ,content: '{{url('/admin/lever_multiple/edit')}}?id=' + data.id
                        ,area: ['800px', '600px'],
                    });
                }
            });
        });
    }
</script>
</body>
</html>

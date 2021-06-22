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
        .div_of_children .layui-input-block{
            border-width: 1px;
            border-style: solid;
            border-radius: 2px;
            border-color: #e6e6e6;
        }
        .div_of_children .layui-form-item{
            margin-left: 20px;
        }
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">添加表单组</div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin">
                <div class="layui-form-item">
                    <label class="layui-form-label">组名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name"  placeholder="请输入组名称"
                               class="layui-input">
                    </div>
                </div>
                <div id="view"></div>
                <script id="div_of_field_tpl" type="text/html">
                    <div class="layui-form-item div_of_children" id="div_of_field">
                        <label class="layui-form-label">组内字段</label>
                        <div class="layui-input-block">
                            <div class="layui-form-item">
                                <button type="button" class="layui-btn" lay-submit lay-filter="add_field">添加字段</button>
                            </div>

                        </div>
                    </div>
                </script>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit lay-filter="submit">立即提交</button>
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
    }).use(['index', 'form', 'upload'], function () {
        var $ = layui.$
            , form = layui.form
            , laytpl = layui.laytpl

        var addable_arr = [];
        var added_arr = [];
        var max_attribute_id = 0;
        reload_div_of_field();//加载字段添加框


        //删除字段监听
        form.on('submit(del_field)', function (data) {
            var othis = $(data.elem);
            var div_parent = othis.parent();
            div_parent.remove();
            return false;
        })

        //重载字段添加框
        function reload_div_of_field(){
            $.get('/admin/field/list', {limit:100}, function (res) {
                if(res.code == 0){
                    addable_arr = res.data;
                }else{
                    layer.msg('获取字段列表失败');
                }
            });

            var getTpl = div_of_field_tpl.innerHTML
                ,view = document.getElementById('view');
            laytpl(getTpl).render(addable_arr, function(html){
                view.innerHTML = html;
            });
        }

        //添加字段监听
        form.on('submit(add_field)', function (data) {
            var othis = $(data.elem);
            var this_index = ++max_attribute_id;
            var div_parent = othis.parent().parent();
            var options = '<option value="0"></option>';
            for (x in addable_arr) {
                options += `<option value="${addable_arr[x]['id']}">${addable_arr[x]['field_name']}</option>`;
            }
            var new_element = `<div class="layui-form-item">
                            <div class="layui-input-inline" style="width: 150px;">
                                <select name="field[${this_index}]" lay-search lay-filter="select_field" id="select_field">${options}</select>
                            </div>
                            <button type="button" class="layui-btn layui-btn-danger" lay-submit lay-filter="del_field">删除</button>
                        </div>`;
            div_parent.append(new_element);
            form.render('', 'layuiadmin-form-useradmin');//动态生成的表单元素需要重新渲染
            return false;
        })

        //监听字段下拉
        form.on('select(select_field)', function (data) {
            var othis = data.othis; //得到美化后的DOM对象
            var value = element = data.value;//当前选中的值
            var old_value = $(data.elem).data('old_value');//改变前的值
            $(data.elem).data('old_value',value)//给元素附加上值
            //console.log(value)
            //console.log(old_value)
            var removeId = value;
            var addId = old_value;
            //这一块作用是，不允许重复选择字段
            //console.log($("#div_of_attribute select"))

            //移除可添加名单数组
            addable_arr = $.grep(addable_arr, function(value) {
                //添加到已添加名单数组
                if(value.id == removeId){
                    added_arr.push(value);
                }
                return value.id != removeId;
            });

            //如果是切换select，还需将 原选项 从已添加名单数组中移除，并添加到可添加名单数组
            if(old_value){
                added_arr = $.grep(added_arr, function(value) {
                    //添加到已添加名单数组
                    if(value.id == addId){
                        addable_arr.push(value);
                    }
                    return value.id != addId;
                });
            }

            //console.log(addable_arr);
            //console.log(added_arr);

            $("#div_of_field select").each(function (k,item) {
                var this_item_val = $(item).val()//select 选中的值
                var this_item_selected = $(item).find("option:selected");//select选中的元素
                var selected_text = this_item_selected.text()//选中元素的text值
                //console.log(this_item_val);
                //console.log(selected_text);
                if(this_item_val){
                    var this_options =  `<option value="${this_item_val}" selected>${selected_text}</option>`;
                }else{
                    var this_options = '';
                }
                for (x in addable_arr) {
                    this_options += `<option value="${addable_arr[x]['id']}">${addable_arr[x]['field_name']}</option>`;
                }
                //console.log(this_options)
                $(item).html(this_options);
                //$("#div_of_attribute select").eq(k).html(this_options)
            });
            form.render('select', 'layuiadmin-form-useradmin');//动态生成的表单元素需要重新渲染
            return false;
        })

        //监听表单提交操作
        form.on('submit(submit)', function (data) {
            $.post('/admin/model_group/save', data.field, function (res) {
                layer.msg(res.msg);
            });
            return false;
        })



    })
</script>
</body>
</html>
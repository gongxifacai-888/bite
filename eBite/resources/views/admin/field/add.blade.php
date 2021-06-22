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
        .layui-form-label{
            width: 150px;
        }
        .layui-input-block {
            margin-left: 180px;
        }
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
                    <label class="layui-form-label">字段名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" lay-verify="name" placeholder="请输入字段名称"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">字段标签</label>
                    <div class="layui-input-inline">
                        <select name="element" lay-filter="select_elements" id="select_elements">
                            @foreach($elements as $element)
                                <option value="{{$element}}" >{{$element}}</option>
                                @endforeach
                        </select>
                    </div>
                    <div class="layui-form-mid layui-word-aux">选择字段的标签类型</div>
                </div>
                <div class="layui-form-item" id="if_may_has_children" hidden>
                    <label class="layui-form-label">是否有子元素</label>
                    <div class="layui-input-inline">
                        <input type="checkbox" id="has_children" name="has_children" lay-skin="switch" lay-filter="if_has_children" lay-text="是|否">
                    </div>
                    <div class="layui-form-mid layui-word-aux">单选，多选，下拉都属于拥有子元素的标签</div>
                </div>

                <div id="children_view"></div>
                <div id="view"></div>
                <script id="div_of_attribute_tpl" type="text/html">
                    <div class="layui-form-item div_of_children" id="div_of_attribute">
                        <label class="layui-form-label">标签属性</label>
                        <div class="layui-input-block">
                            <div class="layui-form-item">
                                <button type="button" class="layui-btn" lay-submit lay-filter="add_attribute">添加属性</button>
                            </div>

                        </div>
                    </div>
                </script>
                <script id="div_of_children_tpl" type="text/html">
                    <div class="layui-form-item div_of_children" id="div_of_children">
                        <label class="layui-form-label">子元素</label>
                        <div class="layui-input-block">
                            <div class="layui-form-item">
                                <button type="button" class="layui-btn" lay-submit lay-filter="add_element">添加元素</button>
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
    }).use(['index', 'form', 'upload','laytpl'], function () {
        var laytpl = layui.laytpl;
        var $ = layui.$
            , form = layui.form

        var addable_arr = [];//可选择的字段属性
        var added_arr = [];//已添加的字段属性
        var element = '';//字段元素
        var max_children_id = 0;//当前最大子元素数量
        var max_attribute_id = 0;//当前最大属性数量s

        check_if_may_has_children();//对默认下拉元素的检查
        reload_div_of_attribute();//重载【添加属性框】
        reload_div_of_children(false); //重载【添加子元素框】
        // 检查用户是否操作了 【有子元素的】按钮
        form.on('switch(if_has_children)', function (data) {
            var value = data.elem.checked
            var div_of_children = $("#div_of_children")
            if(value == true){
                reload_div_of_children(true);
            }else{
                reload_div_of_children(false);
            }
            return false;
        })

        //监听选择标签
        form.on('select(select_elements)', function (data) {
            var value = element = data.value
            var if_may_has_children = $("#if_may_has_children")
            var div_of_children = $("#div_of_children")
            var arr = ['input', 'select'];//拥有子元素的标签
            var index = arr.indexOf(value);
            if(index != -1){//是数组中的值
                if_may_has_children.show();//显示【是否有子元素开关】
                var is_checked = check_has_children_input_value();//检查【是否有子元素开关】的状态
                if(is_checked ==false){
                    //关的话，隐藏【添加子元素框】
                    div_of_children.hide();
                    reload_div_of_children(false);
                }else{
                    //否则打开
                    reload_div_of_children(true);
                }
            }else{
                if_may_has_children.hide();//隐藏【是否有子元素开关】
                reload_div_of_children(false);//隐藏【添加子元素框】
            }

            //对于下拉菜单的变化，需要重载【添加属性框】
            reload_div_of_attribute()
            return false;
        })

        //监听属性下拉
        form.on('select(select_property)', function (data) {
            var othis = data.othis; //得到美化后的DOM对象
            var value = element = data.value;//当前选中的值
            var old_value = $(data.elem).data('old_value');//改变前的值
            $(data.elem).data('old_value',value)
            //console.log(value)
            //console.log(old_value)
            var removeId = value;
            var addId = old_value;
            //这一块作用是，不允许重复选择属性
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
            $("#div_of_attribute select").each(function (k,item) {
                var this_item_val = $(item).val()
                var this_item_selected = $(item).find("option:selected");
                var selected_text = this_item_selected.text()
                if(this_item_val){
                    var this_options =  `<option value="${this_item_val}" selected>${selected_text}</option>`;
                }else{
                    var this_options = '';
                }
                for (x in addable_arr) {
                    this_options += `<option value="${addable_arr[x]['id']}">${addable_arr[x]['property_name']}</option>`;
                }
                //console.log(this_options)
                $(item).html(this_options);
                //$("#div_of_attribute select").eq(k).html(this_options)
            });
            form.render('select', 'layuiadmin-form-useradmin');//动态生成的表单元素需要重新渲染
            return false;
        })


        //对默认下拉元素的检查
        function  check_if_may_has_children() {
            var select_elements = $("#select_elements")
            var if_may_has_children = $("#if_may_has_children")
            var value = select_elements.val()
            element = value;
            //console.log(value);
            var arr = ['input', 'select'];
            var index = arr.indexOf(value);
            if(index != -1){
                if_may_has_children.show();
            }else{
                if_may_has_children.hide();
            }
            return false;
        }
        //检查【是否有子元素开关】状态
        function check_has_children_input_value(){
            var has_children = $("#has_children");
            var is_checked = has_children.is(':checked');
            return is_checked;
        }
        //提交保存
        form.on('submit(submit)', function (data) {
            $.post('/admin/field/save', data.field, function (res) {
                layer.msg(res.msg);
            });
            return false;
        })

        //删除元素监听
        form.on('submit(del_element)', function (data) {
            var othis = $(data.elem);
            var div_parent = othis.parent();
            div_parent.remove();
            return false;
        })

        //添加元素监听
        form.on('submit(add_element)', function (data) {
            var othis = $(data.elem);
            var this_index =  ++max_children_id;
            var div_parent = othis.parent().parent();
            var new_element = `<div class="layui-form-item">
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" name="children_value[${this_index}][real]" placeholder="实际值" autocomplete="off" class="layui-input">
                            </div>
                            <div class="layui-form-mid">-</div>
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" name="children_value[${this_index}][show]" placeholder="显示值" autocomplete="off" class="layui-input">
                            </div>
                            <button type="button" class="layui-btn layui-btn-danger" lay-submit lay-filter="del_element">删除</button>
                        </div>`;
            div_parent.append(new_element);
            form.render('', 'layuiadmin-form-useradmin');//动态生成的表单元素需要重新渲染
            return false;
        })

        //重载字段属性添加框
        function reload_div_of_attribute(){
            $.get('/admin/field_property/list', {element:element,limit:100}, function (res) {
                if(res.code == 0){
                    addable_arr = res.data;
                }else{
                    layer.msg('获取字段属性列表失败');
                }
            });

            var getTpl = div_of_attribute_tpl.innerHTML
                ,view = document.getElementById('view');
            laytpl(getTpl).render(addable_arr, function(html){
                view.innerHTML = html;
            });
        }

        //重载子元素添加框
        function reload_div_of_children(show){
            if (typeof(show) == "undefined" || show == true)
            {
                var getTpl = div_of_children_tpl.innerHTML
            }else{
                var getTpl = '';
            }

            var view = document.getElementById('children_view');
            laytpl(getTpl).render([], function(html){
                view.innerHTML = html;
            });
        }

        //删除属性监听
        form.on('submit(del_attribute)', function (data) {
            var othis = $(data.elem);
            var div_parent = othis.parent();
            div_parent.remove();
            return false;
        })

        //添加属性监听
        form.on('submit(add_attribute)', function (data) {
            var othis = $(data.elem);
            var this_index = ++max_attribute_id;
            var div_parent = othis.parent().parent();
            var options = '<option value="0"></option>';
            for (x in addable_arr) {
                options += `<option value="${addable_arr[x]['id']}">${addable_arr[x]['property_name']}</option>`;
            }
            var new_element = `<div class="layui-form-item">
                            <div class="layui-input-inline" style="width: 150px;">
                                <select name="property[${this_index}][option_id]" lay-search lay-filter="select_property" id="select_property">${options}</select>
                            </div>
                            <div class="layui-form-mid">：</div>
                            <div class="layui-input-inline" style="width: 500px;">
                                <input type="text" name="property[${this_index}][value]" placeholder="值" autocomplete="off" class="layui-input">
                            </div>
                            <button type="button" class="layui-btn layui-btn-danger" lay-submit lay-filter="del_element">删除</button>
                        </div>`;
            div_parent.append(new_element);
            form.render('', 'layuiadmin-form-useradmin');//动态生成的表单元素需要重新渲染
            return false;
        })
    })
</script>
</body>
</html>
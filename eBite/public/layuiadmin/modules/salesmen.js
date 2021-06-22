/**

 @Name：layuiAdmin 代理商管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL

 */


layui.define(['table', 'form'], function(exports){
    var $ = layui.$
        ,admin = layui.admin
        ,view = layui.view
        ,table = layui.table
        ,form = layui.form;

    //代理商管理
    table.render({
        elem: '#LAY-user-manage'
        ,url: '/agent/salesmen/lists' //模拟接口
        ,cols: [[
            {type: 'checkbox', fixed: 'left'}
            ,{field: 'id', width: 60, title: 'ID', sort: true }
            ,{field: 'username', title: '用户名', minWidth: 150 , event : "getsons",style:"color: #fff;background-color: #5FB878;"}
            ,{field: 'parent_agent_name', title: '上级用户名', width: 120}
            ,{field: 'agent_name', title: '用户等级', width: 100}
            ,{field: 'is_lock', title: '是否锁定', width: 90, templet: '#lockTpl'}
            ,{field: 'is_addson', title: '是否拉新', width: 90, templet: '#addsonTpl'}
            ,{field: 'pro_loss', title: '头寸比例(%)', width: 120}
            ,{field: 'pro_ser', title: '手续费比例(%)', width: 120}
            ,{field: 'reg_time', title: '加入时间', sort: true, width: 170}
            ,{field: 'lock_time', title: '锁定时间', sort: true, width: 170}
            ,{title: '操作', width: 450, align:'center', fixed: 'right', toolbar: '#table-tool'}
        ]]
        ,page: true
        ,limit: 30
        ,height: 'full-320'
        ,text: '对不起，加载出现异常！'

        ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

        }
    });

    //监听工具条
    table.on('tool(LAY-user-manage)', function(obj){
        var data = obj.data;
        if(obj.event === 'getsons'){

            //执行重载
            table.reload('LAY-user-manage', {
                where: {
                    parent_agent_id : data.id
                }
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                }
            });
        } else if(obj.event === 'edit'){
            console.log(data);
            delete data.reg_time;
            layer.show('编辑代理商', '/agent/salesmen/add', data);
        }else if(obj.event === 'lock'){
            var value = 0;
            if (data.is_lock == 1) {
                value = 0;
            }else{
                value = 1;
            }
            admin.req( {
                type : "POST",
                url : '/agent/salesmen/update',
                dataType : "json",
                data : {name:'is_lock',value:value , agentid : data.id},
                done : function(result) { //返回数据根据结果进行相应的处理
                    layui.layer.msg(result.msg, {icon: 6 });
                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                    layui.table.reload('LAY-user-manage' , {

                        done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                            if (res !== 0 ){
                                if (res.code === 1001) {
                                    //清空本地记录的 token，并跳转到登入页
                                    admin.exit();
                                }
                            }
                        }
                    }); //重载表格
                }
            });
        }else if(obj.event === 'addson'){
            var value = 0;
            if (data.is_addson == 1) {
                value = 0;
            }else{
                value = 1;
            }
            admin.req( {
                type : "POST",
                url : '/agent/salesmen/update',
                dataType : "json",
                data : {name:'is_addson',value:value , agentid : data.id},
                done : function(result) { //返回数据根据结果进行相应的处理
                    layer.msg(result.msg, {icon: 6 });
                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                    layui.table.reload('LAY-user-manage'); //重载表格
                    layer.close(index); //执行关闭
                }
            });
        }else if (obj.event == 'this-sons'){
            console.log(obj)
            var parent_agent_id = obj.data.id
            parent.layui.index.openTabsPage("/agent/user/index?parent_id="+parent_agent_id, '代理商会员');
        }else if (obj.event == 'addsonagent') {
            layer.prompt({title: '请输入下级代理商帐号', formType: 0, btn :['查询该用户' , '取消']}, function(value, index){
                layer.close(index);
                if (value.length == 0) {
                    layer.msg('用户名不能位空', {icon: 5 });
                }else{

                    $.ajax({
                        type: "POST",
                        url: '/agent/salesmen/search_agent_son',
                        dataType : "json",
                        data : {username : value , id : data.id},
                        success: function(result) {
                            //console.log(result);
                            if (result.code == 1) {
                                result.data.agent_id = data.id;
                                layer.show('添加代理商', '/agent/salesmen/add', result.data);

                            } else {
                                layer.msg(result.msg)
                            }
                        }
                    });

                }
            });
        }
    });


    exports('salesmen', {})
});

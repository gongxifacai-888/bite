/**

 @Name：layuiAdmin 公共业务
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：LPPL
    
 */
 
layui.define(function(exports){
  var $ = layui.$
  ,layer = layui.layer
  ,laytpl = layui.laytpl
  ,setter = layui.setter
  ,view = layui.view
  ,admin = layui.admin
  
  //公共业务的逻辑处理可以写在此处，切换任何页面都会执行
  //……
  
  /*弹出层*/
    /*
     参数解释：
     title	标题
     url		请求的url
     id		需要操作的数据id
     w		弹出层宽度（缺省调默认值）
     h		弹出层高度（缺省调默认值）
     */
    layer.show = function (title, url, data, w, h, full) {
        if (title == null || title == '') {
            title = false;
        }

        if (url == null || url == '') {
            url = "404.html";
        }
        var add_str = '';
        if(data){
            if(url.indexOf('?') != -1){
                add_str = '&';
            }else{
                add_str = '?';
            }
            for (i in data){
                add_str += i+'='+data[i]+'&';
            }
        }
        //console.log(add_str);
        if (w == null || w == '') {
            w = 800;
        }

        if (h == null || h == '') {
            h = ($(window).height() - 50);
        }

        if (full == null || full == '') {
            full = false;
        } else {
            full = true;
        }
        if (full == true) {
            var index = layer.open({
                type: 2,
                title: title,
                content: url,
            });
            setTimeout(function () {
                layer.full(index);
            }, 100)
        } else {
            layer.open({
                type: 2,
                area: [w + 'px', h + 'px'],
                fix: false, //不固定
                maxmin: true,
                shade: 0.4,
                title: title,
                content: url+add_str,
                offset: '10px',
            });
        }
    }
  
  
  //退出
  admin.events.logout = function(){
    //执行退出接口
    admin.req({
      url: layui.setter.base + 'json/user/logout.js'
      ,type: 'get'
      ,data: {}
      ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
        
        //清空本地记录的 token，并跳转到登入页
        admin.exit(function(){
          location.href = 'user/login.html';
        });
      }
    });
  };

  //代理商退出

  admin.events.agent_logout = function(){
    //执行退出接口
    
      $.ajax({
        url: '/agent/logout',
        type: 'post',
        dataType: 'json',
        data: {},
        success: function(res) {
         
          if (res.code == 1) {
            //登入成功的提示与跳转
            layer.msg(res.msg, {
              icon: 1,
              time: 2000
            });
            location.href = '/agent';
          } else {
            layer.msg(res.msg, {
              icon: 5
            });
          }


        }

      });
  };


  
  //对外暴露的接口
  exports('common', {});
});
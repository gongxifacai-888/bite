class Socket{
    constructor(url){
        this.url=url;
        this.methods={};
        this.socket={}
        this.reconnect_time='30000'
        this.connect_inter=null;
        this.open();
        return this;
    }
    on(method,fn){
        this.methods[method]=fn;
    }
    open(){
        var that=this;
        that.socket=new WebSocket(that.url)
        that.socket.onopen=function(){
            clearInterval(that.connect_inter);
            console.log('socket 打开了')
        //    that.hasConnected()
        }
        that.socket.onmessage=function(msg){
            var data=JSON.parse(msg.data);
            // console.log(data);
            if(that.methods[data.type]){
                that.methods[data.type](data);
            }
        }
    }
    connected(fn) {
        if (this.socket.readyState === 1) {//成功
          fn(this);
          this.sendPing()
        }else{
          setTimeout(()=>{
            console.log('等待连接')
            this.connected(fn);
          },100)
        }
    }
    // connected2(){
    //     var that=this;
    //     return new Promise((resolve,reject)=>{
    //         if (this.socket.readyState === 1) {
    //             retsolve()
    //         }else{
    //             reject()
    //             console.log('等待连接')
    //             that.reconnect();
    //         }
    //     })
    // }
    send(infos){
        console.log(infos.constructor);
        if(infos.constructor==Array){
            console.log(infos);
            for (var i in infos) {
                this.socket.send(JSON.stringify({
                  "type": infos[i].type,
                  params: infos[i].params
                }))
            }
        }else if(infos.constructor==String){
            console.log(infos);
            this.socket.send(infos)
        }else{
            console.log(infos);
            this.socket.send(JSON.stringify(infos))
        }
    }
    close(){
        var that=this;
        this.socket.onclose=function(){
            this.close();
            console.log('关闭连接')
            // that.reconnect();
        }
    }
    reconnect(){
        clearInterval(this.connect_inter);
        this.connect_inter=setInterval(() => {
            this.open()
        }, this.reconnect_time);
    }
    /**发送心跳 */
    sendPing() {
        this.socket.send('ping')
        this.connect_inter = setInterval(() => {
            this.socket.send('ping')
        }, 5000)
    }
   
}
// var socket=new WebSocket('ws://m.mddz.cc/ws')
let protocol = location.protocol === 'https:' ? 'wss://m.mddz.cc/ws' : 'ws://m.mddz.cc/ws'
var socket=new Socket(protocol)
export default socket
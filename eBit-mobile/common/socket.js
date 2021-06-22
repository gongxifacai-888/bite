var socketOpen = false;
var emitMsg,onMsg

function sendSocketMessage(msg) {
  if (socketOpen) {
	console.log(msg);
	for(var i in msg){
		uni.sendSocketMessage({
		  data: JSON.stringify(msg[i])
		});
	}
  }
}

function reciveSocketMessage(callback){
	if (socketOpen) {
		uni.onSocketMessage(function (res) {
		  // console.log('收到服务器内容：' + res.data);
		  callback&&callback(res.data);
		});
	}
}
function listenFun(sendMsg=[],callback){
	if(!socketOpen){
		uni.connectSocket({
		  url: 'ws://m.mddz.cc/ws'
		});
	}
	uni.onSocketOpen(function (res) {
	  socketOpen = true;
	  console.log(socketOpen);
	  if(sendMsg.length>0){
		  sendSocketMessage(sendMsg);
	  }
	  reciveSocketMessage(callback);
	});
}
function closeSocket(){
	uni.closeSocket();
	socketOpen=false;
	uni.onSocketClose(function (res) {
	  console.log('WebSocket 已关闭！');
	  socketOpen=false;
	});
}
export default {
	listenFun,
	closeSocket,
}
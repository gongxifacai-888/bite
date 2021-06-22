class Socket {

	constructor(url) {
		/**断线重连interval
		 *
		 * @type {null}
		 */
		this.reconnect_interval = null;

		/**断线重连周期
		 *
		 * @type {number}
		 */
		this.reconnect_time = 1000;

		/**
		 * @var int ping_interval 心跳包时间
		 */
		this.ping_time = 30000;

		/**ping interval
		 * @var int
		 */
		this.ping_interval = null;

		/**
		 * @var Object socketInstance socket实例
		 */
		this.socketInstance = {};

		/**
		 * @type Object on的 所有回调事件
		 */
		this.methods = {};

		this.url = url;
		this.datas = '';
		// this.emits ={};

		this.connect();

		return this;
	}

	on(method, fn) {
		this.datas = method;
		// var datas = method.params;
		// var data1 = datas.split('.');
		
		this.methods[method.type] = fn;
	}

	emit(datas) {
		let send = '';
		send = JSON.stringify(datas);
		this.socketInstance.send({
			data:send
		});
	}
	emits(data) {
		let send = '';
		send = JSON.stringify(data);

		// send = JSON.stringify(send);
		this.socketInstance.send({
			data:send
		});
	}


	connect() {
		//打开连接
		this.socketInstance = uni.connectSocket({
			url: this.url,
			complete: () => {}
		});

		//连接成功回调
		this.socketInstance.onOpen(() => {
			clearInterval(this.reconnect_interval);
			console.log('socket连接成功');
			this.emit('login');
			this.on('login', res => {
				uni.setStorageSync('socketClientId', res.data.client_id)
			})
		});

		//收到消息时的回调
		this.socketInstance.onMessage(res => {
			let data = JSON.parse(res.data);
			//console.log(this.methods);
			// console.log(this.methods[data.type])
			if (this.methods[data.type]) {
				this.methods[data.type](data);
			// 	console.log(this.methods[data.type](data))
			} else {
				console.log(this.methods[data.type])
			}
		});
		
		// uni.onSocketOpen(function (res) {
		//   var datas = '{"event": "sub", "params": "market_depth.BTC/USDT"}'
		//   uni.sendSocketMessage({
		//     data: datas
		//   },res=>{
		//   	console.log(res)
		//   });
		// });
		//连接关闭回调
		this.socketInstance.onClose(() => {
			this.reconnect();
			console.log("socket连接已关闭");
		});

		//开始心跳
		clearInterval(this.ping_interval);
		this.ping_interval = setInterval(() => {
			this.emit('ping')
		}, this.ping_time);
	}

	reconnect() {
		clearInterval(this.reconnect_interval);

		this.reconnect_interval = setInterval(() => {
			this.connect();
			console.log("socket尝试重连");
		}, 1000);
	};

	close() {
		this.socketInstance.close();
	}

}

const socket = new Socket('ws://m.mddz.cc/ws')

export default socket

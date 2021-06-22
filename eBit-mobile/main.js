import Vue from 'vue'
import App from './App'
import utils from '@/common/helper'
import MD5 from '@/common/md5.js'
import store from '@/store/store.js'
import i18n from '@/common/lang/lang.js'
import Socket from '@/common/socket.js'

Vue.config.productionTip = false
App.mpType = 'app'
Vue.prototype.$store = store
Vue.prototype.$utils=utils;
Vue.prototype.$MD5=MD5;
Vue.prototype._i18n = i18n
Vue.prototype.$socket=Socket;
Vue.filter('filterDecimals',(value,number=2)=>{
	let val=value-0;
	let num=number-0;
	let base='1';
	let decimal=base.padEnd(num+1,0)-0;
	let result=utils.accMul(val,decimal);
	return (Math.floor(result)/decimal).toFixed(num);
})
const app = new Vue({
    ...App,
	i18n,
	store,
})
app.$mount()

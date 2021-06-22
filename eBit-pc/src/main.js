// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import Axios from './lib/helper.js'
import App from './App'
import router from './router'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
Vue.use(ElementUI)
import '@/assets/style/common.scss'
import utils from './lib/utils.js'
import socket from './lib/socket.js'
import store from './store/store.js'
import md5 from 'js-md5';
import i18n from './lang/lang'
var urls;
if (process.env.NODE_ENV == 'development') {
  urls = "http://m.mddz.cc"
} else {
  var _PROTOCOL = window.location.protocol;
  var _HOST = window.location.host;
  var _DOMAIN = _PROTOCOL + '//' + _HOST;
  urls = _DOMAIN;
}
Vue.prototype.API = urls;
Vue.prototype.$http = Axios;
Vue.config.productionTip = false;
Vue.prototype.$utils = utils;
Vue.prototype.$socket=socket;
Vue.prototype.$md5 = md5;
Vue.filter('filterDecimals',(value,number=2)=>{
	let val=value-0;
	let num=number-0;
	let base='1';
	let decimal=base.padEnd(num+1,0)-0;
	let result=utils.accMul(val,decimal);
	return (Math.floor(result)/decimal).toFixed(num);
})
window.eventBus = new Vue()
/* eslint-disable no-new */
new Vue({
  el: '#app',
  i18n,
  router,
  store,
  components: { App },
  template: '<App/>'
})

// let localUtils = {  //公共方法和属性
// 	phone_reg : /^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$/ig,
// 	trim : function (val) { return val.replace(/\s/g,'')},
// 	host:'http://www.bkcex.vip/',
// 	laravel_api: 'http://www.bkcex.vip/api/',
// 	node_api: 'http://47.75.197.189:3000/',
// 	socket_api: 'http://jnbadmin.mobile369.com:2120/',
// };
import Vue from 'vue'
import pako from 'pako'
import {
  Socket
} from 'dgram';

function checkMobile(phone) {
  // var reg=/^[1][0-9]{10}$/;
  var reg = /^[0-9]{6,}$/;
  return reg.test(phone);
}

function checkEmail(emial) {
  var reg = /^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/;
  return reg.test(emial);
}

function checkPassword(password) {
  var reg = /^[0-9a-zA-Z]{6,16}$/;
  return reg.test(password);
}

function layerMsg(msg, mytype = 'error') {
  Vue.prototype.$message({
    message: msg,
    type: mytype,
    duration:1000
  });
}
function theme(type){
  var head = document.querySelector('head');
  var link = document.querySelector('link#darkTheme');
  window.localStorage.setItem('theme', type);
  if(type=='light'){
    if(link == null || link == ''){
      return;
    }else{
      head.removeChild(link); 
    }
  }else{
    if(link == null || link == ''){
      link = document.createElement('link');
      link.id = 'darkTheme';
      link.rel = 'stylesheet';
      link.href = './static/css/dark.css';
      head.appendChild(link);
    }
  }
}

const accMul=(arg1, arg2) =>{
  var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
  try {
      m += s1.split(".")[1].length;
  }
  catch (e) {
  }
  try {
      m += s2.split(".")[1].length;
  }
  catch (e) {
  }
  return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
}
const filterDecimals=(value,number=2)=>{
  let val=value-0;
  let num=number-0;
  let base='1';
  let decimal=base.padEnd(num+1,0)-0;
  let result=accMul(val,decimal);
  return (Math.floor(result)/decimal).toFixed(num);
}
// 时间戳转时间

const formatTime = (timestamp) => {
  if((timestamp+'').length<13){
    timestamp=timestamp*1000
  }
  var date = new Date(timestamp)
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()
  return [year, month, day].map(formatNumber).join('-') + ' ' + [hour, minute, second].map(formatNumber).join(':')       
}
const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}

// gzip解压
function unzip(b64Data){
  // 加密：window.btoa()，解密：window.atob()
  var strData = atob(b64Data);
  // 字符串转数组 在循环返回一个 Unicode表所在位置的新数组
  var charData = strData.split('').map(function(x){return x.charCodeAt(0);});
  // Uint8Array 数组类型表示一个8位无符号整型数组，创建时内容被初始化为0。创建完后，可以以对象的方式或使用数组下标索引的方式引用数组中的元素。
  var binData = new Uint8Array(charData);
  // 调用pako 解析
  var data = pako.inflate(binData);
  //接受 Unicode 值，然后返回字符串。
  strData = String.fromCharCode.apply(null, new Uint16Array(data));
  return decodeURIComponent(strData);
}
// gzip压缩
function zip(str){
  var binaryString = pako.gzip(encodeURIComponent(str), { to: 'string' })
  return btoa(binaryString);
}
// initSocket();
export default {
  checkMobile,
  checkEmail,
  checkPassword,
  layerMsg,
  theme,
  filterDecimals,
  accMul,
  formatTime,
  unzip,
  zip

//   close,
}

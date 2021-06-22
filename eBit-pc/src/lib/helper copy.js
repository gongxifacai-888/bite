import Axios from 'axios'
import qs from 'qs'
import utils from './utils.js'
// const _PROTOCOL = window.location.protocol;
// var  _HOST ='';
// if(process.env.NODE_ENV=='development'){
// 	_HOST="www.miaobiex.com"
// }else{
// 	_HOST=location.host;
// }
// var _DOMAIN=_PROTOCOL + '//' + _HOST;
// var laravel_api=_DOMAIN+'/api/';
Axios.defaults.timeout=5000;
// Axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8'; 
Axios.defaults.baseURL = utils._API;   //配置接口地址

// POST传参序列化(添加请求拦截器)
Axios.interceptors.request.use((config)=>{
    // var token=localStorage.getItem('token')||'60bd1a6002f2aa1c2f0e6db12d30ff2c';
    var token='60bd1a6002f2aa1c2f0e6db12d30ff2c';
    if(token){
        config.headers.Authorization = token;
    }
    if(config.method=='post'){
        config.data=qs.stringify(config.data);
    }
    return config;
},(error)=>{
    console.log('参数错误');
    return Promise.reject(error);
})
//返回状态判断(添加响应拦截器)
Axios.interceptors.response.use((res) =>{
    console.log(res,123)
    //对响应数据做些事
    if(res.data.type=='error'){
        alert('错误');
        return 'error';
    }
    if(res.data.type == '999'){
        // return res;
        // 跳转登录
        alert(123);
        return '999';
    }
    if(res.data.type=='996'){
        return '996';
    }
    // return res.data.message;
    return res.data.message;
    
}, (error) => {
    console.log('网络异常')
    return Promise.reject(error);
});
const initDataToken=(params)=>{
    var url=params.url;
    var mytype=params.type||'GET';
    var data=params.data || {};
    return new Promise((resolve, reject) => {
        Axios({
            method: mytype,
            url: url,
            params:data,
            // baseURL: '/api/',
            // withCredentials: false
        }).then(res=>{
            console.log(res);
            resolve(res);
        }).catch(err=>{
            reject(err);
        })
    })
}
  Axios.initDataToken=initDataToken;
export default Axios;
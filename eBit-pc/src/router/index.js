import Vue from 'vue'
import Router from 'vue-router'
// import HelloWorld from '@/views/HelloWorld'
import Notice from '@/views/notice'
import Login from '@/views/login'
import Register from '@/views/register'
import Home from '@/views/home'
import Market from '@/views/markets/index'
// 法币模块
import LegalIndex from '@/views/legal/index'
// 商家
import Myshop from '@/views/legal/myshop'
// 币币模块
import Trade from '@/views/trade/index'
// 资产模块
import Assets from '@/views/assets/index'
import LegalAccount from '@/views/assets/legalAccount'
import LeverAccount from '@/views/assets/leverAccount'
import TradeAccount from '@/views/assets/tradeAccount'
import BindAddress from '@/views/assets/bindAddress'
import DealRecords from '@/views/assets/dealRecords'
import Transfer from '@/views/assets/transfer'
// 合约交易
import Lever from '@/views/lever/index'
//极速合约
import Second from '@/views/second/index'
//存币宝
import Deposit from '@/views/deposit/index'
// 个人中心
import Personal from '@/views/personal/index'
import AccountSettings from '@/views/personal/accountSettings'
import TransactionLog from '@/views/personal/transactionLog'
import CollectionSettings from '@/views/personal/collectionSettings'
import Authentication from '@/views/personal/authentication'
import ResetPassword from '@/views/personal/resetPassword'
import ForgetPassword from '@/views/personal/forgetPassword'
import TradePassword from '@/views/personal/tradePassword'
import BindAccount from '@/views/personal/bindAccount'
Vue.use(Router)
export default new Router({
  linkActiveClass:'routeractive',
  routes: [
    // 首页
    {
      path: '/',
      redirect:'/home'
    },
    // 首页
    {
      path: '/home',
      name:'home',
      component:Home
    },
     // 行情
     {
      path: '/markets',
      name:'market',
      component:Market
    },
     // 存币
     {
      path: '/deposit',
      name:'deposit',
      component:Deposit
    },
     // 法币
    {
      path: '/legalIndex',
      name:'legalIndex',
      component:LegalIndex,
      redirect:'/legalIndex/legal',
      children:[
        {
          path:'legal',
          component:() => import ('@/views/legal/legal')
        },
        {
          path:'orders',
          component:() => import ('@/views/legal/orders')
        },
        {
          path:'orderdetail',
          component:() => import ('@/views/legal/orderdetail')
        }
      ]
    },
    // 我的店铺
    {
      path: '/myshop',
      name:'myshop',
      component:Myshop,
      redirect:'/myshop/myshoporders',
      children:[
        {
          path:'myshoporders',
          component:() => import ('@/views/legal/myshoporders')
        },
        {
          path:'shoporders',
          component:() => import ('@/views/legal/shoporders')
        },
        {
          path:'shoporderdetail',
          component:() => import ('@/views/legal/shoporderdetail')
        }
      ]
    },
     // 币币
     {
      path: '/trade',
      name:'trade',
      component:Trade
    },
    // 公告页面
    {
      path: '/notice',
      name: 'notice',
      component: Notice
    },
    // 登录页面
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    // 注册页面
    {
      path: '/register',
      name: 'register',
      component: Register
    },
    // 资产页面
    {
      path:'/assets',
      name:'assets',
      component:Assets,
      redirect:'/assets/tradeAccount',
      children:[
        // 币币账户
        {
          path:'tradeAccount',
          name:'tradeAccount',
          component:TradeAccount
        },
        // 法币账户
        {
          path:'legalAccount',
          name:'legalAccount',
          component:LegalAccount
        },
        // 合约账户
        {
          path: 'leverAccount',
          name: 'leverAccount',
          component: LeverAccount
        },
        //极速合约账户
         {
          path:'secondAccount',
          name:'secondAccount',
          component:() => import ('@/views/assets/secondAccount')
        },
        //理财账户
        {
          path:'financialAccount',
          name:'financialAccount',
          component:() => import ('@/views/assets/financialAccount')
        },
        // 绑定提币地址
        {
          path: 'bindAddr',
          name: 'bindAddr',
          component: BindAddress
        },
         // 财务记录
         {
          path: 'dealRecords',
          name: 'dealRecords',
          component: DealRecords
        },
        // 财务记录
        {
          path: 'transfer',
          name: 'transfer',
          component: Transfer
        },
         //划转记录
         {
          path: 'transferLog',
          name: 'transferLog',
          component:() => import ('@/views/assets/transferLog')
        },
      ]
    },
    // 合约交易
    {
      path: '/lever',
      name:'lever',
      component:Lever
    },
     // 极速合约
     {
      path: '/second',
      name:'second',
      component:Second
    },
    // 个人中心
    {
      path:'/personal',
      name:'personal',
      component:Personal,
      children:[
        // 账户设置
        {
          path:'accountSettings',
          name:'accountSettings',
          component:AccountSettings
        },
        // 交易日志
        {
          path:'transactionLog',
          name:'transactionLog',
          component:TransactionLog
        },
        // 收款设置
        {
          path: 'collectionSettings',
          name: 'collectionSettings',
          component: CollectionSettings
        },
        // 身份认证
        {
          path: 'authentication',
          name: 'authentication',
          component: Authentication
        },
         //用户反馈
         {
          path: 'feedback',
          name: 'feedback',
          component:() => import ('@/views/personal/feedback')
        }
      ]
    },
    {
      path: '/resetPassword',
      name:'resetPassword',
      component:ResetPassword
    },
    {
      path: '/tradePassword',
      name:'tradePassword',
      component:TradePassword
    },
    {
      path: '/bindAccount',
      name:'bindAccount',
      component:BindAccount
    },
    {
      path: '/forgetPassword',
      name:'forgetPassword',
      component:ForgetPassword
    },
    {
      path:'/coinlog',
      name:'coinlog',
      component:() => import ('@/views/deposit/coinlog')
    },
      // name: 'HelloWorld',
      // component: HelloWorld,
      // children:[
     
      // ]
    // },
  ]
})

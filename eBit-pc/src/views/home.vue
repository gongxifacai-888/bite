<template>
    <div>
         <div class="block myswiper">
            <el-carousel  height="460px">
                <el-carousel-item v-for="(item,i) in swipers" :key="i">
                <!-- <img :src="item" alt="" class="w100 ht200"> -->
                    <a href="" class="block w100 h100 swiperhover">
                        <img :src="item.banner" alt="" class="w100 h100">
                    </a>
                </el-carousel-item>
            </el-carousel>
        </div>


        <div class="flex alcenter bgpart ptb10 plr20">
            <div class="plr20 bdrfopt25 flex1 markethover ptb10" v-for="(item,i) in marketlist" :key='i' v-if="i<6&&item.open_change==1">
                <div class="flex alcenter between">
                    <div class="ft14 baselight2"><span class="pr5">{{item.name}}</span><span class="">{{item.symbol}}</span></div>
                    <span class="ft16" :class="{ red: parseFloat(item.currency_quotation.change) < 0, green: parseFloat(item.currency_quotation.change) >= 0 || item.currency_quotation.change == '' }">{{ item.currency_quotation.change>=0 ? '+' : '' }}{{ (item.currency_quotation.change - 0) | toFixed2 }}%</span>
                </div>
                <div class="pt15">
                    <span class="pr5 ft20 bold" :class="{ red: parseFloat(item.currency_quotation.change) < 0, green: parseFloat(item.currency_quotation.change) >= 0 || item.currency_quotation.change == '' }">{{item.currency_quotation.close}}</span>
                    <!-- <span class="ft12 baselight2">≈ {{ ((item.currency_quotation.close - 0) * (cny_rate - 0)) | toFixed2 }}CNY</span> -->
                </div>
                <div class="pt15"><span class="ft12 baselight">{{$t('market.volume')}} {{item.currency_quotation.volume}}</span></div>
            </div>
        </div>
        
        <div class="notice_box bgf6 flex jscenter ptb15 w100 ft14 basegray">
            <!-- <div>
                <span class="plr20" v-for='(item,index) in noticeList' :key="index" @click="goDetail(item.id)">
                    {{item.title}}
                </span>
            </div> -->

            <!-- 无缝滚动效果 -->
            <div class="marquee-wrap">
                <ul class="marquee-list" :class="{'animate-up': animateUp}">
                <li v-for="(item, index) in noticeList" :key="index" @click="goDetail(item.id)">公告：{{item.title}}</li>
                </ul>
            </div>

        </div>

        <!-- register_wrapt -->
        <div class="register_wrap tc flex column jscenter alcenter">
            <h2>注册一个BITE全球站账号 开始交易旅程</h2>
            <div class="flex alcenter mauto mt30">
                <!-- <input type="text" placeholder="请输入你的邮箱或手机号" class="input-register" v-bind:value="reg">  -->
            <a href="/#/register"><button class="ft16">注册</button></a>
            </div>
        </div> 



<div class="el-table tabmarket el-table--fit el-table--enable-row-transition">
    <div class="hidden-columns">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div class="el-table__header-wrapper">
        <table cellspacing="0" cellpadding="0" border="0" class="el-table__header">
            <colgroup>
                <col name="el-table_1_column_1" width="253">
                <col name="el-table_1_column_2" width="252">
                <col name="el-table_1_column_3" width="252">
                <col name="el-table_1_column_4" width="252">
                <col name="el-table_1_column_5" width="252">
                <col name="el-table_1_column_6" width="252">
                <col name="el-table_1_column_7" width="120">
            </colgroup>
            <thead>
                <tr style="font-size:18;font-weight:bold;">
                    <th colspan="1" rowspan="1" class="el-table_1_column_1 is-leaf is-sortable">
                        <div class="cell">交易对<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_1_column_2 is-leaf is-sortable">
                        <div class="cell">最新价<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_1_column_3 is-leaf is-sortable">
                        <div class="cell">涨幅<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_1_column_4 is-leaf">
                        <div class="cell">最高价</div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_1_column_5 is-leaf">
                        <div class="cell">最低价</div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_1_column_6 is-leaf">
                        <div class="cell">24H交易额</div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_1_column_7 is-leaf">
                        <div class="cell">操作</div>
                    </th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="el-table__body-wrapper is-scrolling-none">
        <table cellspacing="0" cellpadding="0" border="0" class="el-table__body">
            <colgroup>
                <col name="el-table_1_column_1" width="253">
                <col name="el-table_1_column_2" width="252">
                <col name="el-table_1_column_3" width="252">
                <col name="el-table_1_column_4" width="252">
                <col name="el-table_1_column_5" width="252">
                <col name="el-table_1_column_6" width="252">
                <col name="el-table_1_column_7" width="120">
            </colgroup>
            <tbody>
                    <tr class="el-table__row" v-for="(item,i) in marketlist" :key='i' v-if="i<18&&item.open_change==1">
                    <td rowspan="1" colspan="1" class="el-table_1_column_1  ">
                        <div class="cell">
                            <div class="item"><img src="" alt="">
                                {{item.name}}{{item.symbol}}
                            </div>
                        </div>
                    </td>
                    <td rowspan="1" colspan="1" class="el-table_1_column_2  ">
                        <div class="cell">{{item.currency_quotation.close}}</div>
                    </td>
                    <td rowspan="1" colspan="1" class="el-table_1_column_3  ">
                        <div class="cell"><span class="el-tag el-tag--success el-tag--light">
                                <p :class="['flex1','tc','change','green',{'red':item.currency_quotation.change<0}]"> {{item.currency_quotation.change}}%</p>
                            </span></div>
                    </td>
                    <td rowspan="1" colspan="1" class="el-table_1_column_4  ">
                        <div class="cell">{{item.currency_quotation.high}}</div>
                    </td>
                    <td rowspan="1" colspan="1" class="el-table_1_column_5  ">
                        <div class="cell">{{item.currency_quotation.low}}</div>
                    </td>
                    <td rowspan="1" colspan="1" class="el-table_1_column_6  ">
                        <div class="cell">{{item.currency_quotation.volume}}</div>
                    </td>
                    <td rowspan="1" colspan="1" class="el-table_1_column_7 ">
                        <div class="cell" v-if="item.currency_quotation.volume == 0">
                                <span style="">待开启</span>

                         </div>
                         <div class="cell" v-else>
                            <button type="button" class="el-button el-button--danger el-button--small is-plain">
                                <a href="/index.html#/trade" target="_blank"><span>去交易</span></a>
                            </button>
                         </div>
                    </td>
                </tr>


<!-- 循环行 结束 -->

            </tbody>
        </table>
        <!---->
    </div>
    <!---->
</div>
<div class="el-table__fixed-right-patch" style="width: 0px; height: 59px;"></div>
<div class="el-table__column-resize-proxy" style="display: none;"></div>

        <!-- exchange info -->
        <div class="bgwhite pt120 pb80">
            <p class="tc ft30 cortt">{{$t('home.leader')}}</p>
            <p class="tc gray4 mt20">{{$t('home.c02')}}</p>
            <div class="w70 mt50 flex alcenter mauto tc">
               <div class="flex1">
                    <img src="../assets/images/p0.svg" alt="" class="wt50">
                    <div class="mt20">
                        <p class="bold ft16 cortt mb10">{{$t('home.c03')}}</p>
                        <p class=" ft14 cortt mb10">{{$t('home.c04')}}</p>
                        <p class=" ft14 cortt">{{$t('home.c05')}}</p>
                    </div>
               </div>
               <div class="flex1">
                    <img src="../assets/images/p1.svg" alt="" class="wt50">
                    <div class="mt20">
                        <p class="bold ft16 cortt mb10">{{$t('home.c06')}}</p>
                        <p class=" ft14 cortt mb10">5 {{$t('home.c07')}}</p>
                        <p class=" ft14 cortt">{{$t('home.c08')}}</p>
                    </div>
               </div>
               <div class="flex1">
                    <img src="../assets/images/p2.svg" alt="" class="wt50">
                    <div class="mt20">
                        <p class="bold ft16 cortt mb10">{{$t('home.c09')}}</p>
                        <p class=" ft14 cortt mb10">{{$t('home.c10')}}</p>
                        <p class=" ft14 cortt">{{$t('home.c11')}}</p>
                    </div>
               </div>
            </div>
        </div>
        <div class="downinfo pt90 pb90">
            <div class="tc white ft30 bold">{{$t('home.c12')}}</div>
            <div class="mt20 ft16 white tc">{{$t('home.c13')}}</div>
            <div class="flex alcenter mt50 w70 mauto">
                <div>
                   <div class="pl10">
                        <div class="flex alcenter mb30">
                            <div class="wt40 ht40 flex alcenter jscenter radius50p bgwhite tc">
                                <img src="../assets/images/andriod.svg" alt="">
                            </div>
                            <span class="pl10 ft14 white">Andriod {{$t('home.downs')}}</span>
                        </div>
                        <div class="flex alcenter mb30">
                            <div class="wt40 ht40 flex alcenter jscenter radius50p bgwhite tc">
                                <img src="../assets/images/ios.svg" alt="">
                            </div>
                            <span class="pl10 ft14 white">IOS {{$t('home.downs')}}</span>
                        </div>
                   </div>
                    <div class="tc">
                        <div class="bdblue plr10 ptb10 bgblue2 inblock">
                            <div class="bgwhite ptb5 plr5">
                                <img src="../assets/images/downcode.png" alt="" class="wt140">
                            </div>
                        </div>
                        <p class="white mt10">{{$t('home.sweep')}} Andriod & IOS</p>
                    </div>
                </div>
                <div class="ml50 flex1 tc posRelt">
                    <img src="../assets/images/expc.png" alt="" class="w80">
                    <div class="abstort rt0" style="top:35%;">
                        <img src="../assets/images/exmobile.png" alt="" class="wt160">
                    </div>
                </div>
            </div>
        </div>
        <footerComponet></footerComponet>
    </div>
</template>
<script>
import footerComponet from '@/components/footer'

export default {
    components:{footerComponet},
    // name: "marquee-up",
    data(){
        return{
            swipers:[],
            marketlist:[
                // {name:'比特币',symbol:'BTC/USDT',change:'-2.0',close:'123',cny:'23434',volume:'773777'},
                // {name:'以太坊',symbol:'ETH/USDT',change:'2.0',close:'222',cny:'555',volume:'33333'},
                // {name:'柚子',symbol:'EOS/USDT',change:'-6.0',close:'123',cny:'23434',volume:'666666666'}
            ],
            cny_rate:'',
            usd_rate:'',
            noticeList:[
                // 'title111111111111111111111','title2222222222222','title3333333333333333'
            ],// 
            animateUp: false,
            timer: null
        }
    },
    filters: {
		toFixed2: function(value, options) {
			value = Number(value);
			return value.toFixed(2);
		}
	},
    created(){
       this.quotation()
       this.bindSocket()
       this.getNoticeList()
       this.getBanner()
    },
    mounted() {
    this.timer = setInterval(this.scrollAnimate, 2500);
    },
    methods:{
        scrollAnimate() {
            this.animateUp = true
            setTimeout(() => {
                this.noticeList.push(this.noticeList[0])
                this.noticeList.shift()
                this.animateUp = false
            }, 500)
            },
  
        destroyed() {
            clearInterval(this.timer)
        },

        //行情交易对
		quotation() {
            this.$http.initDataToken({
                url: 'market/currency_matches'
            },false).then(res=>{
                console.log(res)
                let usdtData = res.find(item => item.code == 'USDT');
                this.cny_rate = usdtData.cny_price;
				this.usd_rate = usdtData.usd_price;
				this.marketlist = usdtData.matches.sort(function(a, b) {
					return b.currency_quotation.change - a.currency_quotation.change;
                });
                console.log(this.marketlist)
            })
        },
        // 链接socket
        bindSocket(){
            var that=this;
            that.$socket.connected((socket)=>{
                socket.send({type: "sub", params: "DAY_MARKET"});
                socket.on('DAY_MARKET',msg=>{
                    for (var i = 0; i < this.marketlist.length; i++) {
                        if (this.marketlist[i].id == msg.currency_match_id) {
                            this.marketlist[i].currency_quotation.close = msg.quotation.close;
                            this.marketlist[i].currency_quotation.change = msg.quotation.change;
                            this.marketlist[i].currency_quotation.low = msg.quotation.low;
                            this.marketlist[i].currency_quotation.high = msg.quotation.high;
                            this.marketlist[i].currency_quotation.volume = msg.quotation.volume;
                        }
                    }
                })
            })
        },
        //公告
		getNoticeList() {
             this.$http.initDataToken({
                url: 'news/list',
                data: { category_id: 22 }
            },false).then(res=>{
               this.noticeList = res.data;
            })
        },
         // 公告详情
        goDetail(id) {
            this.$router.push({
                name: "notice",
                query: { 
                    id: id,
                    category_id: 22
                }
            });
        },
         //轮播图
		getBanner() {
             this.$http.initDataToken({
                url: 'news/list',
                data: { category_id: 23 }
            },false).then(res=>{
               this.swipers = res.data;
            })
        },
        unbindSocket(){
            this.$socket.send({type: "unsub", params: "DAY_MARKET"});
        },
    
    beforeRouteLeave(to, from, next){
        next();
        this.unbindSocket();
    },
    }
}
</script>

<style lang="scss">
/* .el-carousel__container{
    min-height: 460px;
} */
    /* .myswiper img{
        min-height: 460px;
    } */
   .downinfo{
       min-height: 700px;
       background: url('../assets/images/BG.svg') no-repeat left top /100% 100%;
   }
   .notice_box{
    >div{
      overflow: hidden;
      word-break: keep-all;
      text-align: center;
      span{
        display: inline-block;
        position: relative;
        padding: 0 2em 0 1.5em;
        cursor: pointer;
      }
      span:before {
        content: "/";
        position: absolute;
        left: -.5em;
        color: #54748f;
      }
      span:nth-child(1):before{
        content: "";
      }
      span:hover{
        color: #357ce1;
      }
    }
}

    .register_wrap{
        height: 240px;
        width: 100%;
        background: #fff;
    }

    .register_wrap h2 {
        color: #232a4a;
        font-size: 22px;
        line-height: 24px;
    }

    .register_wrap button {
        margin-left: 8px;
        display: inline-block;
        height: 40px;
        padding: 0 44px;
        font-size: 16px;
        min-width: 120px;
        -webkit-appearance: none;
        background-color: #357ce1;
        color: #fff;
        border: none;
        outline: none;
        border-radius: 2px;
        cursor: pointer;
    }

    .register_wrap .input-register {
        width: 400px;
        height: 40px;
        font-size: 12px;
        color: #000;
        border: 1px solid #bdced9;
        border-radius: 2px;
        padding-left: 16px;
    }

.el-table--fit {
    border-right: 0;
    border-bottom: 0;
}

.el-table {
    position: relative;
    overflow: hidden;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-box-flex: 1;
    -ms-flex: 1;
    flex: 1;
    width: 80%;
    max-width: 100%;
    font-size: 14px;
    color: #606266;
}
table{
    width: 100%!important;
    margin: auto;
    font-size: 16px;
}
.el-table, .el-table__expanded-cell {
    background-color: #FFF;
}
.el-table__footer-wrapper, .el-table__header-wrapper {
    overflow: hidden;
}
.el-table__body-wrapper, .el-table__footer-wrapper, .el-table__header-wrapper {
    width: 100%;
}
.el-table__body, .el-table__footer, .el-table__header {
    table-layout: fixed;
    border-collapse: separate;
    width: 100%;
}
.el-table thead {
    color: #909399;
    font-weight: 500;
}
.el-table th, .el-table tr {
    background-color: #FFF;
}
.el-table th {
    overflow: hidden;
    -ms-user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
}
.el-table td, .el-table th {
    padding: 12px 0;
    min-width: 0;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    text-overflow: ellipsis;
    vertical-align: middle;
    position: relative;
    text-align: left;
}
.el-date-table, .el-table th {
    -webkit-user-select: none;
    -moz-user-select: none;
}
caption, th {
    text-align: left;
}
address, caption, cite, code, dfn, em, th, var {
    font-style: normal;
    font-weight: 500;
}
colgroup{
    width:100%;
}
.tabmarket {
    width: 100%;
    padding: 0 20px;
}

.marquee-wrap  {
  width: 80%;
  height: 40px;
  border-radius: 20px;
//   background: rgba($color: #000000, $alpha: 0.6);
  margin: 0 auto;
  overflow: hidden;
  .marquee-list {
    li {
      width: 100%;
      height: 100%;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
      padding: 0 20px;
      list-style: none;
      line-height: 40px;
      text-align: center;
      color: #000;
      font-size: 18px;
      font-weight: 400;
    }
  }
  .animate-up {
    transition: all 0.5s ease-in-out;
    transform: translateY(-40px);
  }
}

</style>
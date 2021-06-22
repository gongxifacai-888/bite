<template>
    <div class="w100 baselight">
        <div class="flex alcenter mt5 vh80" style="min-height:710px">
            <!-- 左侧行情 -->
            <div class="wt240 bd mr5 bgpart  ptb10 radius2 h100 ">
               <div  class="flex alcenter plr10">
                    <div class="plr5 ht25 lh25 tc ft12 baselight2 radius2 mr10 pointer" v-for="(item,i) in list" :key="item.id" :class="{'bgblue txtwhite':currentIndex==i}" @click="changelegal(i)" >{{item.code}}</div>
                </div>
                <div class="flex alcenter mt10 mb10" >
                    <span class="flex1 pl10">{{$t('trade.currency')}}</span>
                    <span class="flex1 tr">{{$t('trade.new_price')}}</span>
                    <span class="flex1 tr pr10">{{$t('trade.changes')}}</span>
                </div>
                <div class="overyscroll" >
                    <div v-if="list.length>0 && list[currentIndex]">
                        <div class="flex alcenter ptb5 markethover pointer" :class="{'bgshadow':itm.symbol==symbol}"  v-for="(itm,j) in list[currentIndex].matches" v-if="itm.open_change==1"  :key="j" @click="changeSymbol(j)">
                            <div class="flex1 pl10 baselight2">{{itm.base_currency_code}}</div>
                            <div class="flex1 tr baselight2" v-if="itm.currency_quotation">{{itm.currency_quotation.close}}</div>
                            <div class="flex1 tr pr10 " :class="[itm.currency_quotation.change>=0?'green':'red']" v-if="itm.currency_quotation">
                                <span v-if="itm.currency_quotation.change>=0">+</span>
                                {{itm.currency_quotation.change}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex1 flex column h100 mr5" v-if="mathid">
                <tradeklineAndhandicap ref="klineChild" :symbol='symbol' :mathid='mathid' :currency_matchInfo='currency_matchInfo' :currencyName='currencyName' :legalName='legalName' :currencyId='currencyId' :legalId='legalId' :cny_usd='cny_usd' :usd_usd='usd_usd' :inlist='inlist' :outlist='outlist' :completelist='completelist' :legalBalance1='legalBalance' :currencyBalance1='currencyBalance'></tradeklineAndhandicap>
                <!-- k线 -->
                <!-- <TradeKline class="flex2 bgpart" :symbol='symbol' :mathid='mathid' :currency_matchInfo='currency_matchInfo'></TradeKline> -->
                <!-- 下单 -->
                <!-- <TradeDoorder class="flex12 mt5 bgpart" :currencyName='currencyName' :legalName='legalName' :currencyId='currencyId' :legalId='legalId' :currency_match_id='mathid'></TradeDoorder> -->
            </div> 
            <!-- 盘口&全站 -->
            <!-- <TradeHandicapComplete class="flex15 h100"  v-if="mathid" :symbol='symbol' :cny_usd='cny_usd' :completelist='completelist' :inlist='inlist' :outlist="outlist" :currency_matchInfo='currency_matchInfo'></TradeHandicapComplete>  -->
            <!-- 全站 -->
            <!-- <TradeComplete class="flex1 bgpart h100"  v-if="mathid" :symbol='symbol' :completelist='completelist'></TradeComplete>     -->
        </div>          
        <TradeHistery class="mt5 bgpart" :mathid='mathid' :currencyName='currencyName' :legalName='legalName' :order='order' :neworder='neworder'></TradeHistery>
    </div>
</template>
<script>
// import TradeKline from '@/views/trade/tradeKline.vue'
import TradeDoorder from '@/views/trade/tradeDoorder.vue'
import TradeHandicapComplete from '@/views/trade/tradeHandicapComplete.vue'
import tradeklineAndhandicap from '@/views/trade/tradeklineAndhandicap.vue'
// import TradeComplete from '@/views/trade/tradeComplete.vue'
import TradeHistery from '@/views/trade/tradeHistery.vue'
import { longStackSupport } from 'q'
export default {
    components:{
        // TradeKline,
        TradeDoorder,
        TradeHistery,
        // TradeHandicapComplete
        tradeklineAndhandicap,
        // TradeComplete
    },
    data(){
        return {
            list:[],
            currentIndex:0,
            symbol:'',
            mathid:'',
            currencyId:'',
            legalId:'',
            currencyName:'',
            legalName:'',
            cny_usd:'',
            usd_usd:1,
            inlist:[],
            outlist:[],
            completelist:[],
            currency_matchInfo:{},
            order:{},
            neworder:{},
            legalBalance:'',
            currencyBalance:''
        }
    },
    created(){
        this.getmathes();
        // this.$http.initDataToken({url:'test'},false)
        //     .then(res=>{
               
        // })
        // let a = this.$utils.zip('123456')
        // console.log(a)
        // console.log(this.$utils.unzip(a))
    },
    mounted(){
        //  setTimeout(() => {
            this.bindSocket()
        // }, 2000);
    },
    methods:{
        // 链接socket
         bindSocket(){
            var that=this;
            let token=localStorage.getItem('token');
            that.$socket.connected((socket)=>{
                socket.send([
                    {type: "sub", params: "DAY_MARKET"},
                    {type: "login", params: token},
                    {type:'sub',params:'TX_MATCHED'},
                    {type:'sub',params:'TX_SUBMIT'}       
                ]);
                socket.on('DAY_MARKET',msg=>{
                    if(msg.symbol==that.symbol){
                        let data=msg.quotation;
                        data.cny_price=(data.close*that.cny_usd).toFixed(2);
                        data.usd_price = (data.close*that.usd_usd).toFixed(2);
                        that.currency_matchInfo=data;
                    }
                    // 过滤
                    if(that.list){
                        let matches=that.list[that.currentIndex].matches;
                        for(var i in matches){
                            if(matches[i].symbol==msg.symbol) {
                                let curquotation=msg.quotation;
                                matches[i].currency_quotation.change=curquotation.change;
                                matches[i].currency_quotation.close=curquotation.close;
                            }
                        }
                    }
                })
                socket.on('TX_MATCHED',msg=>{
                    let order = msg.order;
                    that.legalBalance = order.quote_account.balance;
                    that.currencyBalance = order.base_account.balance;
                    that.order = order;
                    console.log(that.order)
                })
                socket.on('TX_SUBMIT',msg=>{
                    let order = msg.order;
                    order.way = msg.way;
                    that.neworder = order;
                    // console.log(that.neworder)
                })
            })
           
       },
        bindSocket2(){
            var that=this;
            that.$utils.socket.onopen([{type: "login", params:123},{type: "sub", params: "DAY_MARKET"}]);
            //  console.log(that.socket,'--------')
            that.$utils.socket.onmessage=function(res){
                let msg=JSON.parse(res.data);
                // console.log(msg.type)
                if(msg.type=='DAY_MARKET'){
                    //  console.log(msg)
                    // 当前选择的交易对
                    if(msg.symbol==that.symbol){
                       let data=msg.quotation;
                       data.cny_price=(data.close*that.cny_usd).toFixed(2);
                       that.currency_matchInfo=data;
                    }
                    // 过滤
                    if(that.list){
                        let matches=that.list[that.currentIndex].matches;
                        console.log(matches,222222222222)
                        for(var i in matches){
                            if(matches[i].symbol==msg.symbol) {
                                let curquotation=msg.quotation;
                                matches[i].currency_quotation.change=curquotation.change;
                                matches[i].currency_quotation.close=curquotation.close;
                            }
                        }
                    }
                }
            }
       },
        //    获取行情交易列表
        getmathes(){
            this.$http.initDataToken({url:'market/currency_matches'},false)
            .then(res=>{
                this.list=res;
                var tradeinfo=JSON.parse(localStorage.getItem('tradeinfo')||"{}")
                var temp;
                if(res.length>0){
                    if(tradeinfo.symbol){
                        for(var i in res){
                            let matches0=res[i].matches;
                            let matches = matches0.filter(function(item,index){
                                return item.open_change == 1;
                            })
                            for(var j in matches){
                               if(tradeinfo.symbol==matches[j].symbol) {
                                    temp=matches[j];
                                    this.getcurmatchinfo(temp);
                                    return;
                               }
                            }
                        }
                    }else if(res[0].matches.length>0){
                        temp=res[0].matches[0];
                        this.getcurmatchinfo(temp);
                    }
                   
                }
               
            })
        },
        // 获取盘口和全站，k线里边最新价格currency_matchInfo
        getDefault(id){
             this.$http.initDataToken({url:'market/deal',data:{currency_match_id:id}},false)
            .then(res=>{
                // this.cny_usd=res.cny_usd;
                this.cny_usd = res.quotation.currency_match.quote_currency.cny_price;
                this.usd_usd = res.quotation.currency_match.quote_currency.usd_price;
                this.inlist=res.depth.in;
                this.outlist=res.depth.out.reverse();
                this.completelist=res.tx_completes;
                this.currency_matchInfo=res.quotation;
                 console.log(this.inlist)
            })
        },
        getcurmatchinfo(temp){
            console.log(temp);
            this.symbol=temp.symbol
            this.mathid=temp.currency_quotation.currency_match_id;
            this.currencyId=temp.base_currency_id;
            this.legalId=temp.quote_currency_id;
            this.currencyName=temp.base_currency_code;
            this.legalName=temp.quote_currency_code;
            localStorage.setItem('tradeinfo',JSON.stringify({currencyName:temp.base_currency_code,currencyId:temp.base_currency_id,legalId:temp.quote_currency_id,legalName:temp.quote_currency_code,symbol:temp.symbol,mathid:temp.currency_quotation.currency_match_id,}))
            // 获取盘口和全站
            this.getDefault(this.mathid);
        },
        // 改变当前交易对
        changeSymbol(j){
            var curmatch=this.list[this.currentIndex].matches[j];
            this.getcurmatchinfo(curmatch);
        },
        // 改变法币
        changelegal(i){
            this.currentIndex = i;
        },
        unbindSocket(){
            this.$socket.send(
                {type: "unsub", params: "DAY_MARKET"},
                {type:'unsub',params:'TX_MATCHED'},
                {type:'unsub',params:'TX_SUBMIT'}
            );
        },
    },
    beforeRouteLeave(to, from, next){
        next();
        let symbol = this.currencyName+'/'+this.legalName;
        this.unbindSocket();
        this.$refs.klineChild.unbindSocket(symbol);
    }
}
</script>
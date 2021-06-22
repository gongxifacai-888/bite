<template>
    <div class="flex column">
        <div class="flex">
             <div class="bgheader bdbheader flex1 plr10 ht40 lh40 baselight2 ft16">盘口</div>
             <div class="bgheader bdbheader flex1 plr10 ht40 lh40 baselight2 ft16 ml5">全站成交 </div>
        </div>
        <div class="flex1 flex">
            <div class="flex column w100 flex1 bgpart">
                <div class="plr10 ptb10 flex1 flex column w100">
                    <div class="flex alcenter">
                        <span class="flex1">方向</span>
                        <span class="flex1 tc">价格({{legalname}})</span>
                        <span class="flex1 tr">数量({{currencyname}})</span>
                    </div>
                    <!-- 卖盘 -->
                <div class="bdbheader  mt10">
                    <!-- 没有socket推送的时候 -->
                    <div v-if="outlistSocket.length<=0" >
                        <div class="ptb5 flex alcenter pointer"  v-for='(item,i) in outlist' :key="i" @click="changeSell(item.price)">
                            <span class="flex1 red">{{item.index}}</span>
                            <span class="flex1 tc baselight2">{{item.price}}</span>
                            <span class="flex1 tr baselight2">{{item.number}}</span>
                        </div>  
                    </div>
                        
                    <div v-else class="ptb5 flex alcenter pointer"  v-for='(item,i) in outlistSocket' :key="i"  @click="changeSell(item.price)">
                        <span class="flex1 red">{{item.index}}</span>
                        <span class="flex1 tc baselight2">{{item.price}}</span>
                        <span class="flex1 tr baselight2">{{item.number}}</span>
                    </div>  
                </div>
                <div class="ptb10 bdbheader">
                    <span class="red bold ft16" ref='newprice'>{{newpriceInfo.close}}</span>
                    <span class="">≈{{newpriceInfo.cny_price}}CNY</span>
                </div>
                <!-- 买盘 -->
                <div class="bdbheader bgpart mt10">
                    <div v-if="inlistSocket.length<=0">
                        <div class="ptb5 flex alcenter pointer"  v-for='(item,i) in inlist' :key="i" @click="changeBuy(item.price)">
                            <span class="flex1 green">{{item.index}}</span>
                            <span class="flex1 tc baselight2">{{item.price}}</span>
                            <span class="flex1 tr baselight2">{{item.number}}</span>
                        </div>  
                    </div>
                    <div class="ptb5 flex alcenter pointer" v-else  v-for='(item,i) in inlistSocket' :key="i" @click="changeBuy(item.price)">
                        <span class="flex1 green">{{item.index}}</span>
                        <span class="flex1 tc baselight2">{{item.price}}</span>
                        <span class="flex1 tr baselight2">{{item.number}}</span>
                    </div>   
                </div>
                </div>
            </div>
            <!-- 全站 -->
            <div class="flex1 ml5 bgpart">
                <div class="plr10 ptb10 flex1 flex column w100">
                    <div class="flex alcenter">
                        <span class="flex1">时间</span>
                        <span class="flex1 tc">价格({{legalname}})</span>
                        <span class="flex1 tr">数量({{currencyname}})</span>
                    </div>
                    <div v-if="completelistSocket.length<=0" >
                        <div class="ptb5 flex alcenter"  v-for='(item,i) in completelist' :key="i">
                            <span class="flex1 red">{{item.index}}</span>
                            <span class="flex1 tc baselight2">{{item.price}}</span>
                            <span class="flex1 tr baselight2">{{item.number}}</span>
                        </div>  
                    </div>
                    <div v-else class="ptb5 flex alcenter"  v-for='(item,i) in completelistSocket' :key="i">
                        <span class="flex1 red">{{item.index}}</span>
                        <span class="flex1 tc baselight2">{{item.price}}</span>
                        <span class="flex1 tr baselight2">{{item.number}}</span>
                    </div>   
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props:['inlist','outlist','symbol','currency_matchInfo','cny_usd','completelist'],
    data(){
        return {
            token:'',
            outlistSocket:[],
            inlistSocket:[],
            completelistSocket:[],
            newpriceInfo:{},
            cny_usdprice:'',
            legalname:'',
            currencyname:'',
        }
    },
     watch:{
        currency_matchInfo: {
            handler(newInfo, oldInfo) {
                this.newpriceInfo = newInfo;
            },
            // 代表在wacth里声明了firstName这个方法之后立即先去执行handler方法
            immediate: true
        },
        symbol:{
            handler(newInfo,oldInfo){
                if(oldInfo){
                    this.unbindSocket(oldInfo);
                }
                this.bindSocket(newInfo);
                this.legalname=newInfo.split('/')[1];
                this.currencyname=newInfo.split('/')[0];
            },
            immediate:true
        }
    },
    created(){
        this.token=localStorage.getItem('token');
    },
    mounted(){
   
    },   
    methods:{
       bindSocket(symbol){
            var that=this;
            console.log(that.symbol)
            that.$utils.socket.onopen([{type: "login", params: that.token},{type: "sub",params: "MARKET_DEPTH."+symbol}]);
            //  console.log(that.socket,'--------')
            that.$utils.socket.onmessage=function(res){
                let msg=JSON.parse(res.data);
                // console.log(msg);
                if(msg.type=='MARKET_DEPTH'){
                    that.outlistSocket=msg.depth.out.reverse();
                    that.inlistSocket=msg.depth.in;
                    // console.log(msg.depth)
                }
                // if(msg.type=='DAY_MARKET'){
                //     // 过滤
                //     if(msg.symbol==symbol){
                //          that.newpriceInfo.close=msg.quotation.close;
                //         that.newpriceInfo.cny_price=msg.quotation.close*that.cny_usd;
                //     }
                   
                // }
            }
       },
       unbindSocket(symbol){
           this.$utils.socket.onopen([{type: "unsub",params: "MARKET_DEPTH."+symbol}]);
           this.outlistSocket=[];
           this.inlistSocket=[];
       },
    //   更新下单
        changeBuy(price){
            eventBus.$emit('changeBuyPrice',price);
        },
        changeSell(price){
            eventBus.$emit('changeSellPrice',price);
        }
    },
    destroyed(){
        this.unbindSocket(this.symbol)
    }
}
</script>
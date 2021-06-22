<template>
    <div class="flex column">
        <div class="bgpart ptb15 plr20">
            <p class="flex alcenter ft16">
                <img src="../../assets/images/money.png" alt="">
                <span>{{$t('second.MarginAccount')}}</span>
            </p>  
            <p class="tc ft22 mt20">0 USDT</p>
        </div>
        <div class="flex mt20">
            <div class="bgheader bdbheader flex1 plr10 ht40 lh40 baselight2 ft16">{{$t('market.latest')}}</div>
             <!-- <div class="bgheader bdbheader flex1 plr10 ht40 lh40 baselight2 ft16 ml5">全站成交 </div> -->
        </div>
        <div class="flex1 flex">
            <div class="flex column w100 flex1 bgpart pb10">
                <div class="flex alcenter ptb10 bdbe9 plr10">
                    <span class="flex1">{{$t('trade.direction')}}</span>
                    <span class="flex1 tc">{{$t('trade.price')}}({{currency_matchInfo.quote_currency_code}})</span>
                    <span class="flex1 tr">{{$t('trade.num')}}({{currency_matchInfo.base_currency_code}})</span>
                </div>
                <!-- 卖盘 -->
                <div class="bdbheader mt10 flex1 plr10">
                    <!-- 没有socket推送的时候 -->
                    <div  >
                        <div class="ptb5 flex alcenter pointer"  v-for='(item,i) in outlist' v-if="outlist.length>5 ? (i>(outlist.length-6)&&i<10) : i<5" :key="i" @click="changeSell(item.price)">
                            <span class="flex1 red">{{item.index}}</span>
                            <span class="flex1 tc baselight2">{{item.price}}</span>
                            <span class="flex1 tr baselight2">{{item.amount}}</span>
                        </div>  
                    </div>
                    <!-- <div v-else class="ptb5 flex alcenter pointer"  v-for='(item,i) in outlistSocket' :key="i"  @click="changeSell(item.price)">
                        <span class="flex1 red">{{item.index}}</span>
                        <span class="flex1 tc baselight2">{{item.price}}</span>
                        <span class="flex1 tr baselight2">{{item.number}}</span>
                    </div>   -->
                </div>
                <!-- <div class="ptb10 bdbheader plr10">
                    <span class="bold ft16" :class="[currency_matchInfo.currency_quotation.change < 0 ? 'red' : 'green']">{{ currency_matchInfo.currency_quotation.close || "0.00"}}</span>
                    <span class="">≈{{ currency_matchInfo.currency_quotation.close *cny_usd || "0.00" | filterDecimals}}CNY</span>
                </div> -->
                <!-- 买盘 -->
                <div class="bgpart flex1 plr10">
                    <div>
                        <div class="ptb5 flex alcenter pointer"  v-for='(item,i) in inlist' v-if="i<5" :key="i" @click="changeBuy(item.price)">
                            <span class="flex1 green">{{item.index}}</span>
                            <span class="flex1 tc baselight2">{{item.price}}</span>
                            <span class="flex1 tr baselight2">{{item.amount}}</span>
                        </div>  
                    </div>
                    <!-- <div class="ptb5 flex alcenter pointer" v-else  v-for='(item,i) in inlistSocket' :key="i" @click="changeBuy(item.price)">
                        <span class="flex1 green">{{item.index}}</span>
                        <span class="flex1 tc baselight2">{{item.price}}</span>
                        <span class="flex1 tr baselight2">{{item.number}}</span>
                    </div>    -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props:['inlist','outlist','currency_matchInfo','cny_usd'],
    data(){
        return {
            
           
        }
    },
  
    created(){
        
    },
    mounted(){
    
    },   
    methods:{
    //   更新下单
        changeBuy(price){
            eventBus.$emit('changeBuyPrice',price);
        },
        changeSell(price){
            eventBus.$emit('changeSellPrice',price);
        }

    }
}
</script>
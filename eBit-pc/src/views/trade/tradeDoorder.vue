<template>
    <div>
         <div class="bgheader bdbheader flex alcenter plr10  baselight2" >
            <div class='ptb5 mr10 bold ft14' :class="{'active2px':i==index}" v-for="(item,i) in list" :key="i" @click='changetype(i)'>{{item}}</div> 
        </div>
        <div class=" flex  ft12">
            <!-- ?? -->
            <div class="flex1 plr10 mt10" style="border-right:1px dashed #383f66">
                <div class="flex alcenter mb10">可用{{legalBalance}}{{legalName}}</div>
                <div class="flex alcenter mb10 posRelt">
                    <span class="w15">买入价</span>
                    <input type="number" v-model="buyprice" class="ht30 bdinput lh30 plr5 radius2 ml10 flex1 white" v-show="index==0" @input='buypriceInput'>
                    <input type="text" placeholder="以市场最优价格买入" class="trade ht30 bgheader lh30 plr5 radius2 ml10 flex1 white" v-show="index==1" disabled>
                    <span class="abstort ht30 lh30 plr5 rt0 btm0">{{legalName}}</span>
                </div>
                <div class="flex alcenter mb10 posRelt">
                    <span class="w15" >买入量</span>
                    <input type="number" v-model="buynumber" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white"  @input='buynumberInput'>
                    <span class="abstort ht30 lh30 plr5 rt0 btm0" v-show="index==0">{{currencyName}}</span>
                     <span class="abstort ht30 lh30 plr5 rt0 btm0" v-show="index!=0">{{legalName}}</span>
                </div>
                <!-- <div class="flex alcenter mb10">
                    <span class="w15">密码</span>
                    <input type="passord" v-model="password" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white">
                </div> -->
                <el-slider v-model="value1" :step="25" show-stops class="buyslider ml10" @change="sliderBuy"></el-slider>
                <div class="flex alcenter mb10">交易额：{{totalAmountBuy}}{{legalName}}</div>
                <div class="bdinput tc ht30 lh30  radius2 blue" v-if="!token">
                    <router-link :to="{name:'login'}">登录</router-link>
                    <span class="baselight2">或</span>
                    <router-link :to="{name:'register'}">注册</router-link>
                </div>
                <div class="bggreen tc ht30 lh30 white radius2" @click="buyin" v-else>买入</div>
            </div>
            <!-- 卖盘 -->
             <div class="flex1 plr10 mt10">
                <div class="flex alcenter mb10">可用{{currencyBalance}}{{currencyName}}</div>
                <div class="flex alcenter mb10 posRelt">
                    <span class="w15">卖出价</span>
                    <input type="number" v-model="sellprice" class="ht30 bdinput lh30 plr5 radius2 ml10 flex1 white" v-show="index==0">
                    <input type="text" placeholder="以市场最优价格卖出" class="trade ht30 bgheader lh30 plr5 radius2 ml10 flex1 white" v-show="index==1" disabled>
                    <span class="abstort ht30 lh30 plr5 rt0 btm0">{{currencyName}}</span>
                </div>
                <div class="flex alcenter mb10 posRelt">
                    <span class="w15">卖出量</span>
                    <input type="number" v-model="sellnumber" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white ">
                    <span class="abstort ht30 lh30 plr5 rt0 btm0">{{currencyName}}</span>
                </div>
                <!-- <div class="flex alcenter mb10">
                    <span class="w15">密码</span>
                    <input type="passord" v-model="password" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white">
                </div> -->
                <el-slider v-model="value2" :step="25 " show-stops class="sellslider ml10" @change="sliderSell"></el-slider>
                <div class="flex alcenter mb10">交易额：{{totalAmountSell}}{{legalName}}</div>
                <div class="bdinput tc ht30 lh30  radius2 blue" v-if="!token">
                    <router-link :to="{name:'login'}">登录</router-link>
                    <span class="baselight2">或</span>
                    <router-link :to="{name:'register'}">注册</router-link>
                </div>
                <div class="bgred tc ht30 lh30 white radius2" @click="sellout" v-else>卖出</div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props:['legalName','currencyName','legalId','currencyId','currency_match_id'],
    data(){
        return {
            list:['限价交易','市价交易'],
            index:0,
            buyprice:'',
            buynumber:'',
            password:'',
            sellprice:'',
            sellnumber:'',
            token:'',
            legalBalance:'',
            currencyBalance:'',
            value1:0,
            value2:0,
            // totalAmountBuy:'',
            // totalAmountSell:'',
        }
    },
    created(){
        this.token=localStorage.getItem('token');
        // 获取当前币余额
        this.initBalance(this.currencyId,0)
        // 获取法币余额
        this.initBalance(this.legalId,1)
    },
    mounted(){
        eventBus.$on('changeBuyPrice',msg=>{
            this.buyprice=msg;
            this.sellprice=msg;
        })
        eventBus.$on('changeSellPrice',msg=>{
           this.sellprice=msg;
           this.buyprice=msg;
        })
    },
    computed:{
        totalAmountBuy(){
             return this.buyprice*this.buynumber;
            // if(this.index==0){
            //     return this.buyprice*this.buynumber;
            // }else{
            //     return this.buynumber;
            // }
        },
        totalAmountSell(){
            if(this.index==0){
                return this.sellprice*this.sellnumber;
            }else{
                return this.legalBalance*this.value2/100;
            }
        }
    },
    methods:{
        // 初始化余额
        initBalance(id,type){
            this.$http.initDataToken({url:'account/detail',data:{currency_id:id,account_type_id:1}},false)
            .then(res=>{
               if(type==0){
                   this.currencyBalance=res.balance;
               }else{
                   this.legalBalance=res.balance;
               }
            })
        },
        buyin(){
            if(this.index==0){
                this.$http.initDataToken({url:'tx_in/add',data:{currency_match_id:this.currency_match_id,number:this.buynumber,price:this.buyprice}},false)
                .then(res=>{
              
                })
            }
        },
        sellout(){

        },
        changetype(i){
            this.index=i;
            if(this.index==0){
                 this.buynumber='';
                this.sellnumber='';
            }else{
                 this.buynumber=this.$refs.newprice;
                 console.log(eventBus)
                this.sellnumber='';
            }
           
            this.value1=0;
            this.value2=0;
        },
        buypriceInput(e){
            this.buyprice=e.target.value;
             console.log(e.target.value)
            this.value1=this.totalAmountBuy/this.legalBalance*100;
        },
        buynumberInput(e){
             console.log(e.target.value)
            this.buynumber=e.target.value;
            this.value1=this.totalAmountBuy/this.legalBalance*100;
        },
         sellpriceInput(e){
            this.sellprice=e.target.value;
             console.log(e.target.value)
            this.value2=this.totalAmountSell/this.legalBalance*100;
        },
        sellnumberInput(e){
             console.log(e.target.value)
            this.sellnumber=e.target.value;
            this.value2=this.totalAmountSell/this.legalBalance*100;
        },
        // 买入滑块
        sliderBuy(e){
            console.log(e);
            if(this.buyprice){
                this.buynumber=this.legalBalance*e/100/this.buyprice;
            }
           
        },
         // 卖出滑块
        sliderSell(e){
           if(this.index==0&&this.sellprice){
                this.sellnumber=this.legalBalance*e/100/this.sellprice;
            }
            if(this.index==1){
                this.sellnumber=this.currencyBalance*e/100;
            }
        }
    }
}
</script>
<style lang="scss">
    .buyslider{
        .el-slider__bar{
            background-color: #009688;
        }  
        .el-slider__button{
            border-color: #009688;
            background-color: #171b2b;
        }
        .el-slider__runway{
            background: #252a44
        }
        .el-slider__stop{
            background: #171b2b
        }
    }  
     .sellslider{
        .el-slider__bar{
            background-color: #cc4951;
        }  
        .el-slider__button{
            border-color: #cc4951;
            background-color: #171b2b;
        }
        .el-slider__runway{
            background: #252a44
        }
        .el-slider__stop{
            background: #171b2b
        }
    }  
</style>
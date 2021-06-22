<template>
    <div class="allmarket">
        <div class="innerbox radius6 w100 flex alcenter column">
            <!-- 分区切换 -->
            <div class="nav_top flex between alcenter ft14 w100 mlr30 plr20 bgf6">
                <div class="flex alcenter left">
                    <p v-for="(item,index) in typeList" :class="['pointer',{'select':topnow==index}]" @click="changeType(index)">
                        <i class="iconfont icon-pingjiaxingxing" style="color:#596a7a" v-if="index==0"></i>
                        {{item}}
                    </p>
                </div>
                <!-- <div class="radius6 ptb5 plr20 bde7 right">
                    <input type="text" placeholder="搜索币种">
                </div> -->
            </div>
            <!-- 法币切换 -->
            <div class="legal_top flex w100 mlr30 bde7" v-show="topnow>0">
                <p v-for="(item,index) in marketList" :class="['pointer',{'select':current==index}]" @click="changeLegal(index,item.name)">{{item.code}}</p>
            </div>
            <!-- 币种详情 -->
            <div class="coinbox flex column alcenter w100 mlr30 bgwhite">
                <div class="classfity flex between alcenter w100 plr20 ptb15 bgwhite bdbe7">
                    <div :class="['flex alcenter flex1',{'jscenter':index!=0},{'jsend':index==5}]" v-for="(item,index) in classtify" @click="tapFilters(index,item.key)">
                        <span>{{item.text}}</span> 
                        <p class="flex column between screen" v-if="item.key!=''">
                            <span :class="['up',{'sort':(index===sortindex)&&direction}]"></span>
                            <span :class="['down',{'sort':index===sortindex&&!direction}]"></span>
                        </p>
                    </div>
                </div>
                <ul class="w100 overyscroll bgwhite">
                    <div v-for="(item,i) in marketList[current].matches" :key="i" class="ft14 flex between alcenter  plr10 ptb15 bgwhite bdbe7">
                        <p class="flex alcenter flex1">
                            <span class="iconfont ft14 gray icon-pingjiaxingxing" v-if="!item.added" ></span>
                            <span class="iconfont ft14 blue icon-pingjiaxingxing" v-else ></span>
                            <span class="ml5">{{item.base_currency_code}}</span>
                        </p>
                        <p class="flex1 tc">
                            <span>{{item.currency_quotation.close}}</span>
                            <span class="ft12">≈ {{Math.floor(item.currency_quotation.close*fiat_convert_cny*100)/100}} CNY</span>
                        </p>
                        <p :class="['flex1','tc','change','green',{'red':item.currency_quotation.change<0}]">
                            <span v-if="item.change>0">
                                +{{item.currency_quotation.change || '0.00' | toFixed2 }}%
                            </span>
                            <span v-else>
                                {{item.currency_quotation.change || '0.00' | toFixed2 }}%
                            </span>
                        </p>
                        <p class="flex1 tc">{{item.currency_quotation.high || 0.00}}</p>
                        <p class="flex1 tc">{{item.currency_quotation.low || 0.00}}</p>
                        <p class="flex1 tr">{{item.currency_quotation.volume}}</p>
                    </div>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
            topnow:0,
            current:0,
            typeList:['全部行情'],
            classtify:[{text:'交易对',key:'currency_name'},{text:'最新价',key:'now_price'},{text:'涨幅',key:'change'},{text:'最高价',key:''},{text:'最低价',key:''},{text:'24H交易额',key:''}],
            marketList:[],
            fiat_convert_cny:6.8,
            legal_name:'',
            sortindex:'',
            direction:true,
        }
    },
    filters: {
		toFixed2: function(value, options) {
			value = Number(value);
			return value.toFixed(2);
		}
	},
    created(){
       this.quotation();
       this.bindSocket();
    },
    methods:{
         // 类型切换
        changeType(index){
            this.topnow=index;
        },
        // 法币切换
        changeLegal(index,legalName){
            this.current=index;
            this.fiat_convert_cny=this.marketLists[index].cny_price-0;
		    this.legal_name = legalName;
        },
        //行情交易对
		quotation() {
            this.$http.initDataToken({
                url: 'market/currency_matches'
            },false).then(res=>{
                console.log(res)
                this.marketList=res;
                if(res.length>0){
                    this.legal_name=res[0].code;
                    this.fiat_convert_cny=res[0].cny_price-0;
                }
                console.log(this.marketList)
            })
        },
         // 链接socket
        bindSocket(){
            var that=this;
            that.$socket.connected((socket)=>{
                socket.send({type: "sub", params: "DAY_MARKET"});
                socket.on('DAY_MARKET',msg=>{
                    // var msg=JSON.parse(msg);
                    console.log(msg)
				    let legal_name = msg.symbol.split('/')[1];
                    if(legal_name==this.legal_name){
                        var currencyQuotation=this.marketList[this.current].matches;
                        console.log(currencyQuotation)
                        for(var i in currencyQuotation){
                            if(currencyQuotation[i].id==msg.currency_match_id){
                                currencyQuotation[i].currency_quotation.low=msg.quotation.low-0;
                                currencyQuotation[i].currency_quotation.high=msg.quotation.high-0;
                                currencyQuotation[i].currency_quotation.volume=msg.quotation.volume-0;
                                currencyQuotation[i].currency_quotation.close=msg.quotation.close-0;
                                currencyQuotation[i].currency_quotation.change=msg.quotation.change;
                            }
                        }
                    }
                })
            })
        },
        // 过滤
        tapFilters(e,name){
				this.direction=e===this.sortindex?!this.direction:false;
                this.sortindex=e;
                this.filterList(name,this.direction);
				console.log(this.direction,this.sortindex)
		},
        filterList(name,isflag){
            this.marketList[this.current].matches.sort(function(i,j){
                if(name=='currency_name'){
                    if(isflag==true){
                        return i['base_currency_code'].charCodeAt(0) - j['base_currency_code'].charCodeAt(0);
                    }else{
                        return j['base_currency_code'].charCodeAt(0) - i['base_currency_code'].charCodeAt(0);
                    }
                }else{
                    if(name=='now_price'){
                        if(isflag==true){
                            return i['currency_quotation'].close-j['currency_quotation'].close;
                        }else{
                            return j['currency_quotation'].close-i['currency_quotation'].close;
                        }
                    }else{
                        if(isflag==true){
                            return i['currency_quotation'].change-j['currency_quotation'].change;
                        }else{
                            return j['currency_quotation'].change-i['currency_quotation'].change;
                        }
                    }
                }
            })
        },
        unbindSocket(){
            this.$socket.send({type: "unsub", params: "DAY_MARKET"});
        },
    },
    destroyed(){
        this.unbindSocket()
    }
}
</script>
<style lang="scss">
.innerbox{
    padding:30px 20px 0;
    .nav_top{
        .left{
            p{
                padding: 10px 10px;
                border-top: 3px solid #fff; 
            }
            .select{
                border-top: 3px solid #357ce1;
                background: #fff;
            }
        }
        .right{
            height: 30px;
        }
    }
    .legal_top{
        padding: 15px 30px;
        background: #fff;
        p{
            line-height: 30px;
            width: 80px;
            text-align: center;
            color: #333;
            margin-left: 20px;
            font-size: 14px;
        }
        .select{
            border-radius: 5px;
            color: #fff;
            background: #357ce1;
        }
    }
    .coinbox{
        height: 700px;
        .classfity{
            padding: 0 10px;
            height:40px;
            line-height:40px;
            color:#9ca9b5;
            >div{
                flex: 1;
                cursor: pointer;
                p{
                    height: 25px;
                    span{
                        width: 0;
                        height: 0;
                        border-width: 5px;
                        border-style: solid;
                        margin-left: 10px;
                    }
                    .up{
                        border-color: transparent transparent #9ca9b5 transparent;
                    }
                    .down{
                        border-color: #9ca9b5 transparent transparent transparent;
                    }
                    .up.sort{
                        border-color: transparent transparent #4e5b85 transparent;
                    }
                    .down.sort{
                        border-color: #4e5b85 transparent transparent transparent;
                    }
                }
            }
        }
    }
}

</style>
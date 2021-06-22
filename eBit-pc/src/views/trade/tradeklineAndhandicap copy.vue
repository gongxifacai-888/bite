<template>
    <div class="flex h100">
        <div class="flex2 flex column mr5">
            <!-- 行情 -->
            <div class="flex2">
                <div class="bgheader bdbheader flex alcenter plr10 ht40">
                    <div class="baselight2 ft16 bold mr20">{{symbol}}</div>
                    <div class="">
                        <div class="ft16" :class="[newpriceInfo.change>=0?'green':'red']">{{newpriceInfo.close}}</div>
                        <div class="ft12">≈{{newpriceInfo.cny_price | filterDecimals}}CNY</div>
                    </div>
                    <div class="flex1 ft12  flex ml30">
                        <div class="mr30">
                            <div class="flex1">{{$t('trade.changes')}}</div>
                            <div class="flex1" :class="[newpriceInfo.change>=0?'green':'red']">{{newpriceInfo.change>=0?'+':''}}{{newpriceInfo.change}}%</div>
                         </div>
                        <div class="mr30">
                            <div class="flex1">{{$t('market.high')}}</div>
                            <div class="flex1 baselight2">{{newpriceInfo.high}}</div>
                        </div>
                        <div class="mr30">
                            <div class="flex1">{{$t('market.low')}}</div>
                            <div class="flex1 baselight2">{{newpriceInfo.low}}</div>
                         </div>
                        <div class="mr30">
                            <div class="flex1">{{$t('market.volume')}}</div>
                            <div class="flex1 baselight2">{{newpriceInfo.volume | filterDecimals}}</div>
                         </div>
                    </div>
                </div>
                <div id="tv_chart_container" style="width:100%;height: 90%" class=""></div>
            </div>
            <!-- 下单 -->
            <div class="flex12 bgpart">
                <div class="bgheader bdbheader flex alcenter plr10  baselight2" >
                    <div class='ptb5 mr10 bold ft14 pointer' :class="{'active2px':i==index}" v-for="(item,i) in list" :key="i" @click='changetype(i)'>{{item}}</div> 
                </div>
                <div class=" flex  ft12">
                    <!-- ?? -->
                    <div class="flex1 plr10 mt10" style="border-right:1px dashed #383f66">
                        <div class="flex alcenter mb10">{{$t('trade.use')}}{{legalBalance}}{{legalName}}</div>
                        <div class="flex alcenter mb10 posRelt">
                            <span class="w15">{{$t('trade.buyPrice')}}</span>
                            <input type="number" v-model="buyprice" class="ht30 bdinput lh30 plr5 radius2 ml10 flex1 white" v-show="index==0" @input='buypriceInput' @keyup='buypriceInputKeyDown($event)'>
                            <input type="text" :placeholder="$t('trade.goodbuy')" class="trade ht30 bgheader lh30 plr5 radius2 ml10 flex1 white" v-show="index==1" disabled>
                            <span class="abstort ht30 lh30 plr5 rt0 btm0">{{legalName}}</span>
                        </div>
                        <div class="flex alcenter mb10 posRelt">
                            <span class="w15" >{{$t('trade.buyNumber')}}</span>
                            <input type="number" v-model="buynumber" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white"  @input='buynumberInput'>
                            <span class="abstort ht30 lh30 plr5 rt0 btm0" v-show="index==0">{{currencyName}}</span>
                            <span class="abstort ht30 lh30 plr5 rt0 btm0" v-show="index!=0">{{currencyName}}</span>
                        </div>
                        <!-- <div class="flex alcenter mb10">
                            <span class="w15">密码</span>
                            <input type="passord" v-model="password" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white">
                        </div> -->
                        <el-slider v-model="value1" :step="25" show-stops class="buyslider ml10" @change="sliderBuy"></el-slider>
                        <div class="flex alcenter mb10">{{$t('trade.amout')}}：{{totalAmountBuy}}{{legalName}}</div>
                        <div class="bdinput tc ht30 lh30  radius2 blue" v-if="!token">
                            <router-link :to="{name:'login'}">{{$t('login.login')}}</router-link>
                            <span class="baselight2">{{$t('trade.or')}}</span>
                            <router-link :to="{name:'register'}">{{$t('register.register')}}</router-link>
                        </div>
                        <div class="bggreen tc ht30 lh30 white radius2 pointer" @click="buyin" v-else>{{$t('trade.buy')}}</div>
                    </div>
                    <!-- 卖盘 -->
                    <div class="flex1 plr10 mt10">
                        <div class="flex alcenter mb10">{{$t('trade.use')}}{{currencyBalance}}{{currencyName}}</div>
                        <div class="flex alcenter mb10 posRelt">
                            <span class="w15">{{$t('trade.sellPrice')}}</span>
                            <input type="number" v-model="sellprice" class="ht30 bdinput lh30 plr5 radius2 ml10 flex1 white" v-show="index==0">
                            <input type="text" :placeholder="$t('trade.goodsell')" class="trade ht30 bgheader lh30 plr5 radius2 ml10 flex1 white" v-show="index==1" disabled>
                            <span class="abstort ht30 lh30 plr5 rt0 btm0">{{legalName}}</span>
                        </div>
                        <div class="flex alcenter mb10 posRelt">
                            <span class="w15">{{$t('trade.sellNumber')}}</span>
                            <input type="number" v-model="sellnumber" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white " @input='sellnumberInput'>
                            <span class="abstort ht30 lh30 plr5 rt0 btm0">{{currencyName}}</span>
                        </div>
                        <!-- <div class="flex alcenter mb10">
                            <span class="w15">密码</span>
                            <input type="passord" v-model="password" class="ht30 bdinput lh30 pl5 radius2 ml10 flex1 white">
                        </div> -->
                        <el-slider v-model="value2" :step="25 " show-stops class="sellslider ml10" @change="sliderSell"></el-slider>
                        <div class="flex alcenter mb10">{{$t('trade.amout')}}：{{totalAmountSell}}{{legalName}}</div>
                        <div class="bdinput tc ht30 lh30  radius2 blue" v-if="!token">
                            <router-link :to="{name:'login'}">{{$t('login.login')}}</router-link>
                            <span class="baselight2">{{$t('trade.or')}}</span>
                            <router-link :to="{name:'register'}">{{$t('register.register')}}</router-link>
                        </div>
                        <div class="bgred tc ht30 lh30 white radius2 pointer" @click="sellout" v-else>{{$t('trade.sell')}}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 盘口和全站 -->
        <div class="flex12 flex column overy">
            <div class="flex">
                <div class="bgheader bdbheader flex1 plr10 ht40 lh40 baselight2 ft16">{{$t('trade.pankou')}}</div>
                <div class="bgheader bdbheader flex1 plr10 ht40 lh40 baselight2 ft16 ml5">{{$t('trade.station')}} </div>
            </div>
            <div class="flex1 flex">
                <div class="flex column w100 flex1 bgpart">
                    <div class="ptb10 flex1 flex column w100">
                        <div class="flex alcenter plr10 ">
                            <span class="flex1">{{$t('trade.direction')}}</span>
                            <span class="flex1 tc">{{$t('trade.price')}}({{legalName}})</span>
                            <span class="flex1 tr">{{$t('trade.num')}}({{currencyName}})</span>
                        </div>
                        <!-- 卖盘 -->
                        <div class="mtb10 flex1 flex column jsend asks">
                            <!-- 没有socket推送的时候 -->
                            <div v-show="outlistSocket.length<=0" class="ptb5 flex alcenter pointer plr10"  v-for='(item,i) in outlist' :key="i" @click="changeSell(item.price)">
                                <span class="flex1 red">{{$t('trade.sell1')}}{{outlist.length-i}}</span>
                                <span class="flex1 tc baselight2">{{item.price}}</span>
                                <span class="flex1 tr baselight2">{{item.amount}}</span>
                            </div>  
                            <div v-show="outlistSocket.length>0"  class="ptb5 flex alcenter pointer h100 plr10 " :style="'background-size:'+parseInt((item.amount/sellMax)*100)+'% 100%'"  v-for='(item,i) in outlistSocket' :key="i"  @click="changeSell(item.price)">
                                <span class="flex1 red">{{$t('trade.sell1')}}{{outlistSocket.length-i}}</span>
                                <span class="flex1 tc baselight2">{{item.price}}</span>
                                <span class="flex1 tr baselight2">{{item.amount}}</span>
                            </div>  
                        </div>
                        <div class="ptb15 bdtheader bdbheader plr10">
                            <span class="red bold ft20" ref='newprice'>{{newpriceInfo.close}}</span>
                            <span class="" >≈{{newpriceInfo.cny_price}}CNY</span>
                        </div>
                        <!-- 买盘 -->
                        <div class="bgpart mt10 flex1 flex column bids">
                            <div v-show="inlistSocket.length<=0" class="ptb5 flex alcenter pointer plr10"  v-for='(item,i) in inlist' :key="i" @click="changeBuy(item.price)">
                                <span class="flex1 green">{{$t('trade.buy1')}}{{i+1}}</span>
                                <span class="flex1 tc baselight2">{{item.price}}</span>
                                <span class="flex1 tr baselight2">{{item.amount}}</span>
                            </div>  
                            <div class="ptb5 flex alcenter pointer h100 plr10" v-show="inlistSocket.length>0" :style="'background-size:'+parseInt((item.amount/buyMax)*100)+'% 100%'" v-for='(item,i) in inlistSocket' :key="i" @click="changeBuy(item.price)">
                                <span class="flex1 green">{{$t('trade.buy1')}}{{i+1}}</span>
                                <span class="flex1 tc baselight2">{{item.price}}</span>
                                <span class="flex1 tr baselight2">{{item.amount}}</span>
                            </div>   
                        </div>
                    </div>
                </div>
                <!-- 全站 -->
                <div class="flex1 ml5 bgpart overy flex">
                    <div class=" ptb10 flex1 flex column w100  " >
                        <div class="flex alcenter plr10">
                            <span class="flex1">{{$t('trade.time')}}</span>
                            <span class="flex1 tc">{{$t('trade.price')}}({{legalName}})</span>
                            <span class="flex1 tr">{{$t('trade.num')}}({{currencyName}})</span>
                        </div>
                       <div class="flex1  mt10 " >
                            <div class="overyscroll plr10" style="height:660px;">
                                <div v-show="completelistSocket.length<=0" >
                                    <div class="ptb5 flex alcenter"  v-for='(item,i) in completelist' :key="i">
                                        <span class="flex1 ">{{item.time}}</span>
                                        <span class="flex1 tc" :class="[item.way==2?'green':'red']">{{item.price}}</span>
                                        <span class="flex1 tr baselight2">{{item.amount}}</span>
                                    </div>  
                                </div>
                                <div v-show="completelistSocket.length>0" class="ptb5 flex alcenter"  v-for='(item,i) in completelistSocket' :key="i">
                                    <span class="flex1 ">{{item.time}}</span>
                                    <span class="flex1 tc " :class="[item.way==2?'green':'red']">{{item.price}}</span>
                                    <span class="flex1 tr baselight2">{{item.amount}}</span>
                                </div>   
                            </div>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props:['symbol','mathid','legalName','currencyName','legalId','currencyId','cny_usd','usd_usd','currency_matchInfo','inlist','outlist','completelist','legalBalance1','currencyBalance1'],
    data() {
        return {
            widget: null,
            symbolInfo: null,
            feed: null,
            wsEx:null,
            ws:null,
            lists:[],
            newData:'',
            // symbol:'BTC/USDT',
            priceScale:10000,
            histime:'',
            loctheme:'',
            lang:'',
            nowprice:'',
            change:'',
            maxprice:'',
            minprice:'',
            volume:'',
            token:'',
            outlistSocket:[],
            inlistSocket:[],
            completelistSocket:[],
            newpriceInfo:{},
            cny_usdprice:'',
            legalname:'',
            currencyname:'',
            // 下单
            list:[this.$t('trade.fixing'),this.$t('trade.marketPrice')],
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
            basedigital:1000000,
        }
    },
     watch:{
        symbol:{
            handler(newInfo,oldInfo){
                if(oldInfo){
                    this.unbindSocket(oldInfo);
                }
            },
            immediate:true
        },
        currency_matchInfo: {
            handler(newInfo, oldInfo) {
                this.newpriceInfo = newInfo;
            },
            // 代表在wacth里声明了firstName这个方法之后立即先去执行handler方法
            immediate: true
        },
        mathid:{
            handler(data1,data2){
                 if(this.token){
                     // 获取当前币余额
                    this.initBalance(this.currencyId,0)
                    // 获取计价币余额
                    this.initBalance(this.legalId,1)
                }
                this.createWidget()
            },
            immediate:true
        },
        legalBalance1:{
            handler(data1,data2){
                this.legalBalance = data1
            },
            immediate:true
        },
        currencyBalance1:{
            handler(data1,data2){
                this.currencyBalance = data1
            },
            immediate:true
        }
    },
    created(){
        this.token=localStorage.getItem('token');
    },
    computed:{
        ...mapState(['theme']),
         totalAmountBuy(){
             let total=Math.ceil(this.buyprice*this.buynumber*this.basedigital)/this.basedigital;
             let balance=this.legalBalance;
            if(balance&&(total>balance)){
                // this.$message('test余额不足')
            }
            // console.log(this.buyprice,this.buynumber,this.buyprice*this.buynumber,total,balance,total>balance)
            return total
        },
        totalAmountSell(){
            return Math.ceil(this.sellprice*this.sellnumber*this.basedigital)/this.basedigital;
        },
        buyMax(){//买盘最大数量
            let list=[],data = this.inlistSocket;
            data.map((item)=>{
                list.push(item.amount)
            });
            return Math.max.apply(null, list)
        },
        sellMax(){//卖盘最大数量
            let list=[],data = this.outlistSocket;
            data.map((item)=>{
                list.push(item.amount)
            });
            return Math.max.apply(null, list)
        },
    },
    mounted() {
        this.lang=localStorage.getItem('lang') || 'zh';
        localStorage.setItem('type2','1min')  //默认为1分钟
        if(this.token){
            this.initBalance(this.currencyId,0)
            this.initBalance(this.legalId,1)
        }
        this.createWidget()
        eventBus.$on("changeBalance", msg => {
            this.initBalance(this.currencyId,0)
            this.initBalance(this.legalId,1)
        });
    },
    destroyed() {
        this.removeWidget();
        // this.unbindSocket(this.symbol);
    },
    beforeDestroy(){

    },
    methods: {
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
        // 买入下单
        buyin(){
            if(this.index==0&&!this.buyprice){
                return this.$message('请输入价格')
            }
            if(this.index==1){
                this.getmarketprice();
                if(!this.buyprice){
                    return this.$message('当前没有最优价')
                }
            }
            if(!this.buynumber){
                return this.$message('请输入数量')
            }
            this.$http.initDataToken({url:'tx_in/add',data:{currency_match_id:this.mathid,number:this.buynumber,price:this.buyprice},type:'POST'})
            .then(res=>{
                this.initBalance(this.currencyId,0)
                this.initBalance(this.legalId,1)
            })
        },
        // 卖出下单
        sellout(){
            if(this.index==0&&!this.sellprice){
                return this.$message('请输入价格')
            }
            if(this.index==1){
                this.getmarketprice();
                if(!this.sellprice){
                    return this.$message('当前没有最优价')
                }
            }
            if(!this.sellnumber){
                return this.$message('请输入数量')
            }
            this.$http.initDataToken({url:'tx_out/add',data:{currency_match_id:this.mathid,number:this.sellnumber,price:this.sellprice},type:'POST'})
            .then(res=>{
                this.initBalance(this.currencyId,0)
                this.initBalance(this.legalId,1)
            })
        },
        changetype(i){
            this.index=i;
            this.buynumber='';
            this.sellnumber='';
            this.value1=0;
            this.value2=0;
            if(this.index==0){
               this.buyprice='';
               this.sellprice='';
            }else{
                this.getmarketprice()
            }
            // console.log(this.buyprice,this.sellprice)
        },
        getmarketprice(){
            if(this.outlistSocket.length>0){
                this.buyprice=this.outlistSocket[this.outlistSocket.length-1].price
            }else{
                if(this.outlist.length>0){
                    this.buyprice=this.outlist[this.outlist.length-1].price
                }else{
                    this.buyprice='';
                }
            }
            if(this.inlistSocket.length>0){
                this.sellprice=this.inlistSocket[0].price
            }else{
                if(this.inlist.length>0){
                    this.sellprice=this.inlist[0].price
                }else{
                    this.sellprice='';
                }
                
            }
        },
        buypriceInputKeyDown(e){
            if(e.key=='e'||e.key=='-'||e.data=='+') {
                // console.log(e.key);
                return false;
            }else{
                return true;
            }
        },
        buypriceInput(e){
            this.buyprice=e.target.value;
            // this.value1=this.totalAmountBuy/this.legalBalance*100;
        },
        buynumberInput(e){
            this.buynumber=e.target.value;
            // this.value1=this.totalAmountBuy/this.legalBalance*100;
        },
         sellpriceInput(e){
            this.sellprice=e.target.value;
            this.value2=this.sellnumber/this.currencyBalance*100;
        },
        sellnumberInput(e){
            this.sellnumber=e.target.value;
            if(e.target.value>this.currencyBalance){
                // this.$message('余额不足')
            }
            this.value2=e.target.value/this.currencyBalance*100;
        },
        // 买入滑块
        sliderBuy(e){
            if(this.buyprice){
                this.buynumber=Math.floor(this.legalBalance*e/100/this.buyprice*this.basedigital)/this.basedigital;
            }else{
                if(this.index==1){
                     this.$message('当前没有最优价')
                }
            }
           
        },
         // 卖出滑块
        sliderSell(e){
            if(this.sellprice){
                this.sellnumber=Math.floor(this.currencyBalance*e/100*this.basedigital)/this.basedigital;
            }else{
                if(this.index==1){
                    this.$message('没有最优价')
                }
                
            }
        },
        
       unbindSocket(symbol){
           this.$socket.send([{type: "unsub",params: "MARKET_DEPTH."+symbol},{type:'unsub',params: "GLOBAL_TX."+symbol},{type:'unsub',params:'KLINE.'+symbol}]);
        //    this.$socket.send([{type:'unsub',params:'KLINE.'+symbol}]);
           this.outlistSocket=[];
           this.inlistSocket=[];
           this.completelistSocket=[];
       },
        unbindSocket2(symbol){
           this.$utils.socket.onopen([{type: "unsub",params: "MARKET_DEPTH."+symbol},{type:'unsub',params: "GLOBAL_TX."+symbol},{type:'unsub',params:'KLINE.'+symbol}]);
           this.outlistSocket=[];
           this.inlistSocket=[];
           this.completelistSocket=[];
       },


    //   更新下单
        changeBuy(price){
            eventBus.$emit('changeBuyPrice',price);
            this.buyprice = price;
        },
        changeSell(price){
            eventBus.$emit('changeSellPrice',price);
            this.sellprice = price;
        },
        connect(real) { //封装推送数据
            var that=this;
            that.$socket.send([{type: "login", params: that.token},{type: "sub",params: "MARKET_DEPTH."+that.symbol},{type:'sub',params: "GLOBAL_TX."+that.symbol},{type:'sub',params:'KLINE.'+that.symbol}])
            that.$socket.on('MARKET_DEPTH',msg=>{
                that.outlistSocket=msg.depth.out.reverse();
                that.inlistSocket=msg.depth.in;
            })
            that.$socket.on('GLOBAL_TX',msg=>{
                var comdata=msg.completes;
                for(var i in comdata){
                    comdata[i].time=that.$utils.formatTime(comdata[i].timestamp).substr(11)
                    that.completelistSocket.unshift(comdata[i])
                }
                that.completelistSocket=that.completelistSocket.slice(0,30);
            })
            that.$socket.on('KLINE',msg=>{
                let obj = {};
                var data=msg.kline;
                var type2=localStorage.getItem('type2');
                if(data.period==type2){
                    obj.open = Number(data.open)
                    obj.low = Number(data.low)
                    obj.high = Number(data.high)
                    obj.close = Number(data.close)
                    obj.volume = Number(data.volume)
                    obj.time = Number(data.time)
                    real(obj)
                }
            })
            
        },
        connect2(real) { //封装推送数据
            var that=this;
            that.$socket.connected((socket)=>{
               socket.onopen([{type: "login", params: that.token},{type: "sub",params: "MARKET_DEPTH."+that.symbol},{type:'sub',params: "GLOBAL_TX."+that.symbol},{type:'sub',params:'KLINE.'+that.symbol}])
               socket.onmessage=res=>{
                    let msg=JSON.parse(res.data);
                    console.log(msg.type);
                    if(msg.type=='MARKET_DEPTH'){
                        that.outlistSocket=msg.depth.out.reverse();
                        that.inlistSocket=msg.depth.in;
                        console.log(that.inlistSocket,6666666666)
                    }
                    if(msg.type=='GLOBAL_TX'){
                        var comdata=msg.completes;
                        for(var i in comdata){
                            comdata[i].time=that.$utils.formatTime(comdata[i].timestamp).substr(11)
                            that.completelistSocket.unshift(comdata[i])
                        }
                        that.completelistSocket.slice(0,30);
                    }
                    if(msg.type=='KLINE'){
                        let obj = {};
                        var data=msg.kline;
                        var type2=localStorage.getItem('type2');
                        if(data.period==type2){
                            obj.open = Number(data.open)
                            obj.low = Number(data.low)
                            obj.high = Number(data.high)
                            obj.close = Number(data.close)
                            obj.volume = Number(data.volume)
                            obj.time = Number(data.time)
                            real(obj)
                        }
                    }
                }
            })
        },
        createWidget() {
            let _this = this;
            this.$nextTick(function () {
                let cssfile = this.loctheme=='white'?'bundles/new_white.css':'bundles/new.css';
                let widget =_this.widget= new TradingView.widget({
                    symbol:_this.symbol,
                    interval: 1,
                    debug: true,
                    fullscreen: false,
                    autosize: true,
                    container_id: "tv_chart_container",
                    // datafeed: new Datafeeds.UDFCompatibleDatafeed("http://demo_feed.tradingview.com"),
                    datafeed:_this.createFeed(),
                    library_path: "./static/tradeview/charting_library/",
                    // library_path: "../../../static/tradeview/charting_library/",

                    custom_css_url: cssfile,
                    locale: 'zh',
                    width: "100%",
                    height: 516,
                    drawings_access: { type: 'black', tools: [ { name: "Regression Trend" } ] },
                    disabled_features: [  //  禁用的功能
                        'header_saveload', 'compare_symbol', 'display_market_status',
                        'go_to_date', 'header_chart_type', 'header_compare', 'header_interval_dialog_button',
                        'header_resolutions', 'header_screenshot', 'header_symbol_search', 'header_undo_redo',
                        'legend_context_menu', 'show_hide_button_in_legend', 'show_interval_dialog_on_key_press',
                        'snapshot_trading_drawings', 'symbol_info', 'timeframes_toolbar', 'use_localstorage_for_settings',
                        'volume_force_overlay','widget_logo'
                    ],
                    enabled_features: [ //  启用的功能（备注：disable_resolution_rebuild 功能用于控制当时间范围为1个月时，日期刻度是否都是每个月1号
                        'dont_show_boolean_study_arguments', 'hide_last_na_study_output', 'move_logo_to_main_pane',
                        'same_data_requery', 'side_toolbar_in_fullscreen_mode', 'disable_resolution_rebuild'
                    ],
                    charts_storage_url: 'http://saveload.tradingview.com',
                    charts_storage_api_version: "1.1",
                    toolbar_bg: "transparent",
                    timezone: "Asia/Shanghai",
                    studies_overrides: {
                        'volume.precision': '1000'
                    },
                    overrides: _this.overrides()
                });

                widget.MAStudies = [];
                widget.selectedIntervalButton = null;
                // widget.setLanguage('en')
                widget.onChartReady(function() { //图表方法
                    // document.getElementById('trade-view').childNodes[0].setAttribute('style', 'display:block;width:100%;height:100%;');
                    //let that =this

                    widget.chart().createStudy('Moving Average', false, true, [15,'close', 0],null,{'Plot.color':'#e843da'});
                    widget.chart().createStudy("MA Cross", false, false, [10, 20]);
                    let buttonArr = [
                        {
                            value: "1min",
                            period: "1",
                            text: "Time",
                            chartType: 3,
                            type:"1min"
                        },
                        {
                            value: "1",
                            period: "1m",
                            text: "1min",
                            chartType:1,
                            type:"1min"
                        },
                        {
                            value: "5",
                            period: "5m",
                            text: "5min",
                            chartType: 1,
                            type:"5min"
                        },
                        {
                            value: "15",
                            period: "15m",
                            text: "15min",
                            chartType: 1,
                            type:"15min"
                        },
                        {
                            value: "30",
                            period: "30m",
                            text: "30min",
                            chartType:1,
                            type:"30min"
                        },
                        {
                            value: "60",
                            period: "60分钟",
                            text: "1hour",
                            chartType: 1,
                            type:"60min"
                        },
                        {
                            value: "1D",
                            period: "1D",
                            text: "1day",
                            chartType: 1,
                            type:"1day"
                        },
                        {
                            value: "1W",
                            period: "1W",
                            text: "1week",
                            chartType:1,
                            type:"1week"
                        }, {
                            value: "1M",
                            period: "1M",
                            text: "1mon",
                            chartType: 1,
                            type:"1mon"
                        }
                    ];
                    let btn = {};
                    let nowTime = '';
                    buttonArr.forEach((v, i) => {
                        // console.log(v)
                        let  button=widget.createButton()
                        button.attr('title',v.text)
                            .addClass("my2")
                            .text(v.text)

                        if(v.text=='1min'){
                            if(_this.loctheme=='light'){
                                button.css({"background-color":"#e5ebf5",'color':'#9ca9b5'})
                            }else{
                                button.css({"background-color":"#344568",'color':'#ffffff'})
                            }
                            // localStorage.setItem('tim','1')  //默认为1分钟
                            localStorage.setItem('type2','1min')  //默认为1分钟
                        }

                        // console.log($(this),'999999')
                        btn = button.on("click", function (e) {
                            $(this).parents(".left").children().find(".my2").removeAttr("style"); //去掉1分钟的
                            handleClick(e, v.value,v.type);
                            widget.chart().setChartType(v.chartType); //改变K线类型
                        });

                    });
                    let handleClick = (e, value,type) => {
                        _this.setSymbol = function(symbol,value){
                            gh.chart().setSymbol(symbol,value);
                        };
                        // localStorage.setItem('tim',value)
                        localStorage.setItem('type2',type)
                        widget.chart().setResolution(value,function onReadyCallback(){});  //改变分辨率

                        $(e.target)
                            .addClass("mydate")
                            .closest("div.space-single")
                            .siblings("div.space-single")
                            .find("div.button")
                            .removeClass("mydate")
                    };

                });


                _this.widget = widget;
            })
        },
        createFeed(){
            let this_vue = this;
            let Datafeed = {};


            Datafeed.DataPulseUpdater = function(datafeed, updateFrequency) {
                this._datafeed = datafeed;
                this._subscribers = {};

                this._requestsPending = 0;
                var that = this;

                var update = function() {
                    if (that._requestsPending > 0) {
                        return;
                    }

                    for (var listenerGUID in that._subscribers) {
                        var subscriptionRecord = that._subscribers[listenerGUID];
                        var resolution = subscriptionRecord.resolution;

                        var datesRangeRight = parseInt((new Date().valueOf()) / 1000);
                        

                        //	BEWARE: please note we really need 2 bars, not the only last one
                        //	see the explanation below. `10` is the `large enough` value to work around holidays
                        var datesRangeLeft = datesRangeRight - that.periodLengthSeconds(resolution, 10);

                        that._requestsPending++;

                        (function(_subscriptionRecord) { // eslint-disable-line
                            that._datafeed.getBars(_subscriptionRecord.symbolInfo, resolution, datesRangeLeft, datesRangeRight, function(bars) {
                                    that._requestsPending--;

                                    //	means the subscription was cancelled while waiting for data
                                    if (!that._subscribers.hasOwnProperty(listenerGUID)) {
                                        return;
                                    }

                                    if (bars.length === 0) {
                                        return;
                                    }

                                    var lastBar = bars[bars.length - 1];
                                    if (!isNaN(_subscriptionRecord.lastBarTime) && lastBar.time < _subscriptionRecord.lastBarTime) {
                                        return;
                                    }

                                    var subscribers = _subscriptionRecord.listeners;

                                    //	BEWARE: this one isn't working when first update comes and this update makes a new bar. In this case
                                    //	_subscriptionRecord.lastBarTime = NaN
                                    var isNewBar = !isNaN(_subscriptionRecord.lastBarTime) && lastBar.time > _subscriptionRecord.lastBarTime;

                                    //	Pulse updating may miss some trades data (ie, if pulse period = 10 secods and new bar is started 5 seconds later after the last update, the
                                    //	old bar's last 5 seconds trades will be lost). Thus, at fist we should broadcast old bar updates when it's ready.
                                    if (isNewBar) {
                                        if (bars.length < 2) {
                                            throw new Error('Not enough bars in history for proper pulse update. Need at least 2.');
                                        }

                                        var previousBar = bars[bars.length - 2];
                                        for (var i = 0; i < subscribers.length; ++i) {
                                            subscribers[i](previousBar);
                                        }
                                    }

                                    _subscriptionRecord.lastBarTime = lastBar.time;

                                    for (var i = 0; i < subscribers.length; ++i) {
                                        subscribers[i](lastBar);
                                    }
                                },

                                //	on error
                                function() {
                                    that._requestsPending--;
                                });
                        })(subscriptionRecord);
                    }
                };

                if (typeof updateFrequency != 'undefined' && updateFrequency > 0) {
                    setInterval(update, updateFrequency);
                }
            };

            Datafeed.DataPulseUpdater.prototype.periodLengthSeconds = function(resolution, requiredPeriodsCount) {
                var daysCount = 0;

                if (resolution === 'D') {
                    daysCount = requiredPeriodsCount;
                } else if (resolution === 'M') {
                    daysCount = 31 * requiredPeriodsCount;
                } else if (resolution === 'W') {
                    daysCount = 7 * requiredPeriodsCount;
                } else {
                    daysCount = requiredPeriodsCount * resolution / (24 * 60);
                }

                return daysCount * 24 * 60 * 60;
            };

            Datafeed.DataPulseUpdater.prototype.subscribeDataListener = function(symbolInfo, resolution, newDataCallback, listenerGUID) {
                this._datafeed._logMessage('Subscribing ' + listenerGUID);

                if (!this._subscribers.hasOwnProperty(listenerGUID)) {
                    this._subscribers[listenerGUID] = {
                        symbolInfo: symbolInfo,
                        resolution: resolution,
                        lastBarTime: NaN,
                        listeners: []
                    };
                }

                this._subscribers[listenerGUID].listeners.push(newDataCallback);
            };

            Datafeed.DataPulseUpdater.prototype.unsubscribeDataListener = function(listenerGUID) {
                this._datafeed._logMessage('Unsubscribing ' + listenerGUID);
                delete this._subscribers[listenerGUID];
            };

            Datafeed.Container = function(updateFrequency){
                this._configuration = {
                    supports_search: false,
                    supports_group_request: false,
                    supported_resolutions: ['1', '3', '5', '15', '30', '60', '120', '240', '360', '720', '1D', '3D', '1W', '1M'],
                    supports_marks: true,
                    supports_timescale_marks: true,
                    exchanges: ['gh']
                };

                this._barsPulseUpdater = new Datafeed.DataPulseUpdater(this, updateFrequency || 10 * 1000);
                // this._quotesPulseUpdater = new Datafeed.QuotesPulseUpdater(this);

                this._enableLogging = true;
                this._callbacks = {};

                this._initializationFinished = true;
                this._fireEvent('initialized');
                this._fireEvent('configuration_ready');
            };

            Datafeed.Container.prototype._fireEvent = function(event, argument) {
                if (this._callbacks.hasOwnProperty(event)) {
                    var callbacksChain = this._callbacks[event];
                    for (var i = 0; i < callbacksChain.length; ++i) {
                        callbacksChain[i](argument);
                    }

                    this._callbacks[event] = [];
                }
            };

            Datafeed.Container.prototype._logMessage = function(message) {
                if (this._enableLogging) {
                    var now = new Date();
                    // console.log("CHART LOGS: "+now.toLocaleTimeString() + '.' + now.getMilliseconds() + '> ' + message);
                }
            };

            Datafeed.Container.prototype.on = function(event, callback) {
                if (!this._callbacks.hasOwnProperty(event)) {
                    this._callbacks[event] = [];
                }

                this._callbacks[event].push(callback);
                return this;
            };

            Datafeed.Container.prototype.onReady = function(callback) {
                let that = this;
                if (this._configuration) {
                    setTimeout(function() {
                        callback(that._configuration);
                    }, 0);
                }
                else {
                    this.on('configuration_ready', function() {
                        callback(that._configuration);
                    });
                }
            };

            Datafeed.Container.prototype.resolveSymbol = function(symbolName, onSymbolResolvedCallback, onResolveErrorCallback) {
                this._logMessage("GOWNO :: resolve symbol "+ symbolName);
                Promise.resolve().then(() => {
                    // console.log(this_vue.priceScale,'12345s313123122adaslast')


                    // this._logMessage("GOWNO :: onResultReady inject "+'AAPL');
                    // console.log(this_vue.priceScale,'123stf')
                    onSymbolResolvedCallback({
                        "name": this_vue.symbol,
                        "timezone": "Asia/Shanghai",
                        "pricescale": this_vue.priceScale,
                        "minmov": 1, //minmov(最小波动), pricescale(价格精度), minmove2, fractional(分数)
                        "minmov2": 0,//这是一个神奇的数字来格式化复杂情况下的价格。
                        "ticker": this_vue.symbol,
                        "description": "",
                        "type": "bitcoin",
                        "volume_precision": 8,
                        // "exchange-traded": "sdt",
                        // "exchange-listed": "sdt",
                        //现在，这两个字段都为某个交易所的略称。将被显示在图表的图例中，以表示此商品。目前此字段不用于其他目的。
                        "has_intraday": true,
                        "has_weekly_and_monthly": true,
                        "has_no_volume": false, //布尔表示商品是否拥有成交量数据。
                        'session': '24x7',
                        'supported_resolutions': ['1', '3', '5', '15', '30', '60', '120', '240', '360', '720', '1D', '3D', '1W', '1M']

                    });
                })
            };


            //初始化数据
            Datafeed.Container.prototype.getBars = function(symbolInfo, resolution, rangeStartDate, rangeEndDate, onHistoryCallback, onErrorCallback) {
                // if (rangeStartDate > 0 && (rangeStartDate + '').length > 10) {
                //   throw new Error(['Got a JS time instead of Unix one.', rangeStartDate, rangeEndDate]);
                // }
                if(resolution.indexOf('D')==-1&&resolution.indexOf('W')==-1&&resolution.indexOf('M')==-1){
                    resolution=resolution+'min'
                }else if(resolution.indexOf('W')!=-1||resolution.indexOf('M')!=-1){
                    resolution=resolution
                }
                if(resolution=='1D'){
                    resolution='1day'
                }
                if(resolution=='1W'){
                     resolution='1week'
                }
                if(resolution=='1M'){
                     resolution='1mon'
                }
                this_vue.$http.initDataToken({url:'market/kline',
                    data:{from:rangeStartDate,to:rangeEndDate,symbol:symbolInfo.name,period:resolution,currency_match_id:this_vue.mathid}
                },false)
                .then(res=>{
                    if(res.length>0){
                         res.forEach((item,i)=>{
                            item.open=Number(item.open)
                            item.close=Number(item.close)
                            item.high=Number(item.high)
                            item.low=Number(item.low)
                        })
                        onHistoryCallback(res,{noData:false})
                        onHistoryCallback([],{noData:true})
                    }else{
                        onHistoryCallback([],{noData:true})
                    }
                })
               

            };
            //实时数据
            Datafeed.Container.prototype.subscribeBars = function(symbolInfo, resolution, onRealtimeCallback, listenerGUID, onResetCacheNeededCallback) {
                this_vue.connect(onRealtimeCallback)

                //this._barsPulseUpdater.subscribeDataListener(symbolInfo, resolution, onRealtimeCallback, listenerGUID, onResetCacheNeededCallback);
            };

            Datafeed.Container.prototype.unsubscribeBars = function(listenerGUID) {

                this._barsPulseUpdater.unsubscribeDataListener(listenerGUID);

            };

            return new Datafeed.Container;
        },
        updateData(data) {
            if (data) {
                this.$emit('real-time', data);
            }
        },

        updateWidget(item) {
            this.symbolInfo = {
                name: item,
                ticker: item,
                description: "",
                session: "24x7",
                supported_resolutions: ['1', '5', '15', '30', '60', '240', '1D', '3D', '1W', '1M'],
                has_intraday: true,
                has_daily: true,
                has_weekly_and_monthly: true,
                timezone: "UTC",
            }

            this.removeWidget();
            this.createWidget();
        },
        removeWidget() {
            if (this.widget) {
                // this.widget.remove();
                this.widget = null;
            }
        },
        overrides() {
            let style;
            if(this.loctheme == 'white'){
                style = {
                    up: "#589065",
                    down: "#AE4E54",
                    bg: "#ffffff",
                    grid: "#f9f9fa",
                    cross: "#f9f9fa",
                    border: "#b1b7be",
                    text: "#61688A",
                    areatop: "rgba(122, 152, 247, .1)",
                    areadown: "rgba(122, 152, 247, .02)"
                };
            }else{
                style = {
                    up: "#589065",
                    down: "#AE4E54",
                    bg: "#171b2b",
                    grid: "#1E2740",
                    cross: "#1E2740",
                    border: "#4e5b85",
                    text: "#61688A",
                    areatop: "rgba(122, 152, 247, .1)",
                    areadown: "rgba(122, 152, 247, .02)"
                };
            }
            return{
                'volumePaneSize': "medium", //large, medium, small, tiny
                'paneProperties.topMargin':'20',
                "scalesProperties.lineColor": style.text,
                "scalesProperties.textColor": style.text,
                "paneProperties.background": style.bg,//改变背景色的重要代码
                "paneProperties.vertGridProperties.color": style.grid,
                "paneProperties.horzGridProperties.color": style.grid,
                "paneProperties.crossHairProperties.color": style.cross,
                "paneProperties.legendProperties.showLegend": true,
                "paneProperties.legendProperties.showStudyArguments": true,
                "paneProperties.legendProperties.showStudyTitles": true,
                "paneProperties.legendProperties.showStudyValues": true,
                "paneProperties.legendProperties.showSeriesTitle": true,
                "paneProperties.legendProperties.showSeriesOHLC": true,
                "mainSeriesProperties.candleStyle.upColor": style.up,
                "mainSeriesProperties.candleStyle.downColor": style.down,
                "mainSeriesProperties.candleStyle.drawWick": true,
                "mainSeriesProperties.candleStyle.drawBorder": true,
                "mainSeriesProperties.candleStyle.borderColor": style.border,
                "mainSeriesProperties.candleStyle.borderUpColor": style.up,
                "mainSeriesProperties.candleStyle.borderDownColor": style.down,
                "mainSeriesProperties.candleStyle.wickUpColor": style.up,
                "mainSeriesProperties.candleStyle.wickDownColor": style.down,
                "mainSeriesProperties.candleStyle.barColorsOnPrevClose": false,
                "mainSeriesProperties.hollowCandleStyle.upColor": style.up,
                "mainSeriesProperties.hollowCandleStyle.downColor": style.down,

                "mainSeriesProperties.hollowCandleStyle.drawWick": true,
                "mainSeriesProperties.hollowCandleStyle.drawBorder": true,
                "mainSeriesProperties.hollowCandleStyle.borderColor": style.border,
                "mainSeriesProperties.hollowCandleStyle.borderUpColor": style.up,
                "mainSeriesProperties.hollowCandleStyle.borderDownColor": style.down,
                "mainSeriesProperties.hollowCandleStyle.wickColor": style.line,
                "mainSeriesProperties.haStyle.upColor": style.up,
                "mainSeriesProperties.haStyle.downColor": style.down,
                "mainSeriesProperties.haStyle.drawWick": true,
                "mainSeriesProperties.haStyle.drawBorder": true,
                "mainSeriesProperties.haStyle.borderColor": style.border,
                "mainSeriesProperties.haStyle.borderUpColor": style.up,
                "mainSeriesProperties.haStyle.borderDownColor": style.down,
                "mainSeriesProperties.haStyle.wickColor": style.border,
                "mainSeriesProperties.haStyle.barColorsOnPrevClose": false,
                "mainSeriesProperties.barStyle.upColor": style.up,
                "mainSeriesProperties.barStyle.downColor": style.down,
                "mainSeriesProperties.barStyle.barColorsOnPrevClose": false,
                "mainSeriesProperties.barStyle.dontDrawOpen": false,
                "mainSeriesProperties.lineStyle.color": style.border,
                "mainSeriesProperties.lineStyle.linewidth": 1,
                "mainSeriesProperties.lineStyle.priceSource": "close",
                "mainSeriesProperties.areaStyle.color1": style.areatop,
                "mainSeriesProperties.areaStyle.color2": style.areadown,
                "mainSeriesProperties.areaStyle.linecolor": style.border,
                "mainSeriesProperties.areaStyle.linewidth": 1,
                "mainSeriesProperties.areaStyle.priceSource": "close"
            }
            // this.sty2()
        },


        chose(){
            // console.log(this.widget,'000')
            this.widget.setLanguage('en') //设置语言

            // this.$store.commit('symbol','sdt')
            // this.$store.state.symbol='sdt6456' //切换交易对
        }
    },

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
    .asks>div{
		background-image: linear-gradient(rgba(250,82,82,.1),rgba(250,82,82,.1));
	}
	.bids>div{
		background-image: linear-gradient(rgba(18,184,134,.1),rgba(18,184,134,.1));
	}
	.asks>div,.bids>div{
		background-position: 100%;
		background-repeat: no-repeat;
		background-size: 0% 100%;
	}
</style>
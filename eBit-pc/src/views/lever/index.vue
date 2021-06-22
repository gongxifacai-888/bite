<template>
  <div class="w100 baselight">
    <div class="flex mt5 vh0">
      <!-- 左侧行情 -->
      <div class="wt240 bd mr5 bgpart ptb10 radius2 h0">
        <div class="flex alcenter plr10">
          <div class="plr5 ht25 lh25 tc ft12 baselight2 radius2 mr10 pointer" :class="{ 'bgblue txtwhite': currentIndex == i }"  v-for="(item, i) in list" :key="item.id" @click="changelegal(i)">{{ item.code }}</div>
        </div>
        <div class="flex alcenter mt10 mb10">
          <span class="flex1 pl10">{{$t('trade.currency')}}</span>
          <span class="flex1 tr">{{$t('trade.new_price')}}</span>
          <span class="flex1 tr pr10">{{$t('trade.changes')}}</span>
        </div>
        <div class="overyscroll">
          <div v-if="list.length > 0 && list[currentIndex]">
            <div
              class="flex alcenter ptb5 markethover pointer"
              :class="{ bgshadow: itm.symbol == currentSymbol }"
              v-for="(itm, j) in list[currentIndex].matches"
              :key="j"
              v-show="itm.open_lever == 1"
              @click="changeSymbol(j)"
            >
              <div class="flex1 pl10 baselight2">{{ itm.base_currency_code }}</div>
              <div
                class="flex1 tr baselight2"
                v-if="itm.currency_quotation"
              >{{ itm.currency_quotation.close }}</div>
              <div
                class="flex1 tr pr10"
                :class="[itm.currency_quotation.change >= 0 ? 'green' : 'red']"
                v-if="itm.currency_quotation"
              >
                <span
                  v-if="
                    itm.currency_quotation.change >= 0
                  "
                >+</span>
                {{ itm.currency_quotation.change || "0.00" | filterDecimals }}%
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="flex25 flex column h0 mr5" v-if="mathid">
        <!-- k线 -->
        <LeverKline
          class="flex2 bgpart"
          :cny_usd="cny_usd"
          :currency_matchInfo="currency_matchInfo"
          ref="klineChild"
        ></LeverKline>
        <!-- 下单 -->
        <LeverDoorder
          class="flex12 mt5 bgpart"
          :order_data="orderData"
          :currency_matchInfo="currency_matchInfo"
          @func="orderSuccess"
        ></LeverDoorder>
      </div>
      <!-- 盘口 -->
      <LeverHandicap
        class="flex15 h0"
        v-if="mathid"
        :cny_usd="cny_usd"
        :inlist="inlist"
        :outlist="outlist"
        :currency_matchInfo="currency_matchInfo"
      ></LeverHandicap>
    </div>
    <!-- 订单记录 -->
    <LeverHistery
      :currentDelegation="currentDelegation"
      :currency_matchInfo="currency_matchInfo"
      :historyData="historyData"
      class="mt5 bgpart"
      @history="changePage"
      @closeLever="leverCannel"
    ></LeverHistery>
  </div>
</template>
<script>
import LeverKline from "@/views/lever/leverKline.vue";
import LeverDoorder from "@/views/lever/leverDoorder.vue";
import LeverHandicap from "@/views/lever/leverHandicap.vue";
import LeverHistery from "@/views/lever/leverHistery.vue";
export default {
  components: {
    LeverKline,
    LeverDoorder,
    LeverHistery,
    LeverHandicap
  },
  data() {
    return {
      list: [],
      currentIndex: 0,
      currentSymbol: "",
      mathid: "",
      currencyId: "",
      legalId: "",
      currencyName: "",
      legalName: "",
      cny_usd: "",
      inlist: [],
      outlist: [],
      completelist: [],
      currency_matchInfo: {},
      orderData: {},
      currentDelegation: [],
      page: 1,
      historyData: {
        currentPosition: [],
        orderList: [],
        currentPositionTotal: 0,
        orderListTotal: 0,
        risk: 0,
        totalRisk: 0,
        currentPositionPer:0,
        orderListPer:0,
      },
      types: 5
    };
  },
  created() {
    if(!localStorage.getItem('token')){
      this.$utils.layerMsg('请登录后进行交易');
      this.$router.push({name:'login'})
      return;
    }
    this.getmathes();
    // this.bindSocket();
  },
  mounted(){
    // this.bindSocket()
  },
  methods: {
    // 获取左侧行情信息
    getmathes() {
      var that = this;
      that.$http
        .initDataToken({ url: "market/currency_matches" }, false)
        .then(res => {
          // 判断支持合约交易的法币
          that.list = res.filter(item => item.is_quote == 1);
          // that.list = res;
          // 判断是否存储过信息
          var localStatus = false;
          var data3 = [];
          var data1 = [];
          var data2 = [];
          if (that.list.length > 0) {
            if (
              localStorage.getItem("leverData") &&
              localStorage.getItem("leverData") != undefined
            ) {
              var leverData = localStorage.getItem("leverData") || "";
              data1 = JSON.parse(leverData);
              data2 = res.filter(item => item.id == data1.quote_currency_id);
              if (data2) {
                data3 = data2[0].matches.filter(
                  item =>
                    item.open_lever == 1 &&
                    item.base_currency_id == data1.base_currency_id
                );

                localStatus = true;
              }
            }
            if (localStatus) {
              that.cny_usd = data2[0].cny_price;
              that.currency_matchInfo = data3[0];
            } else {
              that.cny_usd = res[0].cny_price;
              var datas = that.list[0].matches.filter(
                item => item.open_lever == 1
              )[0];
              that.currency_matchInfo = datas;
            }
            localStorage.setItem(
              "leverData",
              JSON.stringify(that.currency_matchInfo)
            );
            that.getcurmatchinfo(that.currency_matchInfo);
            that.bindSocket();
          }
        });
    },
    bindSocket(){
      var that=this;
      var tokens = localStorage.getItem("token");
      that.$socket.connected((socket)=>{
        // 行情socket
        socket.send([{ type: "login", params: tokens },{type: "sub", params: "DAY_MARKET"},{type: "sub", params: "LEVER_TRADE"},{type: "sub", params: "LEVER_CLOSED"}]);
        socket.on('DAY_MARKET',msg=>{
        var datas = that.list[that.currentIndex].matches;
        datas.forEach((item, index) => {
          if (item.currency_quotation.currency_match_id == msg.currency_match_id) {
            that.list[that.currentIndex].matches[
              index
            ].currency_quotation.change = msg.quotation.change;
            that.list[that.currentIndex].matches[
              index
            ].currency_quotation.close = msg.quotation.close;
            }
          });
        })
        //  盘口socket
        that.depthSocket();
        // that.$socket.send([{type: "login", params: tokens},{type: "sub",params: "MARKET_DEPTH."+that.currency_matchInfo.symbol},{type:'sub',params: "GLOBAL_TX."+that.currency_matchInfo.symbol},{type:'sub',params:'KLINE.'+that.currency_matchInfo.symbol}])
        // that.$socket.on('MARKET_DEPTH',msg=>{
        //   that.outlist = msg.depth.out.reverse();
        //   that.inlist = msg.depth.in;
        // })
        // 未平仓单子socket
        socket.on('LEVER_TRADE',msg=>{
          // let data1 = JSON.parse(msg);
          let data1 = msg;
          var data2 = data1.trades_all;
          var datas = [];
          if (that.types == 5) {
            datas = that.historyData.currentPosition;
            if (
              that.currency_matchInfo.currency_quotation.currency_match_id ==
              msg.currency_match_id
            ) {
              that.historyData.risk = msg.hazard_rate;
              that.historyData.totalRisk = msg.profits_all;
            }
          } else {
            datas = that.historyData.orderList;
          }
          data2.forEach((item, index) => {
            datas.forEach((itm, inx) => {
              if (
                itm.base_currency_id == item.base_currency_id &&
                itm.quote_currency_id == item.quote_currency_id &&
                item.id == itm.id
              ) {
                if (that.types == 5) {
                  that.historyData.currentPosition.splice(inx, 1, item);
                } else {
                  that.historyData.orderList.splice(inx, 1, item);
                }
              }
            });
          });
        });
        // 单子已经平仓
        socket.on('LEVER_CLOSED',msg=>{
          var data2 = msg.id;
          var datas = [];
          if (that.types == 5) {
            datas = that.historyData.currentPosition;
          } else if (that.types == 6) {
            datas = that.currentDelegation;
          } else {
            datas = that.historyData.orderList;
          }
          data2.forEach((item, index) => {
            datas.forEach((itm, inx) => {
              if (item == itm.id) {
                if (that.types == 5) {
                  that.historyData.currentPosition.splice(inx, 1);
                } else if (that.types == 6) {
                  that.currentDelegation.splice(inx, 1);
                } else {
                  that.historyData.orderList.splice(inx, 1);
                }
              }
            });
          });
        });
      })      
    },
     // 盘口socket
    depthSocket(){
      let that = this;
      that.$socket.send([{type: "login", params: localStorage.getItem("token")},{type: "sub",params: "MARKET_DEPTH."+that.currency_matchInfo.symbol}])
      that.$socket.on('MARKET_DEPTH',msg=>{
        that.outlist = msg.depth.out.reverse();
        that.inlist = msg.depth.in;
      })
    },
    // 获取合约交易信息
    getDefault() {
      var that = this;
      that.$http
        .initDataToken(
          {
            url: "lever/deal",
            data: {
              currency_match_id:
                that.currency_matchInfo.currency_quotation.currency_match_id
            },
            type: "POST"
          },
          false
        )
        .then(res => {
          that.orderData = res;
          // that.inlist = res.lever_transaction.in;
          // that.outlist = res.lever_transaction.out.reverse();
          that.currentDelegation = res.my_transaction;
        });
    },
    getcurmatchinfo(temp) {
      var that = this;
      that.currentSymbol = temp.symbol;
      that.mathid = temp.currency_quotation.currency_match_id;
      that.currencyId = temp.base_currency_id;
      that.legalId = temp.quote_currency_id;
      that.currencyName = temp.base_currency_code;
      that.legalName = temp.quote_currency_code;
      // 获取合约交易信息
      that.getDefault();
      that.getCurrent();
    },
    // 改变当前交易对
    changeSymbol(j) {
      var that = this;
      this.unbindSocket();
      var curmatch = that.list[that.currentIndex].matches[j];
      that.currency_matchInfo = curmatch;
      localStorage.setItem(
        "leverData",
        JSON.stringify(that.currency_matchInfo)
      );
      this.depthSocket();
      that.getcurmatchinfo(curmatch);
    },
    // 改变法币
    changelegal(i){
        this.currentIndex = i;
    },
    // 下单成功
    orderSuccess(data) {
      console.log(data);
      var that = this;
      if (data) {
        if (that.types == 6) {
          that.getDefault();
        } else if (that.types == 5) {
          that.page = 1;
          that.getCurrent();
        } else {
          that.page = 1;
          that.getOrder();
        }
      }
    },
    // 平仓成功
    leverCannel(data) {
      var that = this;
      var data1 = that.currentDelegation;
      var data2 = that.historyData.currentPosition;
      var data3 = that.historyData.orderList;
      that.currentDelegation = data1.filter(item => item.id != data);
      that.historyData.currentPosition = data2.filter(item => item.id != data);
      that.historyData.orderList = data3.filter(item => item.id != data);
    },

    // 订单记录更改页码
    changePage(data) {
      var that = this;
      that.page = data.pages;
      that.types = data.types;
    },
    // 获取当前持仓
    getCurrent() {
      var that = this;
      that.historyData.currentPosition = [];
      if (that.page == 1) {
        that.historyData.currentPositionTotal = 0;
      }

      that.$http
        .initDataToken(
          {
            url: "lever/dealall",
            type: "POST",
            data: {
              currency_match_id:
                that.currency_matchInfo.currency_quotation.currency_match_id,
              page: that.page
            }
          },
          false
        )
        .then(res => {
          console.log(res);
          that.historyData.currentPosition = res.order.data;
          that.historyData.currentPositionTotal = res.order.total;
          that.historyData.currentPositionPer = res.order.per_page;
        });
    },
    // 获取全部订单
    getOrder() {
      var that = this;
      that.historyData.orderList = [];
      if (that.page == 1) {
        that.historyData.orderListTotal = 0;
      }
      that.$http
        .initDataToken(
          {
            url: "lever/my_trade",
            type: "POST",
            data: {
              status: that.types,
              page: that.page
            }
          },
          false
        )
        .then(res => {
          // console.log(res);
          that.historyData.orderList = res.data;
          that.historyData.orderListTotal = res.total;
          that.historyData.orderListPer=res.per_page;
        });
    },
    unbindSocket(){
      let that = this;
      this.$socket.send([{type: "unsub",params: "MARKET_DEPTH."+that.currency_matchInfo.symbol}]);
    },
  },
  beforeRouteLeave(to, from, next){
    next();
    this.$socket.send([{type: "unsub", params: "DAY_MARKET"},{type: "unsub", params: "LEVER_TRADE"},{type: "unsub", params: "LEVER_CLOSED"}]);     
    this.unbindSocket(); 
    let symbol = this.currencyName+'/'+this.legalName;
    this.$refs.klineChild.unbindSocket(symbol);
  }
};
</script>

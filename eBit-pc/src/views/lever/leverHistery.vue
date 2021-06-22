<template>
  <div>
    <div class="flex bgheader ht40 lh40 baselight2 ft16">
      <!-- <div class="plr40 pointer" :class="types==6?'bgpart':''" @click="changeType(6)">{{$t('lever.dedan')}}</div> -->
      <div class="plr40 pointer" :class="types==5?'bgpart':''" @click="changeType(5)">{{$t('lever.current')}}</div>
      <div class="plr40 pointer" :class="types==1?'bgpart':''" @click="changeType(1)">{{$t('lever.allPosition')}}</div>
      <div class="plr40 pointer" :class="types==0?'bgpart':''" @click="changeType(0)">{{$t('lever.allWei')}}</div>
      <div class="plr40 pointer" :class="types==3?'bgpart':''" @click="changeType(3)">{{$t('lever.allClose')}}</div>
      <div class="plr40 pointer" :class="types==4?'bgpart':''" @click="changeType(4)">{{$t('lever.allBack')}}</div>
    </div>
    <div class="bgpart plr15 pb30">
      <!-- 当前挂单 -->
      <ul v-if="types==6">
        <li class="flex alcenter ptb10">
          <span class="flex1">{{$t('lever.types')}}</span>
          <span class="flex2 tc">{{$t('lever.time')}}</span>
          <span class="flex1 tc">{{$t('lever.price')}}</span>
          <span class="flex1 tc">{{$t('lever.amout')}}</span>
          <span class="flex1 tr">{{$t('lever.operate')}}</span>
        </li>
        <li v-for="(item,index) in currentDelegation" :key="index" class="flex alcenter ptb10">
          <span class="flex1">{{item.type_name}}</span>
          <span class="flex2 tc">{{item.time}}</span>
          <span class="flex1 tc">{{item.price}}</span>
          <span class="flex1 tc">{{item.number}}</span>
          <span class="flex1 tr blue pointer" @click="leverClose(item.id)">{{$t('lever.chedan')}}</span>
        </li>
        <li v-if="currentDelegation.length ==0">
          <div class="tc ptb40">
            <img src="../../assets/images/nodata.png" alt class="wt70" />
            <p>{{$t('lever.noData')}}</p>
          </div>
        </li>
      </ul>
      <!-- 当前持仓 -->
      <ul v-else-if="types==5">
        <li class="flex alcenter between mt10 mb10">
          <div>
            <p>{{$t('lever.risk')}}：{{historyData.risk || '0.00'}}%</p>
            <p class="mt10">
              {{$t('lever.totalyk')}}：
              <span
                :class="historyData.totalRisk < 0?'red':'green'"
              >{{historyData.totalRisk || '0.0000'}}</span>
            </p>
          </div>
          <p
            class="pointer plr15 tc ft12 baselight2 radius2 bgblue txtwhite ptb5"
            @click="allCloseShow=true"
          >{{$t('lever.yijian')}}</p>
        </li>
        <li class="flex alcenter ptb10">
          <span class="flex2">{{$t('lever.types')}}</span>
          <span class="flex1 tc">{{$t('lever.price_cang')}}</span>
          <!-- <span class="flex1 tc">{{$t('lever.price_cur')}}</span> -->
          <span class="flex1 tc">{{$t('lever.money')}}</span>
          <span class="flex1 tc">{{$t('lever.price_zhiying')}}</span>
          <span class="flex1 tc">{{$t('lever.price_lose')}}</span>
          <span class="flex2 tc">{{$t('lever.time_start')}}</span>
          <span class="flex1 tc">{{$t('lever.fee')}}</span>
          <span class="flex1 tc">{{$t('lever.geye_fee')}}</span>
          <span class="flex1 tc">{{$t('lever.loss')}}</span>
          <span class="flex2 tr">{{$t('lever.operate')}}</span>
        </li>
        <li
          class="flex alcenter ptb10"
          v-for="(item,index) in historyData.currentPosition"
          :key="index"
        >
          <p class="flex2">
            <span :class="item.type==1?'green':'red'">{{item.type_name}}</span>
            {{item.symbol}} X{{item.share}}手
          </p>
          <span class="flex1 tc">{{item.price}}</span>
          <!-- <span class="flex1 tc">{{item.update_price}}</span> -->
          <span class="flex1 tc">{{item.caution_money}}</span>
          <span class="flex1 tc">{{item.target_profit_price}}</span>
          <span class="flex1 tc">{{item.stop_loss_price}}</span>
          <span class="flex2 tc">{{item.time}}</span>
          <span class="flex1 tc">{{item.trade_fee}}</span>
          <span class="flex1 tc">{{item.overnight_money}}</span>
          <span class="flex1 tc" :class="item.profits < 0 ? 'red' : 'green'">{{item.profits}}</span>
          <div class="flex2 tr blue">
            <span class="pointer" @click="lossSet(item)">{{$t('lever.setys')}}</span>
            <span class="pointer ml25" @click="leverClose(item.id)">{{$t('lever.pingcang')}}</span>
          </div>
        </li>
        <li v-if="historyData.currentPositionTotal>historyData.currentPositionPer" class="w100 tc pages mb40 mt20">
          <el-pagination
            :total="historyData.currentPositionTotal"
            layout="prev, pager, next"
            @current-change="handleCurrentChange"
          ></el-pagination>
        </li>
        <li v-if="historyData.currentPositionTotal ==0">
          <div class="tc ptb40">
            <img src="../../assets/images/nodata.png" alt class="wt70" />
            <p>{{$t('lever.noData')}}</p>
          </div>
        </li>
      </ul>
      <!-- 全部持仓 -->
      <ul v-else>
        <li class="flex alcenter ptb10">
          <span class="flex2">{{$t('lever.types')}}</span>
          <span class="flex1 tc">{{types==0?$t('lever.delegate_price'):$t('lever.price_cang')}}</span>
          <!-- <span class="flex1 tc">{{$t('lever.price_cur')}}</span> -->
          <span class="flex1 tc">{{$t('lever.price_zhiying')}}</span>
          <span class="flex1 tc">{{$t('lever.price_lose')}}</span>
          <span class="flex2 tc">{{$t('lever.time_start')}}</span>
          <span class="flex1 tc">{{$t('lever.fee')}}</span>
          <span class="flex1 tc">{{$t('lever.geye_fee')}}</span>
          <span class="flex1 tc">{{$t('lever.num_zhehe')}}</span>
          <span class="flex1 tc">{{$t('lever.money')}}</span>
          <span class="flex1 tc">{{$t('lever.can_money')}}</span>
          <span class="flex1 tc">{{$t('lever.loss')}}</span>
          <span class="flex1 tr" v-if="types == 0 || types == 1">{{$t('lever.operate')}}</span>
        </li>
        <li class="flex alcenter ptb10" v-for="(item,index) in historyData.orderList" :key="index">
          <p class="flex2">
            <span :class="item.type==1?'green':'red'">{{item.type_name}}</span>
            {{item.symbol}} X{{item.share}}{{$t('lever.hand')}}
          </p>
          <span class="flex1 tc">{{types == 0 ? item.origin_price : item.price}}</span>
          <!-- <span class="flex1 tc">{{item.update_price}}</span> -->
          <span class="flex1 tc">{{item.target_profit_price}}</span>
          <span class="flex1 tc">{{item.stop_loss_price}}</span>
          <span class="flex2 tc">{{item.time}}</span>
          <span class="flex1 tc">{{item.trade_fee}}</span>
          <span class="flex1 tc">{{item.overnight_money}}</span>
          <span class="flex1 tc">{{item.number}}</span>
          <span class="flex1 tc">{{item.origin_caution_money}}</span>
          <span class="flex1 tc">{{item.caution_money}}</span>
          <span class="flex1 tc" :class="item.profits < 0 ? 'red' : 'green'">{{item.profits}}</span>
          <span
            class="flex1 tr blue pointer"
            v-if="types == 0 || types == 1"
            @click="leverClose(item.id)"
          >{{types == 0 ? $t('lever.chedan'):$t('lever.pingcang')}}</span>
        </li>
        <!-- 分页 -->
        <li v-if="historyData.orderListTotal>historyData.orderListPer" class="w100 tc pages mb40 mt20">
          <el-pagination
            :total="historyData.orderListTotal"
            layout="prev, pager, next"
            @current-change="handleCurrentChange"
          ></el-pagination>
        </li>
        <li v-if="historyData.orderListTotal ==0">
          <div class="tc ptb40">
            <img src="../../assets/images/nodata.png" alt class="wt70" />
            <p>{{$t('lever.noData')}}</p>
          </div>
        </li>
      </ul>
    </div>
    <!-- 止盈止损弹窗 -->
    <el-dialog :title="$t('lever.setys')" :visible.sync="lossShow" width="40%" :before-close="handleClose">
      <div class="mb15">
        <div class="flex alcenter">
          <span>{{$t('lever.price_zhiying')}}</span>
          <div class="ml20">
            <el-input-number
              v-model="profitPrice"
              @change="profitChange"
              :min="0"
              @input.native="profitChange"
            ></el-input-number>
          </div>
        </div>
        <div class="mt10">{{$t('lever.profit')}}：{{expectedProfit || '0.00' |filterDecimals}}</div>
        <div class="flex alcenter mt10">
          <span>{{$t('lever.price_lose')}}</span>
          <div class="ml20">
            <el-input-number
              v-model="lossPrice"
              @change="lossChange"
              @input.native="lossChange"
              :min="0"
            ></el-input-number>
          </div>
        </div>
        <div class="mt10">{{$t('lever.lose')}}：{{expectedLoss || '0.00' |filterDecimals}}</div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button class="radius4" @click="lossShow=false">{{$t('lever.cannel')}}</el-button>
        <el-button type="primary" @click="setLoss" class="radius4">{{$t('lever.e_confrim2')}}</el-button>
      </span>
    </el-dialog>

    <!-- 一键平仓弹窗 -->
    <el-dialog :title="$t('lever.confrim_ping')" :visible.sync="allCloseShow" width="40%" :before-close="handleClose">
      <div class="mb15 flex">
        <div
          class="blue bdblue ptb5 plr15 radius4 pointer"
          :class="closeType==0?'white bgblue':''"
          @click="closeType=0"
        >{{$t('lever.allClose')}}</div>
        <div
          class="green bdgreen ptb5 plr15 radius4 ml25 pointer"
          :class="closeType==1?'white bggreen':''"
          @click="closeType=1"
        >{{$t('lever.duo_ping')}}</div>
        <div
          class="red bdred ptb5 plr15 radius4 ml25 pointer"
          :class="closeType==2?'white bgred':''"
          @click="closeType=2"
        >{{$t('lever.kong_ping')}}</div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button class="radius4" @click="allCloseShow=false">{{$t('lever.cannel')}}</el-button>
        <el-button type="primary" @click="allClose" class="radius4">{{$t('lever.e_confrim2')}}</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
export default {
  props: ["currentDelegation", "currency_matchInfo", "historyData"],
  data() {
    return {
      list: [],
      types: 5,
      page: 1,
      lossShow: false,
      profitPrice: "",
      lossPrice: "",
      expectedProfit: "0.00",
      expectedLoss: "0.00",
      lossData: {},
      price1: "",
      price2: "",
      allCloseShow: false,
      closeType: 0
    };
  },
  created() {
    // this.orderSocket();
  },
  methods: {
    // 选择列表类型
    changeType(options) {
      var that = this;
      that.types = options;
      if (options == 6) {
        this.$emit("history", {
          pages: 1,
          types: options
        });
        this.$parent.getDefault();
      } else if (options == 5) {
        this.$emit("history", {
          pages: 1,
          types: options
        });
        this.$parent.getCurrent();
      } else {
        console.log(options);
        this.$emit("history", {
          pages: 1,
          types: options
        });
        that.$parent.getOrder();
      }
    },
    // 关闭弹窗
    handleClose() {
      var that = this;
      that.lossShow = false;
      that.allCloseShow = false;
    },
    // 平仓
    leverClose(ids) {
      var that = this;
      var urls = "";
      if (that.types == 0) {
        urls = "lever/cancel";
      } else {
        urls = "lever/close";
      }
      that.$http
        .initDataToken({ url: urls, data: { id: ids }, type: "POST" })
        .then(res => {
          console.log(res);
          this.$emit("closeLever", ids);
        });
    },
    // 设置止盈止损
    setLoss() {
      var that = this;
      that.$http
        .initDataToken({
          url: "lever/setstop",
          data: {
            id: that.lossData.id,
            target_profit_price: that.price1,
            stop_loss_price: that.price2
          },
          type: "POST"
        })
        .then(res => {
          that.lossShow = false;
          var data2 = that.historyData.currentPosition;
          var data3 = that.historyData.orderList;
          data2.forEach((item, index) => {
            if (item.id == that.lossData.id) {
              that.historyData.currentPosition[index].target_profit_price =
                that.price1;
              that.historyData.currentPosition[index].stop_loss_price =
                that.price2;
            }
          });
          data3.forEach((item, index) => {
            if (item.id == that.lossData.id) {
              that.historyData.orderList[index].target_profit_price =
                that.price1;
              that.historyData.orderList[index].stop_loss_price = that.price2;
            }
          });
        });
    },
    // 止盈止损弹窗出现
    lossSet(options) {
      var that = this;
      that.lossShow = true;
      that.lossData = options;
      // 初始赋值止盈价
      if (options.target_profit_price > 0) {
        // 设置过止盈价
        that.profitPrice = Number(options.target_profit_price);
        if (options.type == 1) {
          if (that.profitPrice > options.price) {
            that.expectedProfit =
              (that.profitPrice - options.price) * options.share;
          } else {
            that.expectedProfit = "0.00";
          }
        } else {
          if (options.price > that.profitPrice) {
            that.expectedProfit =
              (options.price - that.profitPrice) * options.share;
          } else {
            that.expectedProfit = "0.00";
          }
        }
      } else {
        that.profitPrice = Number(options.update_price);
      }
      that.price1 = that.profitPrice;

      // 初始赋值止损价
      if (options.stop_loss_price > 0) {
        that.lossPrice = Number(options.stop_loss_price);
        if (options.type == 1) {
          if (options.price > that.lossPrice) {
            that.expectedLoss =
              (options.price - that.lossPrice) * options.share;
          } else {
            that.expectedLoss = "0.00";
          }
        } else {
          if (that.lossPrice > options.price) {
            that.expectedLoss =
              (that.lossPrice - options.price) * options.share;
          }
        }
      } else {
        that.lossPrice = Number(options.update_price);
      }
      that.price2 = that.lossPrice;
    },

    // 止盈价改变
    profitChange(e) {
      var that = this;
      var values = 0;
      if (e.target && e.target.value) {
        values = e.target.value;
      } else {
        values = e;
      }
      console.log(values);
      if (that.lossData.type == 1) {
        if (values > that.lossData.price) {
          that.expectedProfit =
            (values - that.lossData.price) * that.lossData.share;
        } else {
          that.expectedProfit = "0.00";
        }
      } else {
        if (that.lossData.price > values) {
          that.expectedProfit =
            (that.lossData.price - values) * that.lossData.share;
        } else {
          that.expectedProfit = "0.00";
        }
      }
      that.price1 = values;
    },

    // 止损价变化
    lossChange(e) {
      var that = this;
      var values = "";
      if (e.target && e.target.value) {
        values = e.target.value;
      } else {
        values = e;
      }
      if (that.lossData.type == 1) {
        if (that.lossData.price > values) {
          that.expectedLoss =
            (that.lossData.price - values) * that.lossData.share;
        } else {
          that.expectedLoss = "0.00";
        }
      } else {
        if (values > that.lossData.price) {
          that.expectedLoss =
            (values - that.lossData.price) * that.lossData.share;
        } else {
          that.expectedLoss = "0.00";
        }
      }
      that.price2 = values;
    },

    // 一键平仓
    allClose() {
      var that = this;
      that.allCloseShow = false;
      that.$http
        .initDataToken({
          url: "lever/batch_close",
          data: {
            type: that.closeType,
            currency_match_id:
              that.currency_matchInfo.currency_quotation.currency_match_id
          },
          type: "POST"
        })
        .then(res => {
          if (that.types == 6) {
            this.$emit("history", {
              pages: 1,
              types: that.types
            });
            this.$parent.getDefault();
          } else if (that.types == 5) {
            this.$emit("history", {
              pages: 1,
              types: that.types
            });
            this.$parent.getCurrent();
          } else {
            console.log(that.types);
            this.$emit("history", {
              pages: 1,
              types: that.types
            });
            that.$parent.getOrder();
          }
        });
    },

    // 分页
    handleCurrentChange(val) {
      var that = this;
      if (that.types == 6) {
      } else if (that.types == 5) {
        that.$emit("history", {
          pages: val,
          types: that.types
        });
        that.$parent.getCurrent();
      } else {
        console.log(that.types);
        that.$emit("history", {
          pages: val,
          types: that.types
        });
        that.$parent.getOrder();
      }
    }
  }
};
</script>
<style lang="scss">
.pages .el-dialog,
.el-pager li {
  background: transparent;
}

.pages .el-pagination button {
  background: transparent !important;
  color: #b0b8db !important;
}

.pages .el-pagination button:disabled {
  color: #5a5a5a !important;
}

.pages .el-pagination {
  color: #b0b8db;
}
</style>
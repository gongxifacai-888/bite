<template>
  <div>
    <div class="flex bgheader ht40 lh40 baselight2 tc ft16">
      <div class="plr20 pointer" :class="status==1?'bdbblue blue':''" @click="changeType(1)">{{$t('lever.dealing')}}</div>
      <div class="plr20 ml10 pointer" :class="status==3?'bdbblue blue':''" @click="changeType(3)">{{$t('lever.hasping')}}</div>
    </div>
    <div class="bgpart plr15 pb10 h180 overyscroll">
      <ul>
        <li class="flex alcenter ptb10">
          <span class="flex1">{{$t('market.pairs')}}</span>
          <span class="flex1 tc">{{$t('trade.num')}}</span>
          <span class="flex1 tc">{{$t('second.PurchasePrice')}}</span>
          <span class="flex1 tc" v-if="status == 1">{{$t('trade.price_cur')}}</span>
          <span class="flex1 tc" v-if="status == 1">{{$t('second.E_profitandloss')}}</span>
          <span class="flex1 tr" v-if="status == 1">{{$t('second.countDown')}}</span>
          <span class="flex1 tc" v-if="status == 3">{{$t('second.TransactionPrice')}}</span>
          <span class="flex1 tc" v-if="status == 3">{{$t('trade.fee')}}</span>
          <span class="flex1 tr" v-if="status == 3">{{$t('lever.loss')}}</span>
        </li>
      </ul>
      <ul>
        <li v-if="historyData.currentPosition.length > 0" v-for="(item,index) in historyData.currentPosition":key="index" class="flex between ptb5">
          <span class="flex1">{{item.symbol_name}} {{item.seconds}}s</span>
          <span class="flex1 tc">{{parseFloat(item.number) || '0'}} {{item.currency_name}}</span>
          <span class="flex1 tc">
            {{item.open_price || '0.00' | tofixedFour}}
            <img
              v-if="item.type == 1"
              width="10"
              src="../../../static/imgs/buy.png"
              alt
            >
          <img v-else width="10" src="../../../static/imgs/sell.png" alt>
          </span>
          <span v-if="status == 1&& item.status == 1" class="flex1 tc">{{currency_matchInfo.currency_quotation.close  || '0.00' | tofixedFour}}</span>
          <span v-if="status == 1&&item.status == 3" class="flex1 tc">{{item.end_price || '0.00' |tofixedFour}}</span>
          <span v-if="status == 3" class="flex1 tc">{{item.end_price || '0.00' |tofixedFour}}</span>
          <span
            class="flex1 redColor tc"
            v-if="item.status == 1&& status == 1 && item.type == 1 && (item.open_price -0) > (currency_matchInfo.currency_quotation.close  -0)"
          >-{{item.number || '0' | tofixedFour}}</span>
          <span
            class="flex1 greenColor tc"
            v-if="item.status == 1&& status == 1 && item.type == 1 && (item.open_price -0) <= (currency_matchInfo.currency_quotation.close  -0)"
          >{{(item.number * item.profit_ratio -0) || '0.00' | tofixedFour}}</span>
          <span
            class="flex1 greenColor tc"
            v-if="item.status == 1&& status == 1 && item.type == 2 && (item.open_price -0) >= (currency_matchInfo.currency_quotation.close  -0)"
          >{{(item.number * item.profit_ratio -0) || '0.00' | tofixedFour}}</span>
          <span
            class="flex1 redColor tc"
            v-if="item.status == 1&& status == 1 && item.type == 2 && (item.open_price -0) < (currency_matchInfo.currency_quotation.close  -0)"
          >-{{(item.number -0) || '0.00' | tofixedFour}}</span>
          <span class="flex1 tr" v-if="status == 1 &&item.status == 3" :class="item.fact_profits <0?'redColor':'greenColor'">{{item.fact_profits || '0' |toFixedNum}}</span>
          <span
            v-if="status == 1&&item.status == 1"
            class="times flex1 tr"
          >{{item.remain_milli_seconds}}s</span>
          <span
            v-if="status == 1&&item.status == 3"
            class="times flex1 tc"
          >0.0s</span>
          <span class="flex1 tc" v-if="status == 3">{{item.fee || '0.00' | tofixedFour}}</span>
          <span class="flex1 tr" v-if="status == 3" :class="item.fact_profits <0?'redColor':'greenColor'">{{item.fact_profits || '0' |tofixedFour}}</span>
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
            <p>{{$t('assets.noData')}}</p>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>
<script>
export default {
  props: ["currentDelegation", "currency_matchInfo", "historyData"],
  data() {
    return {
      status: 1,
      page: 1
    };
  },
  created() {
    // this.orderSocket();
  },
  filters: {
    toFixeds: function(val) {
      val = Number(val);
      return val.toFixed(2);
    },
    tofixedFour: function(val) {
      val = Number(val);
      return val.toFixed(4);
    },
    toFixedNum(val) {
      val = Number(val);
      return val.toFixed(0);
    }
  },
  mounted(){
    
  },
  methods: {
    // 选择列表类型
    changeType(options) {
      var that = this;
      that.status = options;
      this.$emit("history", {
        pages: 1,
        status: options
      });
      this.$parent.getCurrent();
    },
    // 关闭弹窗
    handleClose() {
      var that = this;
      that.lossShow = false;
      that.allCloseShow = false;
    },
    // 分页
    handleCurrentChange(val) {
      var that = this;
      that.$emit("history", {
        pages: val,
        status: that.status
      });
      that.$parent.getCurrent();
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
.h180{
  height: 180px;
}
</style>
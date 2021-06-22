<template>
  <div>
    <div class="between baselight2 ptb20 plr20 ft16 bgheader flex alcenter">
      <p>  <span class="blue">{{name}}{{$t('assets.account')}}</span> {{$t('assets.records')}}</p>
      <div>
        <el-select v-model="coinCode" :placeholder="$t('assets.selectCurrency')" class="flex1 bdinput" @change="changeCoin">
          <el-option :label="$t('assets.all')" :value="1"></el-option>
          <el-option
            v-for="item in coinlist"
            :key="item.currency_id"
            :label="item.currency_code"
            :value="item"
          ></el-option>
        </el-select>
      </div>
    </div>
    <div class="bgpart plr15">
      <!-- <div class="flex alcenter ptb10">
        <span class="flex1">币种</span>
        <span class="flex1 tc">数量</span>
        <span class="flex1 tc">记录</span>
        <span class="flex1 tr">时间</span>
      </div>-->
      <!-- 记录列表 -->
      <ul>
        <li class="flex alcenter ptb10">
          <span class="flex1">{{$t('assets.currency')}}</span>
          <span class="flex1 tc">{{$t('assets.numbers')}}</span>
          <span class="flex1 tc">{{$t('assets.record')}}</span>
          <span class="flex1 tr">{{$t('assets.times')}}</span>
        </li>
        <li class="flex alcenter ptb10" v-for="item in logList" :key="item.id">
          <span class="flex1">{{item.currency.code}}</span>
          <span class="flex1 tc">{{item.value}}</span>
          <p class="flex1 tc">
            {{item.memo}}
            <span v-if="item.extra_data&&item.extra_data.from">({{$t('assets.xiaji')}}{{item.extra_data.from}})</span>
          </p>
          <span class="flex1 tr">{{item.created_at}}</span>
        </li>
      </ul>
      <!-- 分页 -->
      <div v-if="total >15" class="w100 tc pages mb40 mt20">
        <el-pagination
          :total="total"
          layout="prev, pager, next"
          @current-change="handleCurrentChange"
        ></el-pagination>
      </div>
      <div>
        <div class="tc ptb40" v-if="total==0">
          <img src="../../assets/images/nodata.png" alt class="wt70" />
          <p>{{$t('assets.noData')}}</p>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      recordAccount: "",
      logUrl: "",
      page: 1,
      coinlist: [],
      coinCode: "",
      logList: [],
      total: 0,
      currency_id: "",
      name:''
    };
  },
  created() {
    var that = this;
    that.recordAccount = this.$route.query.types;
    if (that.recordAccount == "change_account") {
      that.logUrl = "account_log/change";
      that.name=this.$t('assets.trade');
    } else if (that.recordAccount == "legal_account") {
      that.logUrl = "account_log/legal";
      that.name=this.$t('assets.legalCurrency');
    } else if (that.recordAccount == "lever_account") {
      that.logUrl = "account_log/lever";
      that.name=this.$t('assets.lever');
    }else if (that.recordAccount == "micro_account") {
      that.logUrl = "account_log/micro";
      that.name=this.$t('assets.micro');
    }else if (that.recordAccount == "financial_account") {
      that.logUrl = "account_log/financial";
      that.name=this.$t('assets.financial');
    }
    that.init();
    that.getCoinList();
  },
  methods: {
    init() {
      var that = this;
      that.$http
        .initDataToken(
          {
            url: that.logUrl,
            data: {
              currency_id: that.currency_id,
              page: that.page
            }
          },
          false
        )
        .then(res => {
          console.log(res);
          that.total = res.total;
          that.logList = res.data;
        });
    },
    handleCurrentChange(val) {
      var that = this;
      that.logList = [];
      that.page = val;
      that.init();
    },
    getCoinList() {
      var that = this;
      that.$http.initDataToken({ url: "account/list" }, false).then(res => {
        res.forEach(item => {
          if (item.account_code == that.recordAccount) {
            that.coinlist = item.accounts;
            that.changeData = item;
          }
        });
      });
    },
    changeCoin(e) {
      var that = this;
      if (e == 1) {
        that.currency_id = "";
        that.coinCode = this.$t('assets.all');
      } else {
        that.currency_id = e.currency_id;
        that.coinCode = e.currency_code;
      }
      that.page = 1;
      that.total = 0;
      that.logList = [];
      that.init();
    }
  }
};
</script>
<style>
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
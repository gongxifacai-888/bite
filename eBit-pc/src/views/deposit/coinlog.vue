<template>
  <div class="mt20">
    <div class="between baselight2 ptb20 plr20 ft16 bgheader flex alcenter max1200 mt20">
      <p class="ft14">{{$t('cun.log')}}</p>
    </div>
    <div class="bgpart max1200 mt20" style="min-height:500px">
      <!-- 记录列表 -->
      <ul>
        <li class="ptb15 bdb_1e plr15 bdbheader" v-for="(item,index) in logList" :key="item.id">
          <div class="flex between alcenter">
            <div class="flex flex flexend">
              <span v-if="item.storage_currency">{{item.storage_currency.name}}</span>
              <span class="gray9 pl10">{{item.created_at.substring(10,16)}} {{item.created_at.substring(5,7)}}/{{item.created_at.substring(8,11)}}</span>
            </div>
            <div :class="['gray',{'red':item.status==1}]">
              {{item.status==1?$t('cun.ing'):$t('cun.loss')}}
            </div>
          </div>
          <div class="mt20 flex">
            <div class="flex1">
              <span class="gray9">{{$t('cun.num')}}</span>
              <div class="mt5" v-if="item.storage_currency">{{item.storage_currency.limit_number}}</div>
            </div>
            <div class="flex1 tc">
              <span class="gray9">{{$t('cun.leiji')}}</span>
              <div class="mt5">{{item.pile_income}}</div>
            </div>
            <div class="flex1 tr">
              <span class="gray9">{{$t('cun.lastday')}}({{$t('cun.day')}})</span>
              <div class="mt5">{{item.surplus_days}}</div>
            </div>
          </div>
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
      page: 1,
      total:0,
      logList:[]
    };
  },
  created() {
    var that = this;
    that.init();
  },
  methods: {
    init() {
      var that = this;
      that.$http
        .initDataToken({url: 'storage_currency/history',
            data: {
              type:that.type,
              page: that.page
            }
          },false)
      .then(res => {
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
    changeType(e) {
      var that = this;
      that.page = 1;
      that.total = 0;
      that.logList = [];
      that.init();
    }
  }
};
</script>
<style lang='scss'>
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
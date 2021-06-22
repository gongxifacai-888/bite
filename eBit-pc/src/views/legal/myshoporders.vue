<template>
  <div class="radius2 shop">
    <!-- 头部商家信息部分 -->
    <div class="bgheader ptb20 plr20">
      <div class="flex between">
        <div class="flex ">
          <div class="flex alcenter ft14 mr30">
            <span
              class="wt50 ht50 radius50p white tc flex alcenter jscenter bgblue"
            >{{personinfo.name.substr(0,1)}}</span>
            <span class="pl5 baselight2">{{personinfo.name}}</span>
          </div>
          <div class="flex1 tc">
            <p>{{$t('store.regtime')}}</p>
            <p class="mt5 baselight2">{{personinfo.created_at}}</p>
          </div>
        </div>
        <div class="flex flex1 jsend">

          <div
            class="wt60 tc white bgblue radius2 ht30 lh30 pointer"
            @click="dialogVisiblefabu=true"
          >{{$t('store.fabu')}}</div>
        </div>
      </div>
    </div>
    <!-- 出售求购订单部分 -->
    <div class="bgheader plr20 radius2">
      <div class="ft16 ptb20 flex alcenter between">
        <div class="flex baselight2">
          <span
            class="bold mr20 pointer"
            @click="changeType('BUY')"
            :class="{'green':selectType=='BUY'}"
          >{{$t('store.mybuy')}}</span>
          <span class="ht20 bdrfopt25"></span>
          <span
            class="bold ml20 pointer"
            @click="changeType('SELL')"
            :class="{'red':selectType=='SELL'}"
          >{{$t('store.mysell')}}</span>
        </div>
      </div>
      <div class="flex alcenter ft16 bold baselight2">
        <div
          class="mr20 ptb10 pointer bd2px"
          v-for="(item,i) in statusList"
          :key="i"
          :class="[i==coinIndex?(selectType=='BUY'?'bdbgreen':'bdbred'):'',i==coinIndex?(selectType=='BUY'?'green':'red'):'']"
          @click="changeStatus(i,item.status)"
        >{{item.name}}</div>
      </div>
    </div>
    <div class="plr20 mt20 baselight2">
      <div class="flex alcenter ptb10" v-if="orderlist.length>0">
        <span class="w10">{{$t('store.currency')}}</span>
        <span class="flex1">{{$t('legal.num')}}</span>
        <span class="flex1">{{$t('legal.limit')}}</span>
        <span class="flex1">{{$t('legal.price')}}</span>
        <span class="flex1">{{$t('legal.payWay')}}</span>
        <span class="flex15 tr">{{$t('legal.operate')}}</span>
      </div>
      <div class="flex alcenter ptb10 bdb30" v-for="(item,i) in orderlist" :key="i">
        <div class="w10 flex alcenter">
          <span class="pl5">{{item.currency_name}}</span>
        </div>
        <div class="flex1">
          <span>{{item.total_qty}}</span>
          <span class="pl5">{{item.currency_name}}</span>
        </div>
        <div class="flex1">
          {{(item.min_number * item.price) | filterDecimals}}-{{(item.max_number * item.price) | filterDecimals}}
          <span class="pl5">{{item.area_currency}}</span>
        </div>
        <div class="flex1 green">
          {{item.price}}
          <span class="pl5">{{item.area_currency}}</span>
        </div>
        <div class="flex1 flex alcenter">
          <p class="mr5 ptb2" v-for="(itm,inx) in item.payways" :key="inx">
          <img :src="API + itm.logo" width="20" alt="">
            <!-- <span
              :class="[{'blue':itm.code=='alipay','green':itm.code=='wxpay','yellow':itm.code=='bank'}]"
            >{{itm.name}}</span> -->
          </p>
        </div>
        <div class="flex15 white flex alcenter jsend">
          <div
            class="mr10 pointer bgline plr10 radius2 ptb5"
            v-if="item.status==1"
            @click="isStart(i,item.id,0)"
          >{{$t('store.suspend')}}</div>
          <div
            class="mr10 pointer bgline plr10 radius2 ptb5"
            v-if="item.status==0"
            @click="isStart(i,item.id,1)"
          >{{$t('store.open')}}</div>
          <div
            class="mr10 pointer bgline plr10 radius2 ptb5"
            v-if="item.status!=3"
            @click="withdraw(i,item.id)"
          >{{$t('store.back')}}</div>
          <div class="pointer bgblue plr10 radius2 ptb5" @click="goOrder(item.id)">{{$t('store.lookorder')}}</div>
        </div>
      </div>
    </div>
    <div class="tc ptb40" v-if="orderlist.length==0">
      <img src="../../assets/images/nodata.png" alt class="wt70" />
      <p>{{$t('legal.norecord')}}</p>
    </div>

    <!-- 交易密码 -->
    <el-dialog
      :title="$t('legal.payPassword')"
      :visible.sync="dialogVisiblepwd"
      width="30%"
      :before-close="handleClose"
    >
      <div class="ht50 lh50 flex alcenter mb15">
        <input type="text" class="flex1 h100 bde7 pl20 radius2" :placeholder="$t('legal.enterPassword')" />
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button class="radius4" @click="dialogVisiblepwd=false">{{$t('legal.cannel')}}</el-button>
        <el-button type="primary" @click="confirmpwd" class="radius4">{{$t('legal.e_confrim2')}}</el-button>
      </span>
    </el-dialog>
    <!-- 发布 -->
    <el-dialog :title="$t('store.fabu')" :visible.sync="dialogVisiblefabu" width="30%" :before-close="handleClose">
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('store.currency')}}：</span>
        <el-select class="flex1 h100 bde7" v-model="currency_id" :placeholder="$t('store.selectCurrency')">
          <el-option
            v-for="item in currencyList"
            :key="item.currency_id"
            :label="item.currency_code"
            :value="item.currency_id"
          ></el-option>
        </el-select>
      </div>
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('store.unit')}}：</span>
        <el-select class="flex1 h100 bde7" v-model="area_id" :placeholder="$t('store.p_coin')">
          <el-option
            v-for="item in areaList"
            :key="item.id"
            :label="item.currency"
            :value="item.id"
          ></el-option>
        </el-select>
      </div>
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('legal.payWay')}}：</span>
        <el-select
          class="flex1 h100 bde7"
          v-model="payways"
          multiple
          filterable
          allow-create
          default-first-option
          :placeholder="$t('store.p_payment')"
        >
          <el-option
            v-for="item in payList"
            :key="item.payway_id"
            :label="item.payway.name"
            :value="item.payway_id"
          ></el-option>
        </el-select>
      </div>
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('legal.auto')}}：</span>
        <div>
          <el-radio v-model="auto_start" label="1">{{$t('legal.yes1')}}</el-radio>
          <el-radio v-model="auto_start" label="0">{{$t('legal.no1')}}</el-radio>
        </div>
      </div>
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('legal.price')}}：</span>
        <input
          type="number"
          class="flex1 h100 bde7 pl20 radius2"
          :placeholder="$t('store.p_price')"
          v-model="price"
        />
      </div>
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('legal.num')}}：</span>
        <input
          type="number"
          class="flex1 h100 bde7 pl20 radius2"
          :placeholder="$t('legal.buyNumber')"
          v-model="total_qty"
        />
      </div>
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('store.minnum')}}：</span>
        <input
          type="number"
          class="flex1 h100 bde7 pl20 radius2"
          :placeholder="$t('store.p_min')"
          v-model="min_number"
        />
      </div>
      <div class="ht40 lh40 flex alcenter mb15">
        <span class="w25">{{$t('store.maxnum')}}：</span>
        <input
          type="number"
          class="flex1 h100 bde7 pl20 radius2"
          :placeholder="$t('store.p_max')"
          v-model="max_number"
        />
      </div>
      <!-- <div class=" ht40 lh40 flex alcenter  mb15">
               <span class="w25">交易密码：</span> 
               <input type="text" class="flex1 h100 bde7 pl20 radius2" placeholder="请输入交易密码">
      </div>-->
      <span slot="footer" class="dialog-footer">
        <el-button class="radius4" @click="dialogVisiblefabu=false">{{$t('legal.cannel')}}</el-button>
        <el-button type="primary" @click="confirmfabu" class="radius4">{{$t('legal.e_confrim2')}}</el-button>
      </span>
    </el-dialog>
    <div class="abstort w100 tc lf0 btm20 pages mt40">
      <el-pagination
        background
        @current-change="handleCurrentChange"
        :current-page.sync="currentPage"
        :hide-on-single-page="last"
        :page-size="per_page"
        layout="prev, pager, next"
        :total="total"
      ></el-pagination>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      personinfo: { name: "" },
      statusList: [
        { name: this.$t('store.openIn'), status: 1 },
        { name: this.$t('store.suspendIn'), status: 0 },
        { name: this.$t('store.has_cancel'), status: 4 },
        { name: this.$t('store.OffShelf'), status: 2 },
        { name: this.$t('store.done'), status: 3 }
      ],
      selectType: "BUY", //购买
      coinIndex: 0,
      status: 1,
      currentPage: 1,
      per_page: 10,
      total: 0,
      last: false,
      orderlist: [],
      dialogVisiblepwd: false,
      dialogVisiblefabu: false,
      payList: [], //支付方式
      currencyList: [], //币种
      areaList: [], //价格单位,
      currency_id: "",
      area_id: "",
      payways: [],
      price: "",
      total_qty: "",
      min_number: "",
      max_number: "",
      auto_start: '1'
    };
  },
  created() {
    this.getlist();
    this.getPay();
    this.getCurrency();
    this.getCountry();
    this.init();
  },
  methods: {
    init() {
      var that = this;
      this.$http.initDataToken({ url: "user/info" }, false).then(res => {
        this.personinfo = res.seller;
      });
    },
    getPay() {
      //获取支付方式
      let that = this;
      this.$http
        .initDataToken({ url: "pay_method/methods" }, false)
        .then(res => {
          this.payList = res;
        });
    },
    getCurrency() {
      //获取可发布币种
      this.$http
        .initDataToken({ url: "otc/seller_currencies" }, false)
        .then(res => {
          this.currencyList = res;
          this.currency_id = res[0].currency_id;
        });
    },
    getCountry() {
      //获取价格单位
      this.$http
        .initDataToken({ url: "default/area_list" }, false)
        .then(res => {
          this.areaList = res;
          this.area_id = res[0].id;
        });
    },
    getlist() {
      //获取历史发布列表
      this.$http
        .initDataToken(
          {
            url: "otc/seller_masters",
            data: {
              page: this.currentPage,
              way: this.selectType,
              status: this.status
            }
          },
          false
        )
        .then(res => {
          this.total = res.total;
          this.per_page = res.per_page;
          this.last = res.last_page == 1 ? true : false;
          this.orderlist = res.data;
        });
    },
    handleCurrentChange(val) {
      //点击页码
      this.getlist();
    },
    withdraw(index, id) {
      //撤回
      this.$http
        .initDataToken(
          { url: "otc/cancel_master", data: { master_id: id }, type: "post" },
          true,
          true
        )
        .then(res => {
          this.orderlist.splice(index, 1);
        });
    },
    isStart(index, id, type) {
      //开启暂停
      let that = this;
      let url = type == 0 ? "otc/pause_master" : "otc/start_master";
      this.$http
        .initDataToken(
          { url: url, data: { master_id: id }, type: "post" },
          true,
          true
        )
        .then(res => {
          this.orderlist.splice(index, 1);
        });
    },
    goOrder(id) {
      //查看订单
      this.$router.push({
        path: "shoporders",
        query: { id: id }
      });
    },
    // 买入卖出类型
    changeType(e) {
      this.selectType = e;
      this.currentPage = 1;
      this.getlist();
    },
    // 切换币种
    changeStatus(i, status) {
      this.coinIndex = i;
      this.status = status;
      this.currentPage = 1;
      this.getlist();
    },
    // 关闭密码框
    handleClose(done) {
      this.dialogVisiblefabu = false;
    },

    // 确认密码
    confirmpwd() {},
    // 确认发布
    confirmfabu() {
      if (this.payways.length == 0) {
       this.$message(this.$t('store.p_payment'));
       return false;
      }
      if (Number(this.price) <= 0) {
        this.$message(this.$t('store.p_price'));
        return false;
      }
      if (Number(this.total_qty) <= 0) {
        this.$message(this.$t('legal.buyNumber'));
        return false;
      }
      if (Number(this.min_number) <= 0) {
         this.$message(this.$t('store.p_min'));
         return false;
      }
      if (Number(this.max_number) <= 0) {
         this.$message(this.$t('store.p_max'));
         return false;
      }
      if (
        (Number(this.max_number) || Number(this.min_number)) >
        Number(this.total_qty)
      ) {
         this.$message(this.$t('store.insufficient'));
         return false;
      }
      let data = {
        way: this.selectType,
        currency_id: this.currency_id,
        area_id: this.area_id,
        payways: this.payways,
        price: this.price,
        total_qty: this.total_qty,
        min_number: this.min_number,
        max_number: this.max_number,
        auto_start: this.auto_start
      };
      this.$http
        .initDataToken(
          { url: "otc/post_master", data: data, type: "post" },
          true,
          true
        )
        .then(res => {
          this.dialogVisiblefabu = false;
          this.price = "";
          this.total_qty = "";
          this.min_number = "";
          this.max_number = "";
          this.payways = [];
          this.currentPage = 1;
          this.getlist();
        });
    }
  }
};
</script>
<style lang="">
/* .pages .el-dialog, .el-pager li{
        background: transparent;
    }
   .pages .el-pagination button{
        background: transparent !important;
        color: #b0b8db !important;
    }
   .pages .el-pagination button:disabled{
        color: #5a5a5a !important;
    }
   .pages .el-pagination{
        color:#b0b8db;
    } */
.shop .el-input__inner {
  height: 40px;
  line-height: 40px;
  border: none;
}
.shop .el-select:hover .el-input__inner {
  border: none;
}
.shop .el-input__icon {
  line-height: 40px;
}
</style>
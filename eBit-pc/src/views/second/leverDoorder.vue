<template>
  <div class="">
    <div class="tc flex bgpart ptb20 ft14">
      <div class="flex1 hidden-xs">
        <div class="flex jscenter alcenter ft16">
          <img src="../../assets/images/money.png" alt>
          <p>{{$t('assets.microacc')}}</p>
        </div>
        <p class="pt15 ft22" v-if="wallet_list.length&&wallet_list[modelIndex]">{{wallet_list[modelIndex].micro_account.balance}} {{wallet_list[modelIndex].code}}</p>
        <!-- <ul>
          <li class="mt10 ft22" v-for="item in wallet_list"  :key="item.id" v-if="item.micro_account">{{item.micro_account.balance}} {{item.code}}</li>
        </ul> -->
      </div>
      <!-- <div class="flex1 bdle9">
        <div class="flex jscenter alcenter ft14">
          <img src="../../assets/images/money.png" alt>
          <p>受保资产</p>
        </div>
        <div>
          <p class="mt10 tc">100 TKB</p>
        </div>
      </div> -->
    </div>
    <div class="mt20">
      <div class="bgpart flex alcenter ft14 ptb10 plr20">
        <img src="../../assets/images/tick.png" alt="">
        <span class="pl10">{{$t('second.TransactionMode')}}</span>
      </div>
      <ul class="flex wraps times-list tc mt10">
        <li class="mb10 bde9 w30 ptb10 mr10 bgpart posRelt pointer gray9 ft14" v-for="(item,i) in wallet_list" :key="i" @click="changeModel(i)">
          <span>{{item.code}}</span>
          <img src="../../assets/images/pitch_on.png" alt="" v-if="modelIndex==i" class="abstort btm0 rt0">
        </li>
      </ul>
      <div class="bgpart flex alcenter ft14 ptb10 plr20">
        <img src="../../assets/images/tick.png" alt="">
        <span class="pl10">{{$t('second.OpenQuantity')}}</span>
      </div>
      <ul class="flex wraps times-list tc mt10" v-if="wallet_list.length&&wallet_list[modelIndex]&&wallet_list[modelIndex].micro_numbers.length">
        <li class="mb10 bde9 w30 ptb10 mr10 bgpart posRelt pointer gray9 ft14" v-for="(item,i) in wallet_list[modelIndex].micro_numbers" :key="i" @click="changeNumber(i)">
          <span>{{parseFloat(item.number)}}</span>
          <img src="../../assets/images/pitch_on.png" alt="" v-if="numberIndex==i" class="abstort btm0 rt0">
        </li>
      </ul>
      <div class="bgpart flex alcenter ft14 ptb10 plr20">
        <img src="../../assets/images/tick.png" alt="">
        <span class="pl10">{{$t('second.OpenTime')}}</span>
      </div>
      <ul class="flex wraps times-list tc mt10" v-if="second_List">
        <li class="mb10 bde9 w30 ptb10 mr10 bgpart posRelt pointer gray9 ft14" v-for="(item,i) in second_List" :key="i" @click="changeTimes(i)">
          <span>{{item.seconds}}</span>
          <img src="../../assets/images/pitch_on.png" alt="" v-if="timeIndex==i" class="abstort btm0 rt0">
        </li>
      </ul>
      <div class="bgpart ptb20 flex hidden-xs">
        <div class="flex flex1 alcenter jscenter">
          <img src="../../assets/images/miaoIcon.png" alt="">
          <span class="">{{$t('second.profitRate')}}</span>
        </div>
       <div class="flex1 alcenter flex  jscenter gold ft36">
          <span class="bold" v-if="second_List.length&&second_List[timeIndex].profit_ratio">{{parseFloat(Number(second_List[timeIndex].profit_ratio)*100)}}</span>%
          <span></span>
        </div>
      </div>

      <!-- <div class="mb10 posRelt mult">
        <p class="w15 gray9">价格</p>
        <input
          type="number"
          v-model="buyOrder.prices"
          placeholder="请输入价格"
          class="ht30 bdinput lh30 plr5 radius2 w100 mt10 nwhite"
        />
      </div> -->
      <div class="bdinput tc ht30 lh30 radius2 blue" v-if="!token">
        <router-link :to="{name:'login'}">{{$t('login.login')}}</router-link>
        <span class="baselight2">{{$t('lever.or')}}</span>
        <router-link :to="{name:'register'}">{{$t('login.register')}}</router-link>
      </div>
      <div class="mt20 nmb_mt20" v-else>
        <div class="bggreen tc ptb10 ft16 white radius40 flex alcenter jscenter shadow_green pointer mb_btn" @click="submits(1)" >
          <img src="../../assets/images/miaoIcon_18.png" alt="">
          <span class="white">{{$t('second.buyUp')}}</span> 
        </div>
        <div class="bgred tc ptb10 ft16 white radius40 mt10 flex alcenter jscenter shadow_red pointer mb_btn" @click="submits(2)" >
          <img src="../../assets/images/miaoIcon_21.png" alt="">
          <span class="white">{{$t('second.buyDown')}}</span> 
        </div>
      </div>
    </div>
    <!-- 提交弹窗 -->
    <el-dialog :title="submitType==1?$t('second.buyUp'):$t('second.buyDown')" :visible.sync="orderShow" :width="wscreenWithdata" :before-close="handleClose">
      <div class="mb15 flex">
        <div class="flex1 tc">
          <p>{{$t('legal.num')}}</p>
          <p class="mt10" v-if="wallet_list.length&&wallet_list[modelIndex]&&wallet_list[modelIndex].micro_numbers.length">{{parseFloat(wallet_list[modelIndex].micro_numbers[numberIndex].number)}}</p>
        </div>
        <div class="flex1 tc">
          <p>{{$t('store.times')}}</p>
          <p class="mt10" v-if="second_List[timeIndex]">{{second_List[timeIndex].seconds}}</p>
        </div>
        <div class="flex1 tc">
          <p>{{$t('second.profitRate')}}</p>
          <p class="mt10" v-if="second_List.length&&second_List[timeIndex].profit_ratio">{{parseFloat(Number(second_List[timeIndex].profit_ratio)*100)}}%</p>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button class="radius4" size="small" @click="orderShow=false">{{$t('trade.cancel')}}</el-button>
        <el-button type="primary" size="small" @click="orderSubmit" class="radius4">{{$t('lever.e_confrim2')}}</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
export default {
  props: ["currency_matchInfo","wallet_list","second_List"],
  data() {
    return {
      modelIndex:0,
      numberIndex:0,
      timeIndex:0,
      submitType: 1,
      orderShow: false,
      wscreenWithdata:'30%',
    };
  },
  created() {
    this.token = localStorage.getItem("token");
    // 获取当前币余额
    // this.initBalance(this.currencyId, 0);
  },
  mounted() {
    if(document.body.clientWidth<760){
        this.wscreenWithdata = '80%'
    }
  },
  methods: {
    // 点击提交按钮
    submits(options) {
      var that = this;
      that.submitType = options;
      that.orderShow = true;
    },
    // 关闭弹窗
    handleClose() {
      var that = this;
      that.orderShow = false;
    },
    // 确认下单
    orderSubmit() {
      var that = this;
      that.orderShow = false;
      that.$http
        .initDataToken({
          url: "microtrade/submit",
          type: "POST",
          data: {
            match_id: that.currency_matchInfo.currency_quotation.currency_match_id,
            currency_id:that.wallet_list[that.modelIndex].id,
            type: that.submitType,
            seconds:that.second_List[that.timeIndex].seconds,
            number:Number(that.wallet_list[that.modelIndex].micro_numbers[that.numberIndex].number),
          }
        })
        .then(res => {
          this.$emit('funct',1);
        });
    },
    //切换交易模式
    changeModel(i){
      this.modelIndex = i;
    },
    //选择数量
    changeNumber(i){
      this.numberIndex = i;
    },
    //选择倍数
    changeTimes(i){
      this.timeIndex = i;
    }
  }
};
</script>
<style lang="scss">
.mult {
  .el-input__inner {
    border: none !important;
    height: 30px;
    line-height: 30px;
  }
  .el-input__icon {
    line-height: 30px;
  }
}
.selects {
  background: #ffffff;
  border: 1px solid #e9e9e9;
}
.el-popper[x-placement^="bottom"] .popper__arrow::after {
  border-bottom-color: #ffffff;
}
.selects .el-popper .popper__arrow::after {
  border-bottom-color: #ffffff;
}
input[type="number"] {
  -moz-appearance: textfield;
}
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
@media (max-width: 768px) {  
  .nmb_mt20{
    .mb_btn{
      width:80%;
      margin:20px auto;
    }
  }
  .el-dialog__footer{
    text-align:center;
  }
}

</style>
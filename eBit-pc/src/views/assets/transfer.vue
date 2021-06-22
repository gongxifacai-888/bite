<template>
  <div class="baselight">
    <div class="flex between baselight2 ptb20 plr20 ft16 bgheader">
      <span>{{$t('assets.currencyTransfer')}}</span>
      <router-link :to="{name:'transferLog',query:{currency_id:currency_id,currency_code:curcoin}}" class="blue">{{$t('assets.transferRecord')}}</router-link>
    </div>
    <div class="plr20 pt20 pb50 cointransfer">
      <div class="flex alcenter">
        <span class="inblock wt80">{{$t('assets.transferCurrency')}}:</span>
        <el-select v-model="curcoin" class="flex1 bdinput" @change="changeCoin">
          <el-option
            v-for="item in coinlist"
            :key="item.currency_id"
            :label="item.currency_code"
            :value="item.currency_code"
          ></el-option>
        </el-select>
      </div>
      <div class="mt30">
        <div class="wt80">{{$t('assets.transferAccount')}}:</div>
        <div class="pl50 mt20 flex">
          <div class="pl10">
            <div class="flex alcenter">
              <div class="pr10">{{$t('assets.from')}}</div>
              <el-select v-model="fromtype" class="bdinput" @change="changetransfer1">
                <el-option
                  v-for="item in accoutlist"
                  :key="item.account_code"
                  :label="item.name"
                  :value="item.name"
                >
                  <!-- <span class="">{{ item }}</span> -->
                </el-option>
              </el-select>
            </div>
            <div class="mt20">{{$t('trade.balance')}}:{{balance||'0.00'}}</div>
          </div>
          <!-- <div class="tc plr20">
            <img src="../../assets/images/huazhuan.png" class="wt40 ht40 mtb20" alt />
          </div>-->
          <div class="pl10">
            <div class="flex alcenter">
              <div class="pr10">{{$t('assets.reach')}}</div>
              <el-select v-model="totype" class="bdinput" @change="changetransfer2">
                <el-option
                  v-for="item in accoutlist"
                  :key="item.account_code"
                  :label="item.name"
                  :value="item.name"
                >
                  <!-- <span class="">{{ item }}</span> -->
                </el-option>
              </el-select>
            </div>
            <!-- <div class="mt20">余额：12</div> -->
          </div>
        </div>
      </div>
      <div class="mt30 flex alcenter">
        <span class="inblock wt80">{{$t('assets.transfernum')}}:</span>
        <input type="number" class="bdline ht40 lh40 baselight2 radius2 plr20" v-model="number" />
      </div>
      <div class="flex mt60 pt40">
        <div class="wt80"></div>
        <div class="bgblue lh48 ht48 tc white ft16 radius4 wt217" @click="transferConfirm">{{$t('login.e_confrim2')}}</div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      accoutlist: [],
      coinlist: [],
      curindex: 0,
      curcoin: "",
      fromtype: "",
      totype: "",
      number: "",
      changeTransfer: 0,
      legalTransfer: 0,
      leverTransfer: 0,
      from: "",
      tos: "",
      currency_id: "",
      balance: "",
      transferData: []
    };
  },
  created() {
    var that = this;
    that.init();
  },
  methods: {
    init() {
      var that = this;
      that.$http.initDataToken({ url: "account/list" }, false).then(res => {
        that.transferData = res;

        res.forEach(item => {
          if (item.is_recharge == 1 && item.account_code == "change_account") {
            that.changeTransfer = 1;
            var datas = item.accounts;
            var arr = datas.filter(
              options =>
                options.currency.is_recharge_account &&
                options.currency.account_types.length > 1
            );
            that.coinlist = arr;
            that.accoutlist = arr[0].currency.account_types;
            that.fromtype = that.accoutlist[0].name;
            that.totype = that.accoutlist[1].name;
            that.currency_id = arr[0].currency_id;
            that.curcoin = arr[0].currency_code;
            that.from = that.accoutlist[0].account_code;
            that.tos = that.accoutlist[1].account_code;
            var data1 = that.transferData.filter(
              options => options.account_code == that.accoutlist[0].account_code
            );
            that.balance = data1[0].accounts.filter(
              options => options.currency_id == that.currency_id
            )[0].balance;
          } else if (
            item.is_recharge == 1 &&
            item.account_code == "legal_account"
          ) {
            that.legalTransfer = 1;
            var datas = item.accounts;
            var arr = datas.filter(
              options =>
                options.currency.is_recharge_account &&
                options.currency.account_types.length > 1
            );
            that.coinlist = arr;
            that.accoutlist = arr[0].currency.account_types;
            that.fromtype = that.accoutlist[0].name;
            that.totype = that.accoutlist[1].name;
            that.currency_id = arr[0].currency_id;
            that.curcoin = arr[0].currency_code;
            that.from = that.accoutlist[0].account_code;
            that.tos = that.accoutlist[1].account_code;
            var data1 = that.transferData.filter(
              options => options.account_code == that.accoutlist[0].account_code
            );
            that.balance = data1[0].accounts.filter(
              options => options.currency_id == that.currency_id
            )[0].balance;
          } else if (
            item.is_recharge == 1 &&
            item.account_code == "lever_account"
          ) {
            that.leverTransfer = 1;
            var datas = item.accounts;
            var arr = datas.filter(
              options =>
                options.currency.is_recharge_account &&
                options.currency.account_types.length > 1
            );
            that.coinlist = arr;
            that.accoutlist = arr[0].currency.account_types;
            that.fromtype = that.accoutlist[0].name;
            that.totype = that.accoutlist[1].name;
            that.currency_id = arr[0].currency_id;
            that.curcoin = arr[0].currency_code;
            that.from = that.accoutlist[0].account_code;
            that.tos = that.accoutlist[1].account_code;
            var data1 = that.transferData.filter(
              options => options.account_code == that.accoutlist[0].account_code
            );
            that.balance = data1[0].accounts.filter(
              options => options.currency_id == that.currency_id
            )[0].balance;
          }
        });
      });
    },
    // 切换左侧划转账户
    changetransfer1(e) {
      var that = this;
      var obj = that.accoutlist.filter(item => item.name == e)[0];
      that.fromtype = obj.name;
      that.from = obj.account_code;
      var data1 = that.transferData.filter(
        options => options.account_code == that.from
      );
      that.balance = data1[0].accounts.filter(
        options => options.currency_id == that.currency_id
      )[0].balance;
      if (that.changeTransfer == 1) {
        if (obj.account_code == "change_account") {
          that.totype = that.accoutlist.filter(
            options => options.account_code != "change_account"
          )[0].name;
          that.tos = that.accoutlist.filter(
            options => options.account_code != "change_account"
          )[0].account_code;
        } else {
          that.totype = that.accoutlist.filter(
            options => options.account_code == "change_account"
          )[0].name;
          that.tos = that.accoutlist.filter(
            options => options.account_code == "change_account"
          )[0].account_code;
        }
      } else if (that.legalTransfer) {
        if (obj.account_code == "legal_account") {
          that.totype = that.accoutlist.filter(
            options => options.account_code != "legal_account"
          )[0].name;
          that.tos = that.accoutlist.filter(
            options => options.account_code != "legal_account"
          )[0].account_code;
        } else {
          that.totype = that.accoutlist.filter(
            options => options.account_code == "legal_account"
          )[0].name;
          that.tos = that.accoutlist.filter(
            options => options.account_code == "legal_account"
          )[0].account_code;
        }
      } else if (that.leverTransfer == 1) {
        if (obj.account_code == "lever_account") {
          that.totype = that.accoutlist.filter(
            options => options.account_code != "lever_account"
          )[0].name;
          that.tos = that.accoutlist.filter(
            options => options.account_code != "lever_account"
          )[0].account_code;
        } else {
          that.totype = that.accoutlist.filter(
            options => options.account_code == "lever_account"
          )[0].name;
          that.tos = that.accoutlist.filter(
            options => options.account_code == "lever_account"
          )[0].account_code;
        }
      }
    },
    // 切换右侧划转账户
    changetransfer2(e) {
      var that = this;
      var obj = that.accoutlist.filter(item => item.name == e)[0];
      that.totype = obj.name;
      that.tos = obj.account_code;
      if (that.changeTransfer == 1) {
        if (obj.account_code == "change_account") {
          that.fromtype = that.accoutlist.filter(
            options => options.account_code != "change_account"
          )[0].name;
          that.from = that.accoutlist.filter(
            options => options.account_code != "change_account"
          )[0].account_code;
        } else {
          that.fromtype = that.accoutlist.filter(
            options => options.account_code == "change_account"
          )[0].name;
          that.from = that.accoutlist.filter(
            options => options.account_code == "change_account"
          )[0].account_code;
        }
        var data1 = that.transferData.filter(
          options => options.account_code == that.from
        );
        that.balance = data1[0].accounts.filter(
          options => options.currency_id == that.currency_id
        )[0].balance;
      } else if (that.legalTransfer == 1) {
        if (obj.account_code == "legal_account") {
          that.fromtype = that.accoutlist.filter(
            options => options.account_code != "legal_account"
          )[0].name;
          that.from = that.accoutlist.filter(
            options => options.account_code != "legal_account"
          )[0].account_code;
        } else {
          that.fromtype = that.accoutlist.filter(
            options => options.account_code == "legal_account"
          )[0].name;
          that.from = that.accoutlist.filter(
            options => options.account_code == "legal_account"
          )[0].account_code;
        }
        var data1 = that.transferData.filter(
          options => options.account_code == that.from
        );
        that.balance = data1[0].accounts.filter(
          options => options.currency_id == that.currency_id
        )[0].balance;
      } else if (that.leverTransfer == 1) {
        if (obj.account_code == "lever_account") {
          that.fromtype = that.accoutlist.filter(
            options => options.account_code != "lever_account"
          )[0].name;
          that.from = that.accoutlist.filter(
            options => options.account_code != "lever_account"
          )[0].account_code;
        } else {
          that.fromtype = that.accoutlist.filter(
            options => options.account_code == "lever_account"
          )[0].name;
          that.from = that.accoutlist.filter(
            options => options.account_code == "lever_account"
          )[0].account_code;
        }
        var data1 = that.transferData.filter(
          options => options.account_code == that.from
        );
        that.balance = data1[0].accounts.filter(
          options => options.currency_id == that.currency_id
        )[0].balance;
      }
    },
    // 切换币种
    changeCoin(e) {
      var that = this;
      var data1 = that.coinlist.filter(item => item.currency_code == e)[0];
      that.accoutlist = data1.currency.account_types;
      that.fromtype = that.accoutlist[0].name;
      that.totype = that.accoutlist[1].name;
      that.currency_id = data1.currency_id;
      that.curcoin = data1.currency_code;
      that.from = that.accoutlist[0].account_code;
      that.tos = that.accoutlist[1].account_code;
      var data1 = that.transferData.filter(
        options => options.account_code == that.accoutlist[0].account_code
      );
      that.balance = data1[0].accounts.filter(
        options => options.currency_id == that.currency_id
      )[0].balance;
    },
    // 确认划转
    transferConfirm() {
      var that = this;
      if (!that.number) {
        that.$utils.layerMsg(this.$t('assets.p_transfernum'));
        return false;
      }
      that.$http
        .initDataToken({
          url: "account/transfer",
          type: "POST",
          data: {
            currency_id: that.currency_id,
            balance: that.number,
            from: that.from,
            to: that.tos
          }
        })
        .then(res => {
          setTimeout(() => {
            that.init();
            that.number = "";
          }, 1000);
        });
    }
  }
};
</script>
<style lang="">
.cointransfer .el-input__inner {
  border-color: #252a44;
}
</style>
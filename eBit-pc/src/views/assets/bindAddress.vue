<template>
  <div class="mymention pb60">
    <div class="flex between baselight2 ptb20 plr20 ft16 bgheader">
      <span>{{$t('bind.bindAddr')}}</span>
    </div>
    <div class="mt20 plr30">
      <div class="flex alcenter mb20">
        <div class="flex alcenter flex1">
          <span class="wt100">{{$t('lever.currency')}}：</span>
          <el-select v-model="currencyCode" class="flex1 selectBor bdinput" @change="changeCurrency">
            <el-option v-for="item in coinList" :key="item.id" :label="item.code" :value="item.code"></el-option>
          </el-select>
        </div>
        <div class="flex alcenter flex1 ml30" v-if="currencyProtocols.length > 1">
          <span class="wt100">{{$t('assets.chainName')}}：</span>
          <el-select v-model="currencyProtocolsCode" class="flex1 selectBor" @change="changeProtocols">
            <el-option
              v-for="item in currencyProtocols"
              :key="item.id"
              :label="item.chain_protocol.code"
              :value="item.chain_protocol.code"
            ></el-option>
          </el-select>
        </div>
      </div>
      <div class="flex alcenter flex1 mb20">
        <span class="wt100">{{$t('bind.addr')}}:</span>
        <input type="text" v-model="address" class="ht40 lh40 plr20 bdinput flex1 baselight2" />
      </div>
      <div class="flex alcenter flex1 mb20">
        <span class="wt100">{{$t('assets.send')}}：</span>
        <div class="bdinput flex1 baselight2 flex alcenter between">
          <input type="text" v-model="code" class="ht40 lh40 plr20 baselight2" />
          <div class="w20 bdr tc blue pointer" @click="sendcode">{{codeText}}</div>
        </div>
      </div>
      <div class="mt30 radius2 tc ht48 lh48 white bgblue" @click="bindAddress">{{$t('bind.bind')}}</div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      currencyCode: "",
      coinList: [],
      index: 0,
      currencyProtocols: [],
      currencyProtocolsCode: "",
      userId: "",
      coinName: "",
      contractAddress: "",
      address: "",
      code: "",
      codeText: this.$t('addressList.send')
    };
  },
  created() {
    var that = this;
    that.userId = localStorage.getItem("uid");
    that.init();
  },
  methods: {
    init() {
      var that = this;
      that.$http
        .initDataToken(
          {
            url: "block_chain/currency_protocols"
          },
          false
        )
        .then(res => {
          if (res.length > 0) {
            that.coinList = res;
            that.currencyCode = res[0].code;
            if (res[0].currency_protocols.length > 0) {
              that.currencyProtocols = res[0].currency_protocols;
              that.currencyProtocolsCode =
                res[0].currency_protocols[0].chain_protocol.code;
              that.contractAddress = res[0].currency_protocols[0].token_address;
            }
            that.getAddress();
          }
        });
    },
    getAddress() {
      var that = this;
      that.$http
        .initDataNoapi(
          {
            url: "GetDrawAddress",
            data: {
              user_id: that.userId,
              coin_name: that.currencyProtocolsCode,
              contract_address: that.contractAddress
            }
          },
          false
        )
        .then(res => {
          if (res && res.address) {
            that.address = res.address;
          }
        });
    },
    // 切换币种
    changeCurrency(e) {
      var that = this;
      var obj = that.coinList.filter(item=>item.code==e)[0];
      that.address = "";
      that.code = "";
      that.currencyCode = e;
      if (obj.currency_protocols.length > 0) {
        that.currencyProtocols = obj.currency_protocols;
        that.currencyProtocolsCode =
          obj.currency_protocols[0].chain_protocol.code;
        that.contractAddress = obj.currency_protocols[0].token_address;
      }
      that.getAddress();
    },
    // 切换链类型
    changeProtocols(e) {
      var that = this;
      var obj = that.currencyProtocols.filter(item=>item.chain_protocol.code==e)[0];
      that.address = "";
      that.code = "";
      that.currencyProtocolsCode = e;
      that.contractAddress = obj.token_address;
      that.getAddress();
    },
    // 发送验证码
    sendcode(e) {
      var that = this;
      if (e.target.disabled) {
        return;
      } else {
        that.$http
          .initDataNoapi({
            url: "SendVerificationcode",
            data: {
              user_id: this.userId
            }
          })
          .then(res => {
            console.log(res)
            if (res!=undefined) {
              that.$utils.layerMsg(res, "success");
              var time = 59;
              var timer = null;
              timer = setInterval(function() {
                that.codeText = time + "s";
                e.target.disabled = true;
                if (time == 0) {
                  clearInterval(timer);
                  that.codeText = that.$t('addressList.send');
                  e.target.disabled = false;
                  return;
                }
                time--;
              }, 1000);
            }
          });
      }
    },

    // 绑定提币地址
    bindAddress() {
      var that = this;
      var obj = {
        user_id: that.userId,
        address: that.address,
        coin_name: that.currencyProtocolsCode,
        contract_address: that.contractAddress,
        verificationcode: that.code,
        t: Date.parse(new Date()) / 1000
      };
      var obj_str = JSON.stringify(obj);
      var sign = that.$md5(obj_str + "abcd4321");
      var datas = new FormData();
      datas.append("data", obj_str);
      datas.append("sign", sign);
      that.$http
        .initDataNoapi(
          {
            url: "BindDrawAddress",
            data: datas,
            type: "POST"
          },
          true,
          true,
          true
        )
        .then(res => {
          if (res) {
            that.$utils.layerMsg(res, "success");
          }
        });
    }
  }
};
</script>
<style scoped>
.mymention .el-input__inner {
  border-color: #383f66;
}
.selectBor >>> .el-input__inner{
  border: 1px solid #383f66;
}
</style>
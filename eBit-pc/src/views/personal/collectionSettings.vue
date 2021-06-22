<template>
  <div class="pb20">
    <div class="pt20 ml20 w60 flex between alcenter">
      <span>{{$t('collect.method')}}</span>
      <el-select v-model="payName" class="ht40 lh40 radius2 ml10 w80 nwhite bdinput" @change="changePay">
        <el-option v-for="item in typeList" :key="item.name" :label="item.name" :value="item">
          <span>{{ item.name }}</span>
        </el-option>
      </el-select>
    </div>
    <!-- 支付宝 -->
    <ul class="pt20 ml20 w60" v-if="payType=='alipay'">
      <li class="flex between alcenter">
        <span>{{$t('collect.name')}}：</span>
        <input
          type="text"
          v-model="realName"
          :placeholder="$t('collect.p_name')"
          class="ht40 bdinput lh40 plr10 radius2 ml10 w80 nwhite"
        />
      </li>

      <li class="flex between alcenter mt20">
        <span>{{$t('collect.alipay')}}：</span>
        <input
          type="text"
          v-model="alipyAccount"
          :placeholder="$t('collect.p_alipay')"
          class="ht40 bdinput lh40 plr10 radius2 ml10 w80 nwhite"
        />
      </li>

      <li class="flex between alcenter mt20">
        <span>{{$t('collect.collectionCode')}}：</span>
        <div class="w80 flex">
          <div class="uploads posRelt wt200 ht200 mt10">
            <img :src="alipyCode" class="wt200 ht200" alt />
            <input
              type="file"
              class="wt200 ht200 abstort lf0 btm0"
              accept="image/*"
              name="file"
              @change="upload(2)"
            />
          </div>
        </div>
      </li>
    </ul>
    <!-- 微信 -->
    <ul class="pt20 ml20 w60" v-else-if="payType=='wxpay'">
      <li class="flex between alcenter">
        <span>{{$t('collect.name')}}：</span>
        <input
          type="text"
          v-model="realName"
          :placeholder="$t('collect.p_name')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 nwhite"
        />
      </li>

      <li class="flex between alcenter mt20">
        <span>{{$t('collect.wechat')}}：</span>
        <input
          type="text"
          v-model="wechatAccount"
          :placeholder="$t('collect.p_wechat')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 nwhite"
        />
      </li>
      <li class="flex between alcenter mt20">
        <span>{{$t('collect.collectionCode')}}：</span>
        <div class="w80 flex">
          <div class="uploads posRelt wt200 ht200 mt10">
            <img :src="wechatCode" class="wt200 ht200" alt />
            <input
              type="file"
              class="wt200 ht200 abstort lf0 btm0"
              accept="image/*"
              name="file"
              @change="upload(1)"
            />
          </div>
        </div>
      </li>
    </ul>
    <!-- 银行卡 -->
    <ul class="pt20 ml20 w60" v-else-if="payType=='bank'">
      <li class="flex between alcenter">
        <span>{{$t('collect.name')}}：</span>
        <input
          type="text"
          v-model="realName"
          :placeholder="$t('collect.p_name')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 nwhite"
        />
      </li>
      <li class="flex between alcenter mt20">
        <span>{{$t('collect.nameofopeningbank')}}：</span>
        <input
          type="text"
          v-model="bankName"
          :placeholder="$t('collect.pleaseenterthenameoftheopeningbank')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 nwhite"
        />
      </li>
      <li class="flex between alcenter mt20">
        <span>{{$t('collect.nameofopeningbranch')}}：</span>
        <input
          type="text"
          v-model="bankAddress"
          :placeholder="$t('collect.pleaseenterthenameoftheopeningbranch')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 nwhite"
        />
      </li>
      <li class="flex between alcenter mt20">
        <span>{{$t('collect.no')}}：</span>
        <input
          type="text"
          v-model="bankAccount"
          :placeholder="$t('collect.p_no')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 nwhite"
        />
      </li>
    </ul>
    <div
      class="bgblue w50 ht40 flex alcenter jscenter mt40 white radius6 ml20 ft16 pointer"
      @click="payMethodSave"
    >{{$t('collect.submits')}}</div>
  </div>
</template>
<script>
import Axios from "axios";
export default {
  data() {
    return {
      realName: "",
      bankName: "",
      bankAccount: "",
      bankAddress: "",
      alipyAccount: "",
      wechatNick: "",
      wechatAccount: "",
      wechatCode: require("@/assets/images/addimg.png"),
      alipyCode: require("@/assets/images/addimg.png"),
      typeList: [],
      payId: "",
      payName: "",
      payType: "",
      alipyStatus: false,
      wechatStatus: false
    };
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      var that = this;
      this.$http.initDataToken({ url: "pay_method/types" }, false).then(res => {
        this.typeList = res;
        this.payId = res[0].id;
        this.payName = res[0].name;
        this.payType = res[0].code;
        this.getInfo();
      });
    },
    getInfo() {
      var that = this;
      that.$http
        .initDataToken(
          { url: "pay_method/get_method_by_type", data: { type: that.payId } },
          false
        )
        .then(res => {
          if(res!='null'&&res!=null){
            if (that.payType == "alipay") {
              that.realName = res.raw_data.real_name;
              that.alipyAccount = res.raw_data.account;
              that.alipyCode = res.raw_data.qrcode;
              if (res.raw_data.qrcode) {
                that.alipyStatus = true;
              }
            } else if (that.payType == "wxpay") {
              that.realName = res.raw_data.real_name;
              that.wechatAccount = res.raw_data.account;
              that.wechatCode = res.raw_data.qrcode;
              if (res.raw_data.qrcode) {
                that.wechatStatus = true;
              }
            } else {
              that.realName = res.raw_data.real_name;
              that.bankName = res.raw_data.bank_name;
              that.bankAccount = res.raw_data.card_no;
              that.bankAddress = res.raw_data.bank_address;
            }
          }
        });
    },
    changePay(e) {
      console.log(e);
      this.payId = e.id;
      this.payType = e.code;
      this.payName = e.name;
      this.getInfo();
    },
    //   上传图片
    upload(options) {
      var that = this;
      var datas = new FormData();
      datas.append("file", event.target.files[0]);
      that.$http
        .initDataToken(
          {
            url: "common/image_upload",
            data: datas,
            type: "POST"
          },
          false,
          true,
          true
        )
        .then(res => {
          if (options == 1) {
            that.wechatCode = res.url;
            that.wechatStatus = true;
          } else if (options == 2) {
            that.alipyCode = res.url;
            that.alipyStatus = true;
          }
        });
    },
    // 保存收款方式
    payMethodSave() {
      var that = this;
      console.log(that.alipyCode);
      var obj = {};
      var datas = "";
      if (that.payType == "alipay") {
        if (!that.realName) {
          return that.$utils.layerMsg(this.$t('collect.p_name'));
        }
        if (!that.alipyAccount) {
          return that.$utils.layerMsg(this.$t('collect.p_alipay'));
        }
        if (!that.alipyStatus) {
          return that.$utils.layerMsg(this.$t('collect.pleaseuploadthecollectioncode'));
        }
        obj.real_name = that.realName;
        obj.account = that.alipyAccount;
        obj.qrcode = that.alipyCode;
      } else if (that.payType == "wxpay") {
        if (!that.realName) {
          return that.$utils.layerMsg(this.$t('collect.p_name'));
        }
        if (!that.wechatAccount) {
          return that.$utils.layerMsg(this.$t('collect.p_wechat'));
        }
        if (!that.wechatStatus) {
          return that.$utils.layerMsg(this.$t('collect.pleaseuploadthecollectioncode'));
        }
        obj.real_name = that.realName;
        obj.account = that.wechatAccount;
        obj.qrcode = that.wechatCode;
      } else {
        if (!that.realName) {
          return that.$utils.layerMsg(this.$t('collect.p_name'));
        }
        if (!that.bankName) {
          return that.$utils.layerMsg(this.$t('collect.pleaseenterthenameoftheopeningbank'));
        }
        if (!that.bankAddress) {
          return that.$utils.layerMsg(this.$t('collect.pleaseenterthenameoftheopeningbranch'));
        }
        if (!that.bankAccount) {
          return that.$utils.layerMsg(this.$t('collect.pleaseenterthebankaccountnumber'));
        }
        obj.real_name = that.realName;
        obj.card_no = that.bankAccount;
        obj.bank_name = that.bankName;
        obj.bank_address = that.bankAddress;
      }
      datas = JSON.stringify(obj);
      
      that.$http
        .initDataToken(
          {
            url: "pay_method/save",
            data: {
              type: that.payId,
              raw_data: obj
            },
            type: "POST"
          },

        )
        .then(res => {});
    }
  }
};
</script>
<style lang="scss" scoped>
.uploads input {
  opacity: 0;
}
.el-input__inner {
  height: 30px;
  line-height: 30px;
}
</style>
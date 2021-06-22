<template>
  <div class="flex alcenter jscenter vh100">
    <div class="bgpart radius4 plr60 ptb40 mb50 w25 mwidth">
      <div class="tc ft20">{{$t('login.forget_pwd')}}</div>
      <div class="mt40">
        <div class="bdbea2 flex alcenter bold">
          <div
            class="pb10 mr20 pointer"
            :class="{'active2px':index==i}"
            v-for="(item,i) in infos"
            :key="i"
            @click="changeMethod(i)"
          >{{item}}</div>
        </div>
        <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
          <!-- <div class="w20 bdr tc">+86</div> -->
          <el-select v-model="country_code" class="w25" @change="changeCountry" v-show="index==0">
            <el-option
              v-for="item in countries"
              :key="item.name"
              :label="'+'+item.global_code"
              :value="item.id"
            >
              <span class="fl">{{ item.name }}</span>
              <span class="fr ft14">+{{ item.global_code }}</span>
            </el-option>
          </el-select>
          <input
            type="text"
            class="flex1 h100 pl20 nwhite"
            :class="index == 0 ?'bdle7':''"
            v-model="account"
            @change="changeAccount"
            :placeholder="index == 0 ? $t('login.p_mobile') : $t('login.p_email')"
          />
        </div>
        <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
          <input type="text" class="flex1 h100 bdre7 pl20 nwhite" v-model="sms_code" :placeholder="$t('login.p_vcode')" />
          <div class="w20 bdr tc blue pointer" @click="sendcode" v-show="!hassend">{{$t('login.r_send')}}</div>
          <div class="w20 bdr tc blue" v-show="hassend">{{second}}s</div>
        </div>
        <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
          <input
            type="password"
            autocomplete="new-password"
            class="flex1 h100 pl20 nwhite"
            v-model="password"
            :placeholder="$t('login.p_pwd')"
          />
        </div>
        <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
          <input
            type="password"
            autocomplete="new-password"
            class="flex1 h100 pl20 nwhite"
            v-model="re_password"
            :placeholder="$t('register.enterPassword2')"
          />
        </div>
                <div class="vcode mt20 bde7 radius2 ht50 lh50 flex alcenter">
                    <Vcode :show="isShow" @success="onSuccess" @close="onClose" />
                    <button @click="submit" class="vsbutton">点此开始验证</button>
                </div>

        <div class="mt40 bgblue white tc h48 lh48 radius2 pointer" @click="submsits">{{$t('login.e_confrim')}}</div>
      </div>
    </div>
  </div>
</template>
<script>
import Vcode from "../../../node_modules/vue-puzzle-vcode";
export default {
  data() {
    return {
      infos: [this.$t('login.f_mobile'), this.$t('login.f_email')],
      index: 0,
      account: "",
      password: "",
      re_password: "",
      countries: [],
      country_code: "",
      sms_code: "",
      hassend: false,
      second: 60,
      interid: "",
      countryCodeId: "",
      isShow: false,
      vscode:false,
    };
  },
    components: {
        Vcode,
    },
  created() {
    // 获取国家
    this.$http.initDataToken({ url: "default/area_list" },false).then(res => {
      this.countries = res;
      if (res.length > 0) {
        this.country_code = "+" + res[0].global_code;
        this.countryCodeId = res[0].id;
      }
    });
  },
  methods: {
        submit() {
        this.isShow = true;
        },

        onSuccess(msg) {
        this.isShow = false; // 通过验证后，需要手动关闭模态框
        this.vscode = true;
        },

        onClose() {
        this.isShow = false;
        },

    changeCountry(e) {
      console.log(e);
      this.countryCodeId = e;
    },
    changeMethod(e) {
      this.index = e;
      this.hassend = false;
      if (this.interid) {
        clearInterval(this.interid);
      }
    },
    // 发送验证码
    sendcode() {
      if (this.hassend) {
        return;
      }
      // 手机
      if (this.index == 0) {
        if (!this.$utils.checkMobile(this.account)) {
          this.$utils.layerMsg(this.$t('login.p_taccount'));
          return false;
        }
        this.$http
          .initDataToken({
            url: "notify/send_sms",
            type: "POST",
            data: {
              to: this.account,
              type: 2,
              area_id: this.countryCodeId
            }
          })
          .then(res => {
            this.hassend = true;
            this.interid = setInterval(() => {
              if (this.second >= 1) {
                this.second--;
              } else {
                this.hassend = false;
                clearInterval(this.interid);
              }
            }, 1000);
          });
      } else {
        // 邮箱验证码
        if (this.account.indexOf("@") == -1) {
          this.$utils.layerMsg(this.$t('login.p_temail'));
          return false;
        }
        this.$http
          .initDataToken({
            url: "notify/send_email",
            type: "POST",
            data: {
              to: this.account,
              type: 2,
              area_id: this.countryCodeId
            }
          })
          .then(res => {
            this.hassend = true;
            this.interid = setInterval(() => {
              if (this.second >= 1) {
                this.second--;
              } else {
                this.hassend = false;
                clearInterval(this.interid);
              }
            }, 1000);
          });
      }
      // 邮箱
    },
    // 提交
    submsits() {
      var types ="";
      if(this.index == 0){
         if (!this.$utils.checkMobile(this.account)) {
          this.$utils.layerMsg(this.$t('login.p_taccount'));
          return false;
        }
      }else{
         if (this.account.indexOf("@") == -1) {
          this.$utils.layerMsg(this.$t('login.p_temail'));
          return false;
        }
      }
      // this.index == 0 ? (types = "mobile") : "email";
      if (!this.sms_code) {
        return this.$utils.layerMsg(this.$t('register.emptyCode'));
      }
      if (!this.password) {
        return this.$utils.layerMsg(this.$t('register.emptyPassword'));
      }
      if (!this.$utils.checkPassword(this.password)) {
        return this.$utils.layerMsg(this.$t('register.length'));
      }
      if (!this.re_password) {
        return this.$utils.layerMsg(this.$t('register.enterPassword2'));
      }
      if (this.password != this.re_password) {
        return this.$utils.layerMsg(this.$t('register.unlike'));
      }
      if(!this.vscode){
          return  this.$utils.layerMsg(this.$t('register.emptyCode'));
      }
      var data = {
        account: this.account,
        new_password: this.password,
        secondary_password: this.re_password,
        type: this.index == 0 ?'mobile':'email',
        auth_code: this.sms_code
      };
      this.$http
        .initDataToken({ url: "user/forget_password", data, type: "POST" })
        .then(res => {
          console.log(res);
          setTimeout(() => {
            this.$router.push({ name: "login" });
          }, 500);
        });
    },


    verifyAccount(ismobile) {
      if (ismobile == 0) {
        if (!this.account) {
          return this.$utils.layerMsg(this.$t('register.emptyPhone'));
        }
        if (!this.$utils.checkMobile(this.account)) {
          return this.$utils.layerMsg(this.$t('register.pRightPhone'));
        }
      } else {
        if (!this.account) {
          this.$utils.layerMsg(this.$t('register.emptyEmail'));
        }
        if (!this.$utils.checkEmail(this.account)) {
          return this.$utils.layerMsg(this.$t('register.pRightEmail'));
        }
      }
    },
    // 改变账号
    changeAccount() {
      this.hassend = false;
      if (this.interid) {
        clearInterval(this.interid);
      }
    }
  }
};
</script>
<style lang="">
.el-input__inner {
  border: none;
}
.mwidth {
  min-width: 440px;
}
    .vcode{
        width:100%;
        text-align: center;
        padding: auto;
        background-color: aliceblue;
    }
    .vsbutton{
        text-align: center;
        margin: auto;
    }
</style>
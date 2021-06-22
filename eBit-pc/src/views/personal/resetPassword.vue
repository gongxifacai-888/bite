<template>
<div class="forget-box">
    <div class="contentBK ">
        <div class="content-wrap">
            <div class="account">
                <div class="main" >
                    <div class="main_title fColorn">{{$t('login.e_pwd')}}</div>
                    <div class="register-input">
                        <span class="register-item">{{$t('login.old')}}</span>
                        <input type="password" autocomplete="new-password" class="input-main input-content bgpart bd nwhite"  v-model="old_password" id="pwd">
                    </div>
                    <div class="register-input">
                        <span class="register-item">{{$t('login.p_pwd')}}</span>
                        <input type="password" autocomplete="new-password" class="input-main input-content bgpart bd nwhite"  v-model="password" id="pwd">
                    </div>
                    <div class="register-input">
                        <span class="register-item">{{$t('register.enterPassword2')}}</span>
                        <input type="password" autocomplete="new-password" class="input-main input-content bgpart bd nwhite"  v-model="re_password" id="repwd">
                    </div>
                    <button class="register-button curPer" type="button" @click="resetPass" style="margin-top:20px">{{$t('login.e_confrim')}}</button>
                </div>
            </div>
        </div>
    </div>
    
</div>

</template>

<script>

export default {
  data() {
    return {
      isMb: true,
      account_number: "",
      phoneCode: "",
      showReset: false,
      old_password:'',
      password: "",
      re_password: ""
    };
  },
  created() {
    this.showReset= this.$route.query.fromSet;
  },
  methods: {
    resetPass() {
      var that = this;
      if (that.old_password == "") {
        that.$utils.layerMsg(this.$t('login.enterOld'))
        return;
      }
      if (that.password == "") {
        that.$utils.layerMsg(this.$t('login.p_pwd'))
        return;
      } else if (that.password.length <6) {
        that.$utils.layerMsg(this.$t('login.p_simple'))
        return;
      } else if (that.password !== that.re_password) {
        that.$utils.layerMsg(this.$t('login.p_t_notpwd'))
        return;
      } else {
        let data = {
          old_password:this.old_password,
					new_password: this.password,
					secondary_password: this.re_password,
        };
         this.$http.initDataToken({url:'user/amend_password',data,type:'POST'})
            .then(res=>{
                // that.$utils.layerMsg(res.msg)
                setTimeout(() => {
                     that.$router.push("/login");
                }, 500);
               
            })
      }
    }
  }
};
</script>

<style scoped>

.account {
  width: 1200px;
  margin: 0 auto;
  padding-top: 93px;
  overflow: hidden;
  min-height: 880px;
}
.main {
  position: relative;
  padding: 0 0 60px 30px;
}
.main_title {
  font-size: 36px;
  color: #c7cce6;
}
.register-item {
  display: block;
  height: 22px;
  color: #61688a;
  font-size: 12px;
}
.register-input {
  position: relative;
  margin-top: 20px;
}
.input-box {
  position: relative;
  margin-top: 40px;
}
.input-main {
  width: 520px;
  min-height: 46px;
  /* border: 1px solid #4e5b85; */
  padding: 0 20px;
  color: #c7cce6;
  font-size: 14px;
  border-radius: 3px;
  /* background-color: #1e2235; */
}
.icon {
  width: 48px;
  height: 48px;
  line-height: 48px;
  border-right: 1px solid #2e3753;
  position: absolute;
  top: 0;
}
.login-btn {
  width: 420px;
  margin-top: 40px;
  background: #5697f4;
  font-size: 16px;
  border-radius: 4px;
  color: #fff;
  line-height: 48px;
  cursor: pointer;
}
.noaccount {
  color: #fff;
}
.register-button {
  width: 200px;
  display: inline-block;
  line-height: 46px;
  background-color: #7a98f7;
  border-radius: 4px;
  color: #fff;
  border: none;
}
.have-account {
  font-size: 14px;
  display: inline-block;
  margin-left: 30px;
}
.right-tip {
  position: absolute;
  left: 620px;
  top: 70px;
  line-height: 24px;
  padding-right: 50px;
  margin-top: 10px;
  font-size: 14px;
  color: #61688a;
}
.code-box {
  width: 520px;
  border: 1px solid #4e5b85;
  background: #1e2235;
}
.code-box .input-main {
  width: 419px;
  border: none;
}
.code-box button {
  border: none;
  border-left: 1px solid #4e5b85;
  line-height: 44px;
  color: #7a98f7;
  background: #1e2235;
  width: 94px;
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

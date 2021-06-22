<template>
  <div class="pb20">
    <ul class="pt40 w80 mauto" v-if="authStatus==0">
      <li class="flex between alcenter">
        <span>{{$t('authentication.name')}}：</span>
        <input
          type="text"
          v-model="realName"
          :placeholder="$t('collect.pleaseenteraname')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 white"
        />
      </li>
      <li class="flex between alcenter mt20">
        <span>{{$t('collect.cardno')}}：</span>
        <input
          type="text"
          v-model="idaccount"
          :placeholder="$t('collect.p_cardno')"
          class="ht30 bdinput lh30 plr10 radius2 ml10 w80 white"
        />
      </li>
      <li class="mt20 ft16">{{$t('collect.upImg')}}</li>
      <li class="flex between alcenter mt20">
        <div class="tc">
          <div class="uploads posRelt wt120 ht120 mt10 pointer">
            <img :src="src1" class="wt1201 ht120" v-if='src1' alt />
            <div class="bgline white ht120 tc lh120" v-else>{{$t('collect.front_img')}}</div>
            <input
              type="file"
              class="wt120 ht120 abstort lf0 btm0 pointer"
              accept="image/*"
              name="file"
              @change="upload(1)"
            />
          </div>
        </div>
        <div class="tc ml30">
          <div class="uploads posRelt wt120 ht120 mt10 pointer">
            <img :src="src2" class="wt1201 ht120" v-if='src2' alt />
            <div class="bgline white ht120 tc lh120" v-else>{{$t('collect.back_img')}}</div>
            <input
              type="file"
              class="wt120 ht120 abstort lf0 btm0 pointer"
              accept="image/*"
              name="file"
              @change="upload(2)"
            />
          </div>
        </div>
        <div class="tc ml30">
          <div class="uploads posRelt wt120 ht120 mt10 pointer">
            <img :src="src3" class="wt1201 ht120" v-if='src3' alt />
            <div class="bgline white ht120 tc lh120" v-else>{{$t('collect.hand_img')}}</div>
            <input
              type="file"
              class="wt1201 ht120 abstort lf0 btm0 pointer"
              accept="image/*"
              name="file"
              @change="upload(3)"
            />
          </div>
        </div>
      </li>
    </ul>
    <div
      class="bgblue w50 ht40 flex alcenter jscenter mt40 white radius6 mauto ft16"
      @click="submits"
      v-if="authStatus==0"
    >{{$t('collect.submits')}}</div>
    <div class="pt120 w100 tc ft20 white" v-if="authStatus==1">{{$t('authentication.ing')}}</div>
    <div class="pt120 w100 tc ft20 white" v-if="authStatus==2">{{$t('authentication.has')}}</div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      realName: "",
      idaccount: "",
      // src1: require("@/assets/images/real1.png"),
      // src2: require("@/assets/images/real2.png"),
      // src3: require("@/assets/images/real3.png"),
      src1: '',
      src2: '',
      src3: '',
      authStatus: 0,
      src1Status:false,
      src2Status:false,
      src3Status:false,
    };
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      var that = this;
      that.$http.initDataToken({ url: "user_real/center" }, false).then(res => {
        that.authStatus = res.review_status;
      });
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
            that.src1 = res.url;
            that.src1Status = true;
          } else if (options == 2) {
            that.src2 = res.url;
            that.src2Status = true;
          } else if (options == 3) {
            that.src3 = res.url;
            that.src3Status = true;
          }
        });
    },
    submits() {
      var that = this;
      if(!that.realName){
        that.$utils.layerMsg(this.$t('collect.p_name'));
        return false;
      }
      if(!that.idaccount){
        that.$utils.layerMsg(this.$t('collect.p_cardno'));
        return false;
      }
      if(!that.src1Status){
        that.$utils.layerMsg(this.$t('collect.up_cardz'));
        return false;
      }
      if(!that.src2Status ){
        that.$utils.layerMsg(this.$t('collect.up_cardf'));
        return false;
      }
      if(!that.src3Status){
        that.$utils.layerMsg(this.$t('collect.up_cardhand'));
        return false;
      }
      console.log(that.src1)
      that.$http
        .initDataToken(
          {
            url: "user_real/real_name",
            data: {
              name: that.realName,
              card_id: that.idaccount,
              front_pic: that.src1,
              reverse_pic: that.src2,
              hand_pic: that.src3
            },
            type:"post"
          },
          true
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
</style>
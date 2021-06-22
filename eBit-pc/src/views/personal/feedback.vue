<template>
  <div class="pb20">
    <div class="pt20 ml20 w60 flex between alcenter">
      <span>{{$t('feed.type')}}</span>
      <el-select v-model="typeName" class="ht40 lh40 radius2 ml10 w80 nwhite bdinput" @change="changePay">
        <el-option v-for="item in typeList" :key="item.name" :label="item.name" :value="item">
          <span>{{ item.name }}</span>
        </el-option>
      </el-select>
    </div>
    <ul class="pt20 ml20 w60">
      <li class="flex between alcenter">
        <span>{{$t('feed.titel')}}：</span>
        <input
          type="text"
          v-model="title"
          :placeholder="$t('feed.p_title')"
          class="ht40 bdinput lh40 plr10 radius2 ml10 w80 nwhite"
        />
      </li>

      <li class="flex between alcenter mt20">
        <span>{{$t('feed.content')}}：</span>
        <textarea class="bdinput w80 ht180 nwhite plr10 ptb10" :placeholder="$t('feed.min_content')" style="background:transparent"  v-model="user_content"></textarea>
      </li>
    </ul>
    <div class="w60 flex jsend">
      <div
        class="bgblue w100 ht40 flex alcenter jscenter mt40 white radius6 ml20 ft16 pointer"
        @click="payMethodSave"
      >{{$t('collect.submits')}}</div>
    </div>
  </div>
</template>
<script>
import Axios from "axios";
export default {
  data() {
    return {
      title:'',
      user_content:'',
      user_image:'',
      index:0,
      listItem:[],
      typeList:[],
      typeName:'',
      type_id:''
    };
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      var that = this;
      this.$http.initDataToken({ url: "feedback/type" }, false).then(res => {
        this.typeList = res;
        this.type_id = res[0].id;
        this.typeName = res[0].name;
      });
    },
    changePay(e) {
      this.type_id = e.id;
      this.typeName = e.name;
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
      if(!that.title){
        return that.$utils.layerMsg(that.$t('feed.p_title'));
      }
      if(!that.user_content){
        return that.$utils.layerMsg(that.$t('feed.p_content'));
      }
      that.$http
        .initDataToken(
          {
            url: "feedback/feedback",
            data: {
              title:that.title,
              content:that.user_content,
              type_id:that.type_id
            },
            type: "POST",
          },
          false
        )
        .then(res => {
          that.$utils.layerMsg(that.$t('feed.ok'),'success');
          that.title = '';
          that.user_content = '';
        });
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
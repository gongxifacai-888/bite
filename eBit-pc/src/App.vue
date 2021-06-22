<template>
  <div id="app">
    <div class="flex alcenter between plr20 bgheader  ft14 bdbheader headerbase">
      <div class="flex alcenter between h48 lh48 app">
        <img src="../static/imgs/logo.png" alt="" class="pointer mr50" @click="goHome" style="height:35px" v-if="value=='light'">
        <img src="../static/imgs/logo.png" alt="" class="pointer mr50" @click="goHome" style="height:35px" v-else>
        <router-link
          :to="{name:item.page}"
          class="mr25"
          v-for="(item,i) in listLeft"
          :key="i"
          v-if="item.show"
        >{{item.name}}</router-link>
      </div>
      <div class="flex alcenter between">
        <router-link :to="{name:'login'}" class="plr20 bdblue ptb5 radius2" v-if="!account">{{$t('login.login')}}</router-link>
        <div class="plr20 pointer" v-else>
          <el-dropdown @command="personalLink">
            <span class="el-dropdown-link ">
              <i class="iconfont icon-zhanghao ft16 "></i>
              {{account}}
              <i class="el-icon-arrow-down el-icon--right"></i>
            </span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item
                v-for="(item,index) in accountList"
                :command="item"
                :key="index"
              >{{item.title}}</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </div>
        <router-link
          :to="{name:'register'}"
          class="ml10 plr20 bdblue ptb5 radius2"
          v-show="!account"
        >{{$t('register.register')}}</router-link>
        <div class="plr20 pointer" style="width:160px;">
          <el-select v-model="langname" class="flex1 bdline" @change="changeLang">
            <el-option
              class="flex alcenter between"
              v-for="item in languages"
              :key="item.value"
              :name="item.name"
              :value="item"
            >
              <span class="fl">{{ item.name }}</span>
              <img :src="item.img" alt class="ml10" style="height:20px;width:30px;" />
            </el-option>
          </el-select>
        </div>
        <div class="ml10">
          <el-switch
            :width="40"
            v-model="value"
            active-icon-class="el-icon-moon"
            inactive-icon-class="el-icon-sunny"
            @change="changeUI"
          ></el-switch>
        </div>
      </div>
    </div>
    <router-view @getUserinfo="getinfo"></router-view>
  </div>
</template>
<script>
// import utils from '@/lib/helper.js'

export default {
  name: "App",
  data() {
    return {
      listLeft: [
        // {
        //   name: "首页",
        //   page: "home",
        //   show: true
        // },
        {
          name: this.$t('market.market'),
          page: "market",
          show: false
        },
        {
          name: this.$t('header.legal'),
          page: "legalIndex",
          show: true
        },
        {
          name: this.$t('header.trade'),
          page: "trade",
          show: true
        },
        {
          name: this.$t('header.lever'),
          page: "lever",
          show: true
        },
        {
          name: this.$t('header.micro'),
          page: "second",
          show: false
        },
        {
          name: this.$t('header.myshop'),
          page: "myshop",
          show: false
        },
        {
          name: this.$t('cun.title'),
          page: "deposit",
          show: true
        },
        {
          name: this.$t('header.assets'),
          page: "assets",
          show: true
        }
      ],
      account: "",
      token: "",
      is_seller: false,
      showPersonal: false,
      accountList: [
        {
          title: this.$t('header.account'),
          type: 1,
          page: "accountSettings"
        },
        // {
        //   title: "交易日志",
        //   type:1,
        //   page: "transactionLog"
        // },
        {
          title: this.$t('header.pays'),
          type: 1,
          page: "collectionSettings"
        },
        {
          title: this.$t('header.auth'),
          type: 1,
          page: "authentication"
        },
        {
          title: this.$t('feed.feed'),
          type: 1,
          page: "feedback"
        },
        {
          title: this.$t('header.login'),
          type: 2,
          page: "login"
        }
      ],
      languages: [
        { 
          name: "中文", 
          img: require("./assets/images/zh.png"), 
          value: "zh" 
        },
        {
          name: "English",
          img: require("./assets/images/en.png"),
          value: "en"
        },
        {
          name: "繁體中文",
          img: require("./assets/images/hk.png"),
          value: "hk"
        },
        { 
          name: "日本語",
          img: require("./assets/images/jp.png"), 
          value: "jp"
        }
      ],
      lang: "",
      langname: "",
      value:false,
    };
  },
  watch: {
    is_seller: {
      //监听我的商铺
      handler(newInfo) {
        if(newInfo=='false'){
          newInfo = false;
        }else if(newInfo=='true'){
          newInfo = true;
        }
        let index;
        this.listLeft.map(function(item, i) {
          if (item.page == "myshop") {
            return (index = i);
          }
        });
        this.listLeft[index].show = newInfo;
      },
      immediate: true
    },
    deep: true
  },
  created() {
    this.token = localStorage.getItem("token");
    console.log(localStorage.getItem("token"));

    let theme = localStorage.getItem('theme') || 'dark';
    this.value= theme == 'light'?false:true;
    this.$utils.theme(theme);
    this.$store.commit('changeTheme',theme);

    if (this.token) {
      this.account = localStorage.getItem("account") || "";
      this.is_seller = localStorage.getItem("is_seller") || false;
    }
    var lang = localStorage.getItem("lang");
    if (lang == "zh") {
      this.langname = "中文";
    } else if (lang == "en") {
      this.langname = "English";
    } else if (lang == "hk") {
      this.langname = "繁體中文";
    } else{
      this.langname = "日本語";
    }
    // 创建链接
    // this.$utils.initSocket();

    // this.$http.initDataToken({url:'currency/quotation_new'})
    // .then(res=>{
    //   console.log(res,'0000');
    // })
    // .catch(err=>{
    //   console.log(err)
    // })

    //  this.$http.initDataToken({url:'transaction/in',type:'POST',data:{currency_id:1,legal_id:3,num:'0.0001',password: "123456",price: "7737.17"}})
    //     .then(res=>{
    //       console.log(res);
    //     })
  },

  methods: {
    //黑白切换
    changeUI(){
      // this.value=!this.value;
      let theme = 'light';
      if(this.value){
        theme = 'dark'
      }
      this.$utils.theme(theme);
      this.$store.commit('changeTheme',theme);
    },
    // 切换语言
    changeLang(e) {
      console.log(e);
      var that = this;
      that.langname = e.name;
      that.lang = e.value;
      window.localStorage.setItem("locale", that.lang);
      window.localStorage.setItem("lang", that.lang);
      this.$i18n.locale = that.lang;
      window.location.reload();
    },
    getinfo(page) {
      this.$http.initDataToken({ url: "user/info" },false).then(res => {
        console.log(res);
        localStorage.setItem("uid", res.id);
        localStorage.setItem("is_seller", res.is_seller);
        let account = res.email || res.mobile;
        localStorage.setItem("account", account);
        this.account = account;
        this.is_seller = res.is_seller;
        // this.$router.push({ name: "home" });
        if (page) {
          this.$router.push({ path: page });
        } else {
          this.$router.push({ path: "/home" });
        }
      });
    },
    personalLink(command) {
      var that = this;
      if (command.type == 2) {
        that.$http
          .initDataToken({ url: "user/logout", type: "post" })
          .then(res => {
            // localStorage.clear();
             localStorage.removeItem('token');
            this.token = "";
            this.account = "";
            this.is_seller = false;
            var lang = 'jp';
            setTimeout(function() {
              that.$router.push({ path: '/login', query: { type: 1 } });
            }, 1000);
          });
      } else {
        that.$router.push({ name: command.page });
      }
      // console.log(command);
    },
    goHome(){
      this.$router.push({name:'home'})
    }
  }
};
</script>

<style lang="scss">
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.showline .router-link-exact-active {
  border-bottom: 2px solid #357ce1;
}
.account-choose {
  // display: none;
  cursor: default;
  position: absolute;
  top: 48px;
  right: -60px;
  z-index: 999;
  width: 160px;
  line-height: 40px;
  background-color: #fff;
  z-index: 5000;
  border-radius: 3px;
  // box-shadow: 0 0 2px 4px rgba(0, 0, 0, 0.2);
  p {
    padding-left: 20px;
    img {
      width: 14px;
      vertical-align: middle;
      margin-right: 8px;
      display: inline-block;
    }
    img:nth-child(2) {
      display: none;
    }
  }
  p:hover {
    color: #5697f4;
    // background-color: #1a243b;
  }
  p:hover img:nth-child(2) {
    display: inline-block;
  }
  p:hover img:nth-child(1) {
    display: none;
  }
}
.el-input__icon {
  line-height: 30px;
}
.el-scrollbar__wrap {
  // overflow-x: hidden;
  overflow: auto!important;
}
.el-input__inner{
  border: none!important;
}
.el-pagination.is-background .btn-next, .el-pagination.is-background .btn-prev, .el-pagination.is-background .el-pager li {
  background-color: #e5ebf5;
}
</style>

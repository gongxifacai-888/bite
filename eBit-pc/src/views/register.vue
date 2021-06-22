<template>
    <div class="flex alcenter jscenter  vh100 ">
        <div class="bgpart radius4 plr60 ptb40 mb50 w25 mwidth">
            <div class="tc ft20">{{$t('register.welcome')}}</div>
            <div class="mt40 ">
                <div class="bdbea2 flex alcenter bold">
                    <div class="pb10 mr20 pointer" :class="{'active2px':index==i}" v-for="(item,i) in infos" :key="i" @click='changeMethod(i)'>{{item}}</div>
                </div>
                <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
                    <!-- <div class="w20 bdr tc">+86</div> -->
                    <el-select v-model="country_code"  class="w25" @change='changeCountry' v-show="index==0">
                        <el-option 
                            v-for="item in countries"
                            :key="item.name"
                            :label="'+'+item.global_code"
                            :value="item.id">
                            <span class="fl">{{ item.name }}</span>
                            <span  class="fr ft14">+{{ item.global_code }}</span>
                        </el-option>
                     </el-select>
                    <input type="text" class="flex1 h100 bdle7 pl20 nwhite" v-model="account" @change="changeAccount" :placeholder="index == 0 ? $t('register.enterPhone') : $t('register.enterEmail')">
                </div>
                <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
                    <input type="text" class="flex1 h100 bdre7 pl20 nwhite" v-model="sms_code" :placeholder="$t('register.enterCode')">
                    <div class="w20 bdr tc blue pointer" @click="sendcode" v-show="!hassend">{{$t('login.send')}}</div>
                    <div class="w20 bdr tc blue "  v-show="hassend">{{second}}s</div>
                </div>
                <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
                    <input type="password" class="flex1 h100 bdle7 pl20 nwhite" v-model="password" :placeholder="$t('register.enterPassword')">
                </div>
                <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
                    <input type="password" class="flex1 h100 bdle7 pl20 nwhite" v-model="re_password" :placeholder="$t('register.enterPassword2')">
                </div>
                <div class="mt20 bde7 radius2 ht50 lh50 flex alcenter">
                    <input type="text" class="flex1 h100 bdle7 pl20 nwhite" v-model="invite_code" :placeholder="$t('register.invitationCode')">
                </div>
                <div class="flex alcenter mt10">
                    <input type="checkbox" v-model="read">
                    <p class="pl5 ft16 gray9 flex alcenter">
                        <span class="blueColor pl5 pointer" @click="goDetail(66,26)" >{{$t('login.p_private')}}</span>
                    </p>
                </div>
                <div class="vcode mt20 bde7 radius2 ht50 lh50 flex alcenter">
                    <Vcode :show="isShow" @success="onSuccess" @close="onClose" />
                    <button @click="submit" class="vsbutton">点此开始验证</button>
                </div>

                <div class="mt40 bgblue white tc h48 lh48 radius2 pointer" @click='register'>{{$t('register.register')}}</div>
            </div>
        </div>
    </div>
</template>
<script>
import Vcode from "../../node_modules/vue-puzzle-vcode";
export default {
    data(){
        return {
            infos:[this.$t('register.phoneRegister'),this.$t('register.emailRegister')],
            index:0,
            account:'',
            password:'',
            re_password:'',
            invite_code:'',
            countries: [],
            country_code: '',
            sms_code:'',
            hassend:false,
            second:60,
            interid:'',
            countryCodeId:"",
            isok:false,
            read:true,
            diasabledInput:false,
            isShow: false,
            vscode:false,
        }
    },
    components: {
        Vcode,
    },
    created(){
        // 获取国家
        this.$http.initDataToken({url:'default/area_list'},false)
        .then(res=>{
           this.countries=res;
           if(res.length>0){
               this.country_code='+'+res[0].global_code;
               this.countryCodeId = res[0].id;
           }
        })
        //获取邀请码
        //获取邀请码
        if(this.$route.query.extension_code){
            this.invite_code = this.$route.query.extension_code || '';
            this.diasabledInput = true;
        }
        let that = this;
        document.onkeypress = function(e) {
            var keycode = document.all ? event.keyCode : e.which;
            if (keycode == 13) {
                that.register();
                return false;
            }
        };
    },
    methods:{
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

          // 详情
        goDetail(id,category_id) {
            this.$router.push({
                name: "notice",
                query: { 
                    id: id,
                    category_id: category_id
                }
            });
        },
        changeCountry(e){
            console.log(e);
            this.countryCodeId = e;
        },
        changeMethod(e){
            this.index=e;
            this.hassend=false;
            if(this.interid){
                clearInterval(this.interid)
            }
            this.second = 60;
            this.account='';
            this.password='';
            this.re_password='';
            this.sms_code='';
        },
        // 发送验证码
        sendcode(){
            if(this.hassend){
                return ;
            }
           
            // 手机
            if(this.index==0){
                this.verifyAccount(this.index);
                if(!this.isok){
                    return;
                }
                this.$http.initDataToken({url:'notify/send_sms',type:'POST',data:{
                    to:this.account,
                    type:1,
                    area_id:this.countryCodeId
                }}).then(res=>{
                    this.hassend=true;
                    this.interid=setInterval(()=>{
						if(this.second>=1){
							this.second--;
						}else{
                            this.hassend=false
                            this.second = 60 ;
							clearInterval(this.interid);
						}
					},1000)
                })
            }else{
                // 邮箱验证码
                this.verifyAccount(this.index);
                if(!this.isok){
                    return;
                }
                this.$http.initDataToken({url:'notify/send_email',type:'POST',data:{
                    to:this.account,
                    type:1,
                    area_id:this.countryCodeId
                }}).then(res=>{
                    this.hassend=true;
                    this.interid=setInterval(()=>{
						if(this.second>=1){
							this.second--;
						}else{
                            this.hassend=false
                            this.second = 60 ;
							clearInterval(this.interid);
						}
					},1000)
                })
            }
            // 邮箱
        },
        // 注册
        register(){
            this.verifyAccount(this.index);
            if(!this.isok){
                return;
            }
             if(!this.read){
                return this.$utils.layerMsg(this.$t('login.p_first'))
            }
            var data={
                account: this.account,
				password: this.password,
				invite_code: this.invite_code,
				re_password:this.re_password,
				area_id:this.countryCodeId,
				sms_code:this.sms_code,
                vscode: this.vscode
            }
            // this.index==0?data.country_code=this.country_code-0:'';
            data.type = this.index==0 ? 'mobile':'email';
            if(!this.sms_code){
                return  this.$utils.layerMsg(this.$t('register.emptyCode'))
            };
            if(!this.password){
                return this.$utils.layerMsg(this.$t('register.emptyPassword'))
            };
            if(!this.$utils.checkPassword(this.password)){
                return  this.$utils.layerMsg(this.$t('register.length'))
            };
            if(!this.re_password){
                return this.$utils.layerMsg(this.$t('register.enterPassword2'))
            };
            if(this.password!=this.re_password){
                return this.$utils.layerMsg(this.$t('register.unlike'))
            }
            if(!this.vscode){
                return  this.$utils.layerMsg(this.$t('register.emptyCode'))
            }
            this.$http.initDataToken({url:'user/register',data,type:'POST'})
            .then(res=>{
                console.log(res);
                setTimeout(() => {
                     this.$router.push({name:'login'})
                }, 500);
               
            })
        },
        verifyAccount(ismobile){
             if(ismobile==0){
                if(!this.account){
                    this.isok = false;
                    return  this.$utils.layerMsg(this.$t('register.emptyPhone'))
                }
                if(!this.$utils.checkMobile(this.account)){
                    this.isok = false;
                    return  this.$utils.layerMsg(this.$t('register.pRightPhone'))
                }
            }else{
                if(!this.account){
                    this.isok = false;
                    this.$utils.layerMsg(this.$t('register.pRightEmail'))
                }
                if(!this.$utils.checkEmail(this.account)){
                    this.isok = false;
                    return  this.$utils.layerMsg(this.$t('register.emptyPassword'))
                }
            }
            this.isok = true;
        },
        // 改变账号
        changeAccount(){
            this.hassend=false;
            if(this.interid){
                clearInterval(this.interid)
            }
        },
         // 详情
        goDetail(id,category_id) {
            this.$router.push({
                name: "notice",
                query: { 
                    id: id,
                    category_id: category_id
                }
            });
        },
        
    },
   
}
</script>
<style lang="">
    .el-input__inner{
        border: none;
    }
    .mwidth{
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
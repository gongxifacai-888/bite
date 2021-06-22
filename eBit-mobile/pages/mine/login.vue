<template>
	<view class="pt30 " :class="{'light':theme=='light'}">
		<view class="tc">
			<image src="../../static/logo.png" mode="widthFix" class="wt100 ht100"></image>
			<!-- <view class="blue">登录ebitcex</view> -->
		</view>
		<view class="mt20 plr10">
			<view class="flex alcenter">
				<view class="plr15 mr20  ptb10 ft16 posRelt " :class="{'blue2':loginActive==0}" @tap="loginActive=0;">
					{{$t('login').l_mobile}}
					<image src="/static/line_blue.png" class="myline" v-if="loginActive==0"></image>
				</view>
				<view class="plr15 ptb10 ft16 posRelt" :class="{'blue2':loginActive==1}" @tap="loginActive=1;">
					{{$t('login').l_email}}
					<image src="/static/line_blue.png" class="myline" v-if="loginActive==1"></image>
				</view>
			</view>
			<view class="mt20 plr10 ptb10 radius4 bgHeader">
				<view class="flex alcenter bdb_myblue mb10 ">
					<view class="flex  alcenter between mr10" v-if="loginActive==0 && isShowCode">
						<picker :value="index" :range="array" class=" flex1"  @change="bindPickerChange" range-key="code">
							<!-- <view class="uni-input">{{array[index].name_cn}}</view> -->
							<view class="ft12" v-if="array[index]">{{array[index].code}}{{array[index].global_code}}</view>
						</picker>
						<image src="/static/image/trade_arrow_down.png" class="wt10 h8 ml5"></image>
					</view>
					<view class="flex alcenter flex1">
						<input type="text"  v-model="user_string" class="h40 lh40  flex1" v-if="loginActive==1" :placeholder="$t('login').p_email">
						<input type="number" v-model="user_string" class="h40 lh40  flex1" v-else :placeholder="$t('login').p_mobile">
					</view>
				</view> 
				<view class="mb10">
					<view class="flex bgwhite alcenter bdb_myblue ">
						<input type="text" password="" v-model="password" @input="passwordInput" class="h40 lh40 flex1" :placeholder="$t('login').p_pwd">
						<image src="/static/image/password.png" class="wt15 h15 ml10"></image>
					</view>
					<!-- <view class="ft10 pt5 chengse" v-if="verifyPwd">密码输入不正确</view> -->
				</view>
				<view class="flex bgwhite alcenter bdb_myblue mb10" v-if="isShowCode">
					<input type="text"  v-model="code" class="h40 lh40 flex1" :placeholder="$t('login').p_vcode">
					<view class="ml10 plr10 white bgBlue ptb5 radius4" @tap="getCode">
						<text v-if="!hasSend">{{$t('login').get_code}}</text>
						<text v-else>{{second}}s</text>
					</view>
				</view>
				
				<!-- code verify -->
				<div class="vcode mt20 bde7 radius2 ht50 lh50 flex alcenter">
					<Vcode :show="isShow" @success="onSuccess" @close="onClose" />
					<button @click="submit" class="vsbutton">点此开始验证</button>
				</div>
				
				<view class="mt15 flex alcenter">
					<checkbox value="cb" :checked="isRemember"  @tap="tapChecked" style="transform:scale(0.7);color:'#1881d2'"/>
					<text>{{$t('login').rem_pwd}}</text>
				</view>
				<view class="mt20 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="login">{{$t('login').login}}</view>
			</view>
			<view class="mt30 flex alcenter between">
				<view class="flex alcenter">
					<text>{{$t('login').noaccount}}</text>
					<navigator url="register" class="blue2">{{$t('login').register}}</navigator>
				</view>
				<navigator url="forgetPwd" class="blue2">{{$t('login').forget_pwd}}</navigator>
			</view>
		</view>
	</view>
</template>

<script>
	import country from '@/common/country.js'
	import Vcode from "../../node_modules/vue-puzzle-vcode";  // verify 
	import {mapState} from 'vuex'
	export default{
		data(){
			return {
				loginActive:0,
				user_string:'',
				password:'',
				re_password:'',
				code:'',
				array: [],
				index: 0,
				area_code:'',
				hasSend:false,
				second:60,
				isRemember:false,
				verifyPwd:false,
				isShowCode:false,
				timeInterval:'',
				lang:'',
				isShow: false,  // verify 
				vscode:false,  // verify 
			}
		},
		
		// verify 
		components: {
			Vcode,
		},
		// verify 
		
		computed:{
			...mapState(['theme']),
		},
		onLoad() {
			
			uni.setNavigationBarTitle({
				title:this.$t('login').login
			})
			this.getCountry();
			if(uni.getStorageSync('isRemember')==0){
				this.isRemember=false;
			}else{
				this.isRemember=true;
				this.user_string= uni.getStorageSync('userString');
				this.password=uni.getStorageSync('userPwd');
			}
			this.lang=uni.getStorageSync('lang');
			this.$utils.initData({url:'default/setting',data:{key:'login_use_sms'}},(res)=>{
				this.isShowCode = res==1 ? true :false;
				uni.setStorageSync('smcode',this.isShowCode);
			})
			// this.$utils.getGlobalSettting({url:'env.json'},res=>{
			// 	console.log(res);
			// 	uni.setStorageSync('socketPort',res.socket_io_port)
			// 	uni.setStorageSync('smcode',res.login_need_smscode)
			// 	this.isShowCode=res.login_need_smscode;
			// })
		},
		onShow() {
			this.$utils.setThemeTop(this.theme)
		},
		methods:{
			// verify start
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
			// verify end
			
			//获取国家列表
			getCountry(){
				this.$utils.initData({url:'default/area_list'},(res,msg)=>{
					// console.log(res)
					this.array = res;
					this.area_code = res[0].id;
				})
			}, 
			// 区号选择
			bindPickerChange(e){
				this.index=e.target.value;
				let item=this.array[this.index];
				this.area_code=item.id;
			},
			// 获取验证码
			getCode(){
				if(this.timeInterval) return;
				if(this.loginActive==0){
					if(!this.user_string){
						return this.$utils.showLayer(this.$t('login').p_taccount)
					}
					this.$utils.initData({url:'sms_send',type:'POST',data:{user_string:this.user_string,type:5,country_code:this.area_code,lang:this.lang}},(res,msg)=>{
						this.$utils.showLayer(msg);
						this.hasSend=true;
						this.timeInterval=setInterval(()=>{
							if(this.second>=1){
								this.second--;
							}else{
								this.hasSend=false
								clearInterval(this.timeInterval);
							}
						},1000)
					})
				}else{
					if(!this.$utils.checkEmail(this.user_string)){
						return this.$utils.showLayer(this.$t('login').p_email)
					}
					this.$utils.initData({url:'sms_mail',type:'POST',data:{user_string:this.user_string,type:5,lang:this.lang}},(res,msg)=>{
						this.$utils.showLayer(msg);
						this.hasSend=true;
						this.timeInterval=setInterval(()=>{
							if(this.second>=1){
								this.second--;
							}else{
								this.hasSend=false
								clearInterval(this.timeInterval);
							}
						},1000)
					})
				}
			},
			// 是否选中记住密码
			tapChecked(){
				this.isRemember=!this.isRemember;
				if(this.isRemember){
					uni.setStorageSync('isRemember',1)
				}else{
					uni.setStorageSync('isRemember',0)
				}
			},
			// 密码框输入判断提示
			passwordInput(e){
				this.hasSend=false;
				if(e.target.value.length<6){
					this.verifyPwd=true;
				}else{
					this.verifyPwd=false;
				}
			},
			login(){
				if(this.loginActive==1){
					// 邮箱登录
					if(!this.$utils.checkEmail(this.user_string)){
						return this.$utils.showLayer(this.$t('login').p_temail)
					}
				}else{
					if(!this.user_string){
						return this.$utils.showLayer(this.$t('login').p_taccount)
					}
				}
				
				let login_type = this.loginActive == 0? 'mobile' :'email';
				if(!this.password){
					return this.$utils.showLayer(this.$t('login').p_pwd)
				}
				if(this.password.length<6){
					return this.$utils.showLayer(this.$t('login').p_pwderr)
				}
				if(this.isShowCode&& !this.code && !this.vscode){
					return this.$utils.showLayer(this.$t('login').p_vcode)
				}
				// verify
				if(!this.vscode){
					// return console.log("wrong vscode")
					return this.$utils.showLayer(this.$t('login').p_slide)
				}
				var data={
					account: this.user_string,
                    password: this.password,
					sms_code: this.code,
					lang:this.lang,
					vscode: this.vscode,
					login_type:login_type
				}
				// if(this.loginActive==0){
					// 手机登录
					data.country_code=this.area_code;
				// } 
				this.$utils.initData({url:'user/login',data,type:'POST'},(res,msg)=>{
					uni.setStorageSync('token',res);
					uni.setStorageSync('accountNumber',this.user_string);
					uni.setStorageSync('codeId',this.area_code);
					if(this.isRemember){
						uni.setStorageSync('userString',this.user_string);
						uni.setStorageSync('userPwd',this.password);
					}
					setTimeout(() => { 
						uni.reLaunch({
							url:'/pages/home/home'
						})
					}, 1500);
				})
			},
			
		}
	}
</script>

<style>
    .mwidth{
        min-width: 440px;
    }
    .vcode{
        
        text-align: center;
        padding: auto;
    }
    .vsbutton{
		width:100%;
        text-align: center;
        margin: auto;
        background-color: aliceblue;
		border: none;
		border-radius: inherit;
    }
</style>

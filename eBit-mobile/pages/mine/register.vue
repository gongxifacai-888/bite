<template>
	<view class="plr20" :class="{'light':theme=='light'}">
		<view class="mt30 ">
			<view class="ft24 bold" v-if="loginActive==0">
				{{$t('login').r_mobile}}
			</view>
			<view class="ft24 bold" v-else>
				{{$t('login').r_email}}
			</view>
			<view class="flex alcenter mt10">
				<image src="../../static/image/regi.png" class="wt15 h15"></image>
				<view class="chengse ft14">{{$t('login').r_noedit}}</view>
			</view>
			<view class="mt40 ">
				<view class="flex alcenter bdb_myblue mb10 ">
					<view class="flex  alcenter between mr10" v-if="loginActive==0">
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
				<!-- code verify -->
				<div class="vcode mt20 bde7 radius2 ht50 lh50 flex alcenter">
					<Vcode :show="isShow" @success="onSuccess" @close="onClose" />
					<button @click="submit" class="vsbutton">点此开始验证</button>
				</div>
				
				<view class="mt50 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="goNext">{{$t('login').r_next}}</view>
			</view>
			
			<view class="mt20 flex between alcenter">
				<view class="blue2" v-if="loginActive==1" @click="loginActive=0;user_string=''">
					{{$t('login').r_mobile}}
				</view>
				<view class="blue2" v-else @click="loginActive=1;user_string=''">
					{{$t('login').r_email}}
				</view>
				<!-- #ifdef H5 -->
				<view class="blue2" @tap="downLoad">
					{{$t('login').downApp}}
				</view>
				<!-- #endif -->
			</view>
			<!-- #ifdef APP-PLUS -->
			<view class="mt45">
				<view class="flex alcenter">
					<text>{{$t('login').r_hasaccount}}?</text>
					<navigator url="login" class="blue2">{{$t('login').login}}</navigator>
				</view>
			</view>
			<!-- #endif -->
			
		</view>
		<view class="fixed my_layer pos_l0b0 w100 ht100" v-if="isShowCodeLayer" @tap="isShowCodeLayer=false;">
			<view class="fixed pos_l0b0 bgHeader w100 pt20 pb10 plr10" @tap.stop>
				<view class="ptb14 tc blue2 ft14 bold" v-if="loginActive==0">{{$t('login').r_sms}}</view>
				<view class="ptb14 tc blue2 ft14 bold" v-else>{{$t('login').r_smsemail}}</view>
				<view class="flex bgwhite alcenter bdb_blue mb10 mt20" >
					<input type="text" v-if="loginActive==0" v-model="code" class="h40 lh40 flex1" :placeholder="$t('login').r_pmobilecode">
					<input type="text" v-else v-model="code" class="h40 lh40 flex1" :placeholder="$t('login').r_pemailcode">

					<view class="ml10 plr10 white bgBlue ptb5 radius4" @tap="getCode">
						<text v-if="!hasSend">{{$t('login').r_send}}</text>
						<text v-else>{{second}}s</text>
					</view>
					
				</view>	
				<view class="mt30 bgBlue radius4 ptb10 white ft14 tc mb10" @tap.stop="confirm">{{$t('login').r_next}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	// import country from '@/common/country.js'
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
				area_code:'+86',
				hasSend:false,
				second:60,
				isRemember:false,
				verifyPwd:false,
				isShowCodeLayer:false,
				timeInterval:'',
				lang:'',
				nationality:'China',
				invitecode:'',
				downUrl:'',
				isShow: false,  // verify 
				vscode:false,  // verify 
			}
		},
		
		// verify 
		components: {
			Vcode,
		},
		// verify
		
		onLoad(e) {
			this.getCountry();
			this.invitecode=e.invite_code;
			console.log(this.invitecode)
			this.lang=uni.getStorageSync('lang');
			uni.setNavigationBarTitle({
				title:this.$t('login').register
			})
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
		    this.$utils.setThemeTop(this.theme);
			this.init();
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
			
			
			init(){
				var that = this;
				this.$utils.initData({url:'default/setting',data:{
					key:'app_download_url'
				}},(res,msg)=>{
					that.downUrl = res;
				})
			},
			//获取国家列表
			getCountry(){
				this.$utils.initData({url:'default/area_list'},(res,msg)=>{
					console.log(res)
					this.array = res;
					this.area_code = res[0].id;
				})
			},
			// 区号选择
			bindPickerChange(e){
				this.index=e.target.value;
				let item=this.array[this.index];
				this.area_code=item.global_code;
				this.nationality=item.code;
				this.area_code=item.id;
			},
			// 获取验证码
			getCode(){
				if(this.timeInterval) return;
				let url;
				if(this.loginActive==0){
					url='notify/send_sms';
					if(!this.user_string){
						return this.$utils.showLayer(this.$t('login').p_taccount)
					}
				}else{
					url='notify/send_email';
					if(!this.$utils.checkEmail(this.user_string)){
						return this.$utils.showLayer(this.$t('login').p_temail)
					}
				}
				this.$utils.initData({url:url,type:'POST',data:{to:this.user_string,type:1,area_id:this.area_code}},(res,msg)=>{
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
			},
			// 下一步
			goNext(){
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
				uni.navigateTo({
					url:`setPwd?user_string=${this.user_string}&is_mobile=${this.loginActive}&areaCode=${this.area_code}&invitecode=${this.invitecode}`
				})
				// this.isShowCodeLayer=true;
				// this.hasSend=false;
				// this.timeInterval='';
				// this.code='';
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
			// 确定
			confirm(){
				if(!this.code){
					return this.$utils.showLayer(this.$t('login').p_vcode)
				}
				// verify
				if(!this.vscode){
					// return console.log("wrong vscode")
					return this.$utils.showLayer(this.$t('login').p_slide)
				}
				
				uni.navigateTo({
					url:`setPwd?user_string=${this.user_string}&is_mobile=${this.loginActive}&code=${this.code}&areaCode=${this.area_code}&invitecode=${this.invitecode}`
				})
				return;
				var data={
					user_string: this.user_string,
					lang:this.lang
				}
				var url=''
				if(this.loginActive==0){
					// 手机注册
					data.country_code=this.area_code;
					data.mobile_code=this.code;
					url="user/check_mobile";
					data.scene='register';
				}else{
					data.email_code=this.code;
					data.country_code=this.area_code;
					url='user/check_email';
					data.scene='register';
				}
				this.$utils.initData({url,data,type:'POST'},(res,msg)=>{
					this.$utils.showLayer(msg);
					this.isShowCodeLayer=false;
					setTimeout(()=>{
						uni.navigateTo({
							url:`setPwd?user_string=${this.user_string}&is_mobile=${this.loginActive}&code=${this.code}&areaCode=${this.area_code}&invitecode=${this.invitecode}`
						})
					},1500)
				})
			},
			downLoad(){
				location.href=this.downUrl;
			}
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

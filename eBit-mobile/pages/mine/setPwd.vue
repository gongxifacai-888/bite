<template>
	<view class="pt20 plr20" :class="{'light':theme=='light'}">
		<!-- <view class="flex alcenter mt10">
			<image src="../../static/image/regi.png" class="wt15 h15"></image>
			<view class="chengse ft14">注册后不能修改</view>
		</view> -->
		<view class="mb10 mt30">
			<view class="flex bgwhite alcenter bdb_myblue ">
				<input type="text" password="" v-model="password" @input="passwordInput1" class="h40 lh40 flex1" :placeholder="$t('login').p_pwd">
				<image src="/static/image/password.png" class="wt15 h15 ml10"></image>
			</view>
			<view class="ft10 pt5 chengse" v-if="verifyPwd1">{{$t('login').p_len}}</view>
		</view>
		<view class="mb10">
			<view class="flex bgwhite alcenter bdb_myblue ">
				<input type="text" password="" v-model="re_password" @input="passwordInput2" class="h40 lh40 flex1" :placeholder="$t('login').p_confirmPwd">
				<image src="/static/image/password.png" class="wt15 h15 ml10"></image>
			</view>
			<view class="ft10 pt5 chengse" v-if="verifyPwd2">{{$t('login').p_notsame}}</view>
		</view>
		<view class="flex bgwhite alcenter bdb_myblue ">
			<input type="text" v-model="code" class="h40 lh40 flex1" :placeholder="$t('login').p_vcode" />
			<view>
				<button size="mini" type="primary" :disabled="disable" :loading="load" @click="send">{{ codeText }}</button>
			</view>
		</view>
		<view class="flex bgwhite alcenter bdb_myblue ">
			<input type="text" v-model="invite_code"  class="h40 lh40 flex1" :placeholder="$t('login').p_invitecode">
		</view>
		<view class="mt20 flex alcenter">
			<view class=" flex alcenter">
				<checkbox value="cb" :checked="isAgree"  @tap="tapChecked" style="transform:scale(0.7);color:'#1881d2'"/>
				<text>{{$t('login').p_agree}}</text>
			</view>
			<navigator :url="'/pages/home/agreement'">
				<view class="ml10 blue2">《{{$t('login').p_private}}》</view>
			</navigator>
		</view>
		<view class="mt45 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="register">{{$t('login').p_set}}</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return {
				user_string:'',
				password:'',
				re_password:'',
				code:'',
				area_code:'',
				isAgree:false,
				invite_code:'',
				verifyPwd1:false,
				verifyPwd2:false,
				lang:'',
				disable:false,
				load:false,
				codeText:this.$t('login.r_send'),
				from_invite:false,
				is_mobile:'',
				isloading:false
			}
		},
		onLoad(e) {
			this.user_string=e.user_string;
			// this.code=e.code;
			this.is_mobile=e.is_mobile;
			this.area_code=e.areaCode;
			if(e.invitecode&&e.invitecode!='undefined'){
				this.invite_code=e.invitecode || '';
				this.from_invite = true;
			}
			this.lang=uni.getStorageSync('lang');
			uni.setNavigationBarTitle({
				title:this.$t('login').p_setPwd
			})
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
		    this.$utils.setThemeTop(this.theme)
		},
		methods:{
			//发送验证码
			send() {
				var url;
				if(this.is_mobile==0){
					url='notify/send_sms';
				}else{
					url='notify/send_email';
				}
				var datas = {
					to: this.user_string,
					area_id: this.area_code,
					type: 1
				};
				this.$utils.initDataToken({ url: url, data: datas, type: 'post' }, (res, msg) => {
					this.$utils.showLayer(msg);
					this.disable = true;
					// this.load = true;
					var times = 60;
					var timer = setInterval(() => {
						times--;
						if (times < 10) {
							times = '0' + times;
						}
						this.codeText = times + 's';
						if (times <= 0) {
							clearInterval(timer);
							this.disable = false;
							this.load = false;
							this.codeText = this.$t('login.r_send');
						}
					}, 1000);
				});
			},
			passwordInput1(e){
				if(e.target.value.length<6){
					this.verifyPwd1=true;
				}else{
					this.verifyPwd1=false;
				}
			},
			passwordInput2(e){
				if(e.target.value!=this.password){
					this.verifyPwd2=true;
				}else{
					this.verifyPwd2=false;
				}
			},
			tapChecked(){
				this.isAgree=!this.isAgree;
			},
			register(){
				if(!this.password){
					return this.$utils.showLayer(this.$t('login').p_pwd)
				}
				if(this.password.length<6){
					return this.$utils.showLayer(this.$t('login').p_simple)
				}
				if(this.password!=this.re_password){
					return this.$utils.showLayer(this.$t('login').p_inputagain)
				}
				if(!this.code){
					return this.$utils.showLayer(this.$t('login').p_vcode)
				}
				// if(this.invite_code){
				// 	return this.$utils.showLayer(this.$t('login').p_inviteInput)
				// }
				if(!this.isAgree){
					return this.$utils.showLayer(this.$t('login').p_first)
				}
				var data={
					account: this.user_string,
					password: this.password,
					invite_code: this.invite_code,
					re_password:this.re_password,
					// lang:this.lang,
					area_id:this.area_code,
					sms_code:this.code
				}
				if(this.is_mobile==0){
					// 手机
					data.type='mobile';
					// data.country_code=this.area_code;
				}else{
					// 邮箱
					data.type='email';
					// data.country_code=this.area_code;
				}
				// if(this.isloading){
				// 	return;
				// }
				// this.isloading = true;
				// setTimeout(()=>{
				// 	this.isloading = false;
				// },3000)
				this.$utils.initData({url:'user/register',data,type:'POST'},(res,msg)=>{
					this.$utils.showLayer(msg);
					if(res.jump&&this.from_invite){
						location.href=res.jump;
					}else{
						setTimeout(()=>{
							uni.reLaunch({
								url:'login'
							})
						},1000)
					}
				})
			}
		}
	}
</script>

<style>
</style>

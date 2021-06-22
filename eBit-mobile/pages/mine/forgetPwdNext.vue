<template>
	<view class="pt20 plr20" :class="{'light':theme=='light'}">
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
		<view class="mt45 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="reset">{{$t('login').e_chongzhi}}</view>
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
				is_mobile:''
			}
		},
		onLoad(e) {
			this.user_string=e.account;
			// this.code=e.code;
			this.area_code = e.area_code;
			this.is_mobile = e.is_mobile;
			uni.setNavigationBarTitle({
				title: this.$t('login').e_chongzhi
			});
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
					type: 2
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
			reset(){
				var types = '';
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
				if(this.user_string.indexOf('@')!=-1){
					types="email";
				}else{
					types="mobile";
				}
				var data={
					account: this.user_string,
				    new_password: this.password,
					auth_code: this.code,
					secondary_password:this.re_password,
					type:types
				}
				this.$utils.initDataToken({url:'user/forget_password',data,type:'POST'},(res,msg)=>{
					this.$utils.showLayer(msg);
					setTimeout(()=>{
						uni.navigateTo({
							url:'login'
						})
					},1000)
				})
			}
		}
	}
</script>

<style>
</style>

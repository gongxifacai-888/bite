<template>
	<view class="pt20 plr20" :class="{ light: theme == 'light' }">
		<!-- <view class="flex alcenter mt10">
			<image src="../../static/image/regi.png" class="wt15 h15"></image>
			<view class="chengse ft14">注册后不能修改</view>
		</view> -->
		<view class="mb10 mt30">
			<view class="flex bgwhite alcenter bdb_myblue ">
				<input type="text" password="" v-model="password" @input="passwordInput1" class="h40 lh40 flex1" :placeholder="$t('login').e_pdeal" />
				<image src="/static/image/password.png" class="wt15 h15 ml10"></image>
			</view>
			
			<view class="ft10 pt5 chengse" v-if="verifyPwd1">{{ $t('login').p_len }}</view>
		</view>
		<view class="mb10">
			<view class="flex bgwhite alcenter bdb_myblue ">
				<input type="text" password="" v-model="re_password" @input="passwordInput2" class="h40 lh40 flex1" :placeholder="$t('login').e_pdealConfrim" />
				<image src="/static/image/password.png" class="wt15 h15 ml10"></image>
			</view>
			<view class="ft10 pt5 chengse" v-if="verifyPwd2">{{ $t('login').p_notsame }}</view>
		</view>

		<view class="flex bgwhite alcenter bdb_myblue ">
			<input type="text" v-model="code" class="h40 lh40 flex1" :placeholder="$t('login').p_vcode" />
			<view>
				<button size="mini" type="primary" :disabled="disable" :loading="load" @click="send">{{ codeText }}</button>
			</view>
		</view>
		<!-- code verify -->
		<div class="vcode mt20 bde7 radius2 ht50 lh50 flex alcenter">
			<Vcode :show="isShow" @success="onSuccess" @close="onClose" />
			<button @click="submit" class="vsbutton">点此开始验证</button>
		</div>
		<!-- <view class="mt20 flex alcenter">
			<view class=" flex alcenter">
				<checkbox value="cb" :checked="isAgree"  @tap="tapChecked" style="transform:scale(0.7);color:'#1881d2'"/>
				<text>我同意</text>
			</view>
			<view class="ml10 blue2">《用户协议及隐私政策》</view>
		</view> -->
		<view class="mt45 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="register">{{ $t('login').e_confrim }}</view>
	</view>
</template>

<script>
import Vcode from "../../node_modules/vue-puzzle-vcode";  // verify 
import { mapState } from 'vuex';
export default {
	data() {
		return {
			user_string: '',
			password: '',
			re_password: '',
			code: '',
			area_code: '',
			isAgree: false,
			invite_code: '',
			verifyPwd1: false,
			verifyPwd2: false,
			lang: '',
			disable: false,
			load: false,
			codeText: this.$t('login').r_send,
			accountNumber: '',
			codeId: '',
			isShow: false,  // verify 
			vscode:false,  // verify 
		};
	},

	
	onLoad(e) {
		// this.getUserInfo();
		this.user_string = e.user_string;
		this.code = e.code;
		this.is_mobile = this.is_mobile;
		this.area_code = this.areaCode;
		this.lang = uni.getStorageSync('lang');
		uni.setNavigationBarTitle({
			title: this.$t('login').e_dealPwd
		});
	},
		// verify 
		components: {
			Vcode,
		},
		// verify
	
	computed: {
		...mapState(['theme'])
	},
	onShow() {
		this.$utils.setThemeTop(this.theme);
		this.accountNumber = uni.getStorageSync('accountNumber');
		this.codeId = uni.getStorageSync('codeId');
	},
	methods: {
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
		
		//获取用户信息
		getUserInfo() {
			var that = this;
			this.$utils.initDataToken({ url: 'user/info', data: {}, type: 'get' }, (res, msg) => {
				that.accountNumber = res.account_number;
			});
		},
		//发送验证码
		send() {
			var reg = /^1[345678]\d{9}$/;
			var emreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
			var url = 'notify/send_sms';
			if (emreg.test(this.accountNumber)) {
				url = 'notify/send_email';
			}
			var datas = {
				to: this.accountNumber,
				area_id: this.codeId,
				lang: this.lang,
				type: 3
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
						this.codeText = this.$t('login').r_send;
					}
				}, 1000);
			});
		},
		passwordInput1(e) {
			if (e.target.value.length < 6) {
				this.verifyPwd1 = true;
			} else {
				this.verifyPwd1 = false;
			}
		},
		passwordInput2(e) {
			if (e.target.value != this.password) {
				this.verifyPwd2 = true;
			} else {
				this.verifyPwd2 = false;
			}
		},
		tapChecked() {
			this.isAgree = !this.isAgree;
		},
		register() {
			var that = this;
			if (!this.password) {
				return this.$utils.showLayer(this.$t('login').e_pdeal);
			}
			if (this.password.length < 6) {
				return this.$utils.showLayer(this.$t('login').p_simple);
			}
			if (this.password != this.re_password) {
				return this.$utils.showLayer(this.$t('login').p_inputagain);
			}
			if (!this.code) {
				this.$utils.showLayer(this.$t('login').p_vcode);
				return;
			}
			// verify
			if(!this.vscode){
				// return console.log("wrong vscode")
				return this.$utils.showLayer(this.$t('login').p_slide)
			}
			var data = {
				password: this.password,
				secondary_password: this.re_password,
				auth_code: this.code,
			};
			this.$utils.initDataToken({ url: 'user/amend_pay_password', data, type: 'POST' }, (res, msg) => {
				that.$utils.showLayer(msg);
				setTimeout(() => {
					uni.navigateBack({
						delta: 1
					});
				}, 1000);
			});
		}
	}
};
</script>

<style>
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

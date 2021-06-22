<template>
	<view class="pt20 plr20" :class="{ light: theme == 'light' }">
		<view class="flex alcenter mb20" v-if="coinList[index]">
			<picker class="bd_input ptb10 plr10 tc flex1 radius4" :value="index" :range="coinList" @change="pickChange" range-key="code">
				<view class="flex between">
					<text>{{ $t('bind.cur_select') }}</text>
					<text v-if="coinList[index]">{{ coinList[index].code }}</text>
				</view>
			</picker>
		</view>
		<view class="" v-if="coinList.length&&coinList[index].currency_protocols">
			<view class="mt10" v-if="coinList[index].currency_protocols.length > 1">
				<view class="">{{ $t('bind.liantype') }}</view>
				<view class="flex mt10">
					<view
						v-for="(item, index) in coinList[index].currency_protocols"
						:key="index"
						class="mr10 ptb5 plr10 bd_input radius4"
						:class="name == item.chain_protocol.code ? 'active' : ''"
						@tap="selectCharge(item)"
					>
						{{ item.chain_protocol.code }}
					</view>
				</view>
			</view>
		</view>
		<view class="flex alcenter mb20">
			<text>{{ $t('bind.addr') }}：</text>
			<input type="text" value="" v-model="address" class="flex1 plr15 h40 bdb1f" :placeholder="$t('bind.p_addr')" />
		</view>
		<view class="flex alcenter">
			<text>{{ $t('bind.code') }} ：</text>
			<view class="bdb1f flex alcenter flex1">
				<input type="text" value="" class="flex1 plr15 h40 " v-model="code" :placeholder="$t('login.p_vcode')" />
				<view class="ml10 plr10 white bgBlue ptb5 radius4" @tap="getCode">
					<text v-if="!hasSend">{{ $t('login.r_send') }}</text>
					<text v-else>{{ second }}s</text>
				</view>
			</view>
		</view>
		<view class="mt40 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="bindAddress">{{ $t('bind.bind') }}</view>
	</view>
</template>

<script>
import { mapState } from 'vuex';
export default {
	data() {
		return {
			index: 0,
			coinList: [],
			uerid: '',
			hasSend: false,
			timeInterval: '',
			address: '',
			code: '',
			second: 60,
			name: '',
			coinType: [],
			currencyName: '',
			contractAddress: '',
			multiProtocol: 0
		};
	},
	onLoad() {
		this.getCoinList();
		uni.setNavigationBarTitle({
			title: this.$t('bind').bindAddr
		});
	},
	computed: {
		...mapState(['theme'])
	},
	onShow() {
		this.$utils.setThemeTop(this.theme);
	},
	methods: {
		pickChange(e) {
			console.log(e);
			var that = this;
			var lists = that.coinList;
			that.index = e.target.value;
			that.address = '';
			that.code = '';
			that.hasSend = false;
			that.second = 60;
			lists.forEach((item, index) => {
				if (item.id == that.coinList[that.index].id) {
					if (item.currency_protocols.length > 1) {
						that.multiProtocol = 1;
					}
				}
			});
			if (that.multiProtocol == 1) {
				that.name = that.coinList[that.index].currency_protocols[0].chain_protocol.code;
				that.currencyName = that.coinList[that.index].code;
				that.contractAddress = that.coinList[that.index].currency_protocols[0].token_address;
			} else {
				if(that.coinList[that.index].currency_protocols.length >0){
					that.name = that.coinList[that.index].currency_protocols[0].chain_protocol.code;
					that.currencyName = that.coinList[that.index].code;
				}else{
					that.name = '';
					that.currencyName = that.coinList[that.index].code;
					that.contractAddress = '';	
				}
				
			}
			that.getAddress();
		},
		getCoinList() {
			var that = this;
			that.$utils.initDataToken({ url: 'block_chain/currency_protocols' }, res => {
				that.coinList = res;
				if (res[0].currency_protocols&&res[0].currency_protocols.length>1) {
					// that.getType(res.accounts[0].currency_id);
					that.name = res[0].currency_protocols[0].chain_protocol.code;
					that.currencyName = res[0].code;
					that.contractAddress = res[0].currency_protocols[0].token_address;
					that.getUserInfo();
				} else {
					if(res[0].currency_protocols&&res[0].currency_protocols.length>0){
						that.name = res[0].currency_protocols[0].chain_protocol.code;
						that.currencyName = res[0].code;
						// that.contractAddress = res.accounts[0].contract_address;
					}else{
						that.name = '';
						that.currencyName = res[0].code;
						// that.contractAddress = res.accounts[0].contract_address;
					}
					
					that.getUserInfo();
				}
			});
		},
		getUserInfo() {
			this.$utils.initDataToken({ url: 'user/info' }, res => {
				this.uerid = res.id;
				this.getAddress();
			});
		},
		// 获取链类型
		getType(currency) {
			var that = this;
			that.coinType = [];
			this.$utils.initDataToken(
				{
					url: 'wallet/get_info',
					type: 'POST',
					data: {
						currency: currency
					}
				},
				res => {
					console.log(res);
					that.coinType = res.type_data;
					that.name = res.type_data[0].type;
					that.currencyName = res.type_data[0].name;
					that.contractAddress = res.type_data[0].contract_address;
					that.getAddress();
				}
			);
		},
		// 选择充币类型
		selectCharge(options) {
			console.log(options);
			var that = this;
			that.code = '';
			that.hasSend = false;
			that.second = 60;
			that.contractAddress = options.token_address;
			that.name = options.chain_protocol.code;
			that.currencyType = options.chain_protocol.code;
			that.currencyName = options.code;
			that.getAddress();
		},
		getAddress() {
			this.address = '';
			this.$utils.getAddressOnline(
				{
					url: 'GetDrawAddress',
					data: {
						user_id: this.uerid,
						coin_name: this.name,
						contract_address: this.contractAddress
					}
				},
				res => {
					console.log(res);
					if (res.code == 0) {
						this.address = res.data.address;
					}
				}
			);
		},
		// 获取验证码
		getCode() {
			if (this.timeInterval) return;
			this.$utils.getAddressOnline({ url: 'SendVerificationcode', data: { user_id: this.uerid } }, res => {
				console.log(res);
				if (res.code == 0) {
					this.$utils.showLayer(this.$t('bind.sendSuccess'));
					this.hasSend = true;
					this.timeInterval = setInterval(() => {
						if (this.second >= 1) {
							this.second--;
						} else {
							this.hasSend = false;
							clearInterval(this.timeInterval);
						}
					}, 1000);
				} else {
					// if(res.errorinfo=='不能重复发送验证码'){
					// 	this.$utils.showLayer('Cannot send verification code repeatedly')
					// }
					this.$utils.showLayer(res.errorinfo);
				}
			});
		},
		bindAddress() {
			if (!this.address) {
				return this.$utils.showLayer(this.$t('bind.p_addr'));
			}
			if (!this.code) {
				return this.$utils.showLayer(this.$t('login.p_vcode'));
			}

			var obj = {
				user_id: this.uerid,
				address: this.address,
				coin_name: this.name,
				contract_address: this.contractAddress,
				verificationcode: this.code,
				t: Date.parse(new Date()) / 1000
			};
			// var obj = {
			// 	user_id:2,
			// 	address:'BANG btc',
			// 	coin_name:'BTC',
			// 	contract_address:'',
			// 	verificationcode:'738646',
			// 	t:1566373963,
			// };
			var obj_str = JSON.stringify(obj);
			console.log(obj_str);
			console.log(this.$MD5(obj_str));
			// console.log(this.$MD5('{"user_id":2,"address":"BANG btc","coin_name":"BTC","contract_address":"","verificationcode":"738646","t":1566373963}'))
			var sign = this.$MD5(obj_str + 'abcd4321');
			console.log(sign);
			this.$utils.getAddressOnline({ url: 'BindDrawAddress', data: { data: obj_str, sign }, type: 'POST' }, res => {
				console.log(res);
				if (res.code == 0) {
					// 成功
					this.$utils.showLayer(this.$t('bind.bindOk'));
					uni.navigateBack({
						delta: 1
					});
				} else {
					this.$utils.showLayer(res.errorinfo);
				}
			});
		}
	}
};
</script>

<style>
.active {
	color: #007aff;
	border-color: #007aff;
}
</style>

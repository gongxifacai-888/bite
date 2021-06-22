<template>
	<view class="" :class="{ light: theme == 'light' }">
		<view class="ptb20 bgPart plr20">
			<view class="ft18">{{ currencyName || '--' }} {{ $t('assets.mention') }}</view>
			<view class="mt10">{{ $t('trade.use') }} {{ balance }} {{ currencyName || '--' }}</view>
		</view>
		<view class="mt10 plr20" v-if="coinInfo.currency_protocols&&coinInfo.currency_protocols.length > 1">
			<view class="">{{$t('bind.liantype')}}</view>
			<view class="flex mt10">
				<view
					v-for="(item, index) in coinInfo.currency_protocols"
					:key="index"
					class="mr10 ptb5 plr10 bd_input radius4"
					:class="name == item.chain_protocol.code ? 'active' : ''"
					@tap="selectCharge(item)"
				>
					{{ item.chain_protocol.code }}
				</view>
			</view>
		</view>
		<view class="plr20 pb30">
			<view class="mb20 mt20">
				<view class="">{{ $t('assets.mentionaddr') }}</view>
				<input type="text" v-model="address" class="bdb1f h40 w100 " disabled="" :placeholder="$t('assets.p_addr')" />
			</view>
			<view class="mb20">
				<view class="">{{ $t('trade.num') }}</view>
				<view class="flex alcenter between bdb1f">
					<input type="number" class=" h40 flex1" v-model="number" @input="numberChange" :placeholder="$t('assets.minnum') + (coinInfo.draw_min || '0.00')" />
					<view class="flex alcenter">
						<text class="blue ft14 pr10 bdr_white50">{{ currencyName || '--' }}</text>
						<view class="pl10" @tap="all">{{ $t('trade.all') }}</view>
					</view>
				</view>
			</view>
			<view class="mb20">
				<view class="">memo</view>
				<input type="text" class="bdb1f h40 w100" v-model="memoText" :placeholder="$t('assets.p_memo')" />
			</view>
			<view class="mb20">
				<view class="">{{ $t('login.s_dealpwd') }}</view>
				<input type="text" password="" class="bdb1f h40 w100" v-model="password" :placeholder="$t('login.e_pdeal')" />
			</view>
			<view class="mb20 ptb10 flex alcenter between">
				<view class="">{{ $t('assets.recivenum') }}</view>
				<view class="">{{ reciveNumber }} {{ currencyName || '--' }}</view>
			</view>
			<view class="mb20">{{ $t('trade.fee') }}：{{ coinInfo.draw_rate*100 || '--' }}% {{ currencyName || '--' }}</view>
			<view class="mt40 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="mention">{{ $t('assets.mention') }}</view>
		</view>
	</view>
</template>

<script>
import { mapState } from 'vuex';
export default {
	data() {
		return {
			password: '',
			currency: '',
			coinInfo: {},
			address: '',
			name: '',
			number: '',
			reciveNumber: '0.0',
			chargeType: [],
			contractAddress: '',
			userId: '',
			currencyName: '',
			currencyType: '',
			balance: '',
			labelText: '',
			walletData: [],
			showMemo:false,
			memoText:"",
			currencyProtocolId:'',
			account_type_id:''
		};
	},
	onLoad(e) {
		uni.setNavigationBarTitle({
			title: this.$t('assets').mention
		});
		this.id = e.id;
		this.currency = e.currency;
		this.name = e.name;
		this.account_type_id = e.account_type_id;
		this.getCoinInfo();
	},
	computed: {
		...mapState(['theme'])
	},
	onPullDownRefresh() {
		this.getCoinInfo();
	},
	onShow() {
		this.$utils.setThemeTop(this.theme);
	},
	methods: {
		getCoinInfo() {
			this.$utils.initDataToken({url:'account/detail',data:{currency_id:this.currency,account_type_id:this.account_type_id,id:this.id}},res=>{
				uni.stopPullDownRefresh();
				this.coinInfo=res.currency;
				this.currencyName = res.currency.code;
				this.balance = res.balance;
				this.userId = res.user_id;
				if(res.currency.currency_protocols.length >0){
					this.currencyProtocolId = res.currency.currency_protocols[0].id;
					this.name = res.currency.currency_protocols[0].chain_protocol.code;
					this.contractAddress = res.currency.currency_protocols[0].token_address;
					this.getAddress(res.user_id);
				}else{
					this.getAddress(res.user_id);
				}
				
			})
			// this.$utils.initDataToken({ url: 'account/get_info', type: 'POST', data: { currency_id: this.currency } }, res => {
			// 	uni.stopPullDownRefresh();
			// 	this.currencyName = res.name;
			// 	this.balance = res.change_balance;
			// 	this.walletData = res.wallet_data;
			// 	if(res.make_wallet == 2){
			// 		this.showMemo = true;
			// 	}else{
			// 		this.showMemo = false;
			// 	}
			// 	if (res.multi_protocol == 1) {
			// 		this.chargeType = res.type_data;
			// 		this.contractAddress = res.type_data[0].contract_address;
			// 		this.name = res.type_data[0].name;
			// 		this.coinInfo = res.type_data[0];
			// 		this.currencyType = res.type_data[0].type;
			// 		var ids = res.type_data[0].id;
					
			// 	} else {
			// 		this.contractAddress = res.contract_address;
			// 		this.name = res.name;
			// 		this.coinInfo = res;
			// 		// if (res.wallet_data && res.wallet_data.length > 0) {
			// 		// 	this.labelText = res.wallet_data[0].label;
			// 		// }
			// 	}
			// 	this.getUserInfo();
			// });
		},
		// getUserInfo() {
		// 	this.$utils.initDataToken({ url: 'user/info' }, res => {
		// 		this.getAddress(res.id);
		// 		this.userId = res.id;
		// 	});
		// },
		getAddress(id) {
			let contract_address='';
			// if(this.coinInfo.currency_protocols.length>0){
			// 	contract_address = this.coinInfo.currency_protocols[0].token_address;
			// }
			this.$utils.getAddressOnline(
				{ url: 'GetDrawAddress', data: { user_id: id, coin_name: this.name, contract_address:this.contractAddress} },
				res => {
					uni.stopPullDownRefresh();
					console.log(res);
					if (res.code == 0) {
						this.address = res.data.address;
					} else {
						this.$utils.showLayer(res.errorinfo);
					}
				}
			);
		},
		all() {
			this.number = this.balance;
			// this.reciveNumber = this.balance* (1 - this.coinInfo.rate / 100);
			this.reciveNumber = this.balance - (this.balance*(this.coinInfo.draw_rate));
		},
		numberChange(e) {
			// 到账数量
			// this.reciveNumber = e.target.value * (1 - this.coinInfo.draw_rate / 100);
			this.reciveNumber = e.target.value - (e.target.value*(this.coinInfo.draw_rate));
		},
		// 选择充币类型
		selectCharge(options) {
			var that = this;
			that.contractAddress = options.token_address;
			that.name = options.chain_protocol.code;
			// that.coinInfo = options;
			this.currencyProtocolId = options.id;
			// that.currencyType = options.type;
			that.address = "";
			that.memoText = "";
			// var ids = options.id;
			// if (that.walletData.length > 0) {
			// 	that.walletData.forEach(item => {
			// 		if (ids == item.currency) {
			// 			that.labelText = item.label;
			// 		}
			// 	});
			// }
			that.getAddress(that.userId);
		},
		// 复制标签地址
		copyLabel() {
			var that = this;
			// #ifdef APP-PLUS
			uni.setClipboardData({
				data: that.labelText,
				success: function() {
					that.$utils.showLayer(that.$t('assets.copy_success'));
				},
				fail: function() {
					that.$utils.showLayer(that.$t('assets.copy_err'));
				}
			});
			// #endif
		},
		mention() {
			if (!this.address) {
				return this.$utils.showLayer(this.$t('assets.p_addr'));
			}
			if (!this.number) {
				return this.$utils.showLayer(this.$t('assets.p_minnum'));
			}
			if (!this.password) {
				return this.$utils.showLayer(this.$t('login.e_pdeal'));
			}
			if (this.password.length < 6) {
				return this.$utils.showLayer(this.$t('login.e_pdealerr'));
			}
			this.$utils.initDataToken(
				{
					url: 'account/draw',
					type: 'POST',
					data: { currency_id: this.currency, number: this.number, address: this.address, pay_password: this.password,memo:this.memoText,currency_protocol_id:this.currencyProtocolId }
				},
				(res, msg) => {
					console.log(res);
					this.$utils.showLayer(msg);
					setTimeout(() => {
						// this.getCoinInfo();
						uni.navigateBack({
							delta: 1
						});
					}, 1500);
				}
			);
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

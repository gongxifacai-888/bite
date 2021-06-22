<template>
	<view class="pt20 plr15 blue" :class="{ light: theme == 'light' }">
		<view class="bgPart flex alcenter between plr15 ptb15 radius4">
			<text>{{ $t('assets.cur_coin') }}</text>
			<text>{{ currencyName || '--' }}</text>
		</view>
		<view class="mt10" v-if="walletData.length > 1">
			<view class="">{{$t('bind.liantype')}}</view>
			<view class="flex mt10">
				<view
					v-for="(item, index) in walletData"
					:key="index"
					class="mr10 ptb5 plr10 bd_input radius4"
					:class="name == item.chain_protocol.code ? 'active' : ''"
					@tap="selectCharge(item)"
				>
					{{ item.chain_protocol.code }}
				</view>
			</view>
		</view>
		<view class="flex between mt10 alcenter" v-if="labelText">
			<view class="">{{ labelText }}</view>
			<view class="wt80 h30 lh30 radius4 bgBlue white tc" @tap="copyLabel">复制标签</view>
		</view>
		<view class="mt10 bgPart radius4 ptb20 plr15 tc">
			<image :src="img" mode="widthFix" :style="{ width: size + 'px', height: size + 'px' }" class="mauto"></image>
			<view class="mt20 ft12 tc">{{ $t('assets.addr_charge') }}</view>
			<view class="tc ft12  mt5">{{ address }}</view>
			<view class="mt20 wt80 h30 lh30 radius4 mauto bgBlue white" @tap="fuzhi_invite">{{ $t('assets.coypaddr') }}</view>
		</view>
		<view class="mt20 ">
			<view class="mb5">
				<text class="ft12">{{ $t('assets.c_tip1') }}</text>
				<text class="ft12">{{ currencyName || '--' }}</text>
				<text class="ft12">{{ $t('assets.assets') }}，</text>
				<text class="ft12">{{ $t('assets.c_tip2') }}。</text>
			</view>
			<view class="mb5">
				<text class="ft12">{{ currencyName || '--' }}</text>
				<text class="ft12">{{ $t('assets.c_tip3') }}。</text>
			</view>
			<view class="ft12">{{ $t('assets.c_tip4') }}。</view>
			<!-- <view class="mb5">
				<text class="ft12">{{ $t('assets.c_tip5') }}：</text>
				<text class="mainnum ft12">{{ coinInfo.min_number || '--' }}</text>
				<text class="ft12">{{ currencyName || '--' }}</text>
				,
			</view> -->
			<!-- <view class="mb5 ft12">{{ $t('assets.c_tip6') }}。</view> -->
			<view class="mb5 ft12">{{ $t('assets.c_tip7') }}。</view>
			<view class="mb5 ft12">{{ $t('assets.c_tip8') }}。</view>
		</view>
	</view>
</template>

<script>
import QR from '@/common/qrcode.js';
import { mapState } from 'vuex';
export default {
	data() {
		return {
			title: '',
			currency: '',
			coinInfo: {},
			img: '',
			size: 160,
			address: '',
			name: '',
			chargeType: [],
			contractAddress: '',
			userId: '',
			currencyName: '',
			labelText: '',
			walletData: []
		};
	},
	onLoad(e) {
		this.currency = e.currency;
		this.name = e.name;
		uni.setNavigationBarTitle({
			title: this.$t('assets').charge
		});
		this.getCoinInfo();
	},
	onPullDownRefresh() {
		this.getCoinInfo();
	},
	computed: {
		...mapState(['theme'])
	},
	onShow() {
		this.$utils.setThemeTop(this.theme);
	},
	methods: {
		getCoinInfo() {
			this.$utils.initDataToken({ url: 'wallet/wallet', data: { currency_id: this.currency } }, res => {
				this.currencyName = res.code;
				this.walletData = res.wallets;
				this.coinInfo = res.wallets[0];
				this.name = res.wallets[0].chain_protocol.code;
				this.labelText = res.wallets[0].memo;
				this.address = res.wallets[0].address;
				this.creatQrcode();
				// if (res.wallets.length > 1) {
				// 	this.chargeType = res.wallets;
				// 	this.contractAddress = res.type_data[0].contract_address;
				// 	this.name = res.type_data[0].type;
				// 	this.coinInfo = res.type_data[0];
				// 	var ids = res.type_data[0].id;
				// 	if (res.wallet_data && res.wallet_data.length > 0) {
				// 		res.wallet_data.forEach(item => {
				// 			if (ids == item.currency) {
				// 				this.labelText = item.label;
				// 			}
				// 		});
				// 	}
				// } else {
				// 	this.contractAddress = res.contract_address;
				// 	this.name = res.type;
				// 	this.coinInfo = res;
				// 	if (res.wallet_data && res.wallet_data.length > 0) {
				// 		this.labelText = res.wallet_data[0].label;
				// 	}
				// }
				this.getUserInfo();
			});
		},
		getUserInfo() {
			this.$utils.initDataToken({ url: 'user/info' }, res => {
				this.userId = res.id;
				// this.getAddress(res.id);
			});
		},
		// 选择充币类型
		selectCharge(options) {
			var that = this;
			this.img = '';
			this.coinInfo = options;
			this.name = options.chain_protocol.code;
			this.labelText = options.memo;
			this.address = options.address;
			this.creatQrcode();
			// that.contractAddress = options.contract_address;
			// that.name = options.type;
			// that.coinInfo = options;
			// var ids = options.id;
			// if (that.walletData.length > 0) {
			// 	that.walletData.forEach(item => {
			// 		if (ids == item.currency) {
			// 			that.labelText = item.label;
			// 		}
			// 	});
			// }
			// that.getAddress(that.userId);
		},
		getAddress(id) {
			this.$utils.getAddressOnline({ url: 'walletMiddle/GetRechargeAddress', data: { user_id: id, coin_type: this.name, contract_address: this.contractAddress } }, res => {
				uni.stopPullDownRefresh();
				console.log(res);
				if (res.code == 0) {
					this.address = res.data.address;
					this.creatQrcode();
				}
			});
		},
		// 复制
		fuzhi_invite() {
			var that = this;
			// #ifdef APP-PLUS
			uni.setClipboardData({
				data: that.address,
				success: function() {
					that.$utils.showLayer(that.$t('assets.copy_success'));
				},
				fail: function() {
					that.$utils.showLayer(that.$t('assets.copy_err'));
				}
			});
			// #endif
		},
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
		creatQrcode() {
			if (this.address == '') {
				return false;
			}
			let img = QR.createQrCodeImg(this.address);
			this.img = img;
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

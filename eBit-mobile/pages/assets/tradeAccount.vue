<template>
	<view class="blue" :class="{ light: theme == 'light' }">
		<view class="plr20 pt20 pb15 bgPart">
			<text class="bold ft18 blue2" v-if="coinInfo.currency">{{ coinInfo.currency.code }}</text>
			<view class="mt10 flex alcenter mt15">
				<view class="flex1">
					<view class="blue ft12">{{ this.$t('trade.use') }}</view>
					<view class="mt5 ft12 ">{{ coinInfo.balance }}</view>
				</view>
				<view class="flex1 tc">
					<view class="blue ft12">{{ $t('assets.lock') }}</view>
					<view class="mt5 ft12 ">{{ coinInfo.lock_balance }}</view>
				</view>
				<view class="flex1 tr">
					<view class="blue ft12">{{ $t('assets.zhehe') }}(USD)</view>
					<view class="mt5">{{ coinInfo.convert_usd | filterDecimals(4) }}</view>
				</view>
			</view>
		</view>
		<!-- <view class="bgHeader h10"></view> -->
		<view class="plr10 ptb15 mt10 bgPart" style="min-height: 80vh;">
			<view class="ft16">{{ $t('assets.records') }}</view>
			<view class="mt10 pb100">
				<view class="flex alcenter ptb10 bdb_blue3 ">
					<view class="flex1">{{ $t('trade.num') }}</view>
					<view class="flex1 ">{{ $t('assets.record') }}</view>
					<view class="flex1 tr">{{ $t('trade.time') }}</view>
				</view>
				<view class="mt10 flex bdb_blue3 ptb5" v-for="(item, i) in logList" :key="i" v-if="logList.length > 0">
					<view class="flex1">{{ item.value - 0 }}</view>
					<view class="flex1 wordbreak pr10">
					{{ item.memo }} </br>
					<text v-if="item.extra_data&&item.extra_data.from">({{$t('assets.xiaji')}}{{item.extra_data.from}})</text>
					</view>
					<view class="flex1 tr">{{ item.created_at }}</view>
				</view>
				<view class="mt20 tc" v-if="logList.length == 0">
					<image src="../../static/image/anonymous.png" class="wt60 h60"></image>
					<view>{{ $t('home.norecord') }}</view>
				</view>
				<view class="tc gray7 ptb20" v-show="!hasMore && logList.length > 0">{{ $t('home.nomore') }}</view>
			</view>
		</view>
		<view class="fixed pos_l0b0 w100 bgHeader bdt2f flex alcenter ptb10 zdx100">
			<!-- 币币账户 -->
			<block v-if="tradeType == 'change_account' &&coinInfo.account_type&& coinInfo.account_type.is_recharge == 1">
				<navigator :url="'transfer?accountTypeId=' + coinInfo.account_type_id+'&id='+coinInfo.id+'&account_code='+coinInfo.account_type.account_code" class="flex1 tc" v-if="changeTransfer == 1">
					<image src="../../static/image/hz01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.transfer') }}</view>
				</navigator>
				<!-- 充币 -->
				<navigator
					:url="'charge?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + id"
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_recharge == 1"
				>
					<image src="../../static/image/cb01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.charge') }}</view>
				</navigator>
				<!-- 提币 -->
				<navigator
					:url="'mention?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + account_id +'&account_type_id='+coinInfo.account_type_id" 
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_draw == 1"
				>
					<image src="../../static/image/tb01.png" class="wt30 h30"></image>
					<view class="">{{ $t('assets.mention') }}</view>
				</navigator>
			</block>
			<!-- 法币账户 -->
			<block v-if="tradeType == 'legal_account' && coinInfo.account_type&&coinInfo.account_type.is_recharge == 1">
				<navigator :url="'transfer?accountTypeId=' + coinInfo.account_type_id+'&id='+coinInfo.id" class="flex1 tc" v-if="legalTransfer == 1">
					<image src="../../static/image/hz01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.transfer') }}</view>
				</navigator>
				<!-- 充币 -->
				<navigator
					:url="'charge?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + id"
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_recharge == 1"
				>
					<image src="../../static/image/cb01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.charge') }}</view>
				</navigator>
				<!-- 提币 -->
				<navigator
					:url="'mention?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + account_id +'&account_type_id='+coinInfo.account_type_id"
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_draw == 1"
				>
					<image src="../../static/image/tb01.png" class="wt30 h30"></image>
					<view class="">{{ $t('assets.mention') }}</view>
				</navigator>
			</block>
			<!-- 杠杠账户 -->
			<block v-if="tradeType == 'lever_account' && coinInfo.account_type&&coinInfo.account_type.is_recharge == 1">
				<navigator :url="'transfer?accountTypeId=' + coinInfo.account_type_id+'&id='+coinInfo.id" class="flex1 tc" v-if="leverTransfer == 1">
					<image src="../../static/image/hz01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.transfer') }}</view>
				</navigator>
				<!-- 充币 -->
				<navigator
					:url="'charge?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + id"
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_recharge == 1"
				>
					<image src="../../static/image/cb01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.charge') }}</view>
				</navigator>
				<!-- 提币 -->
				<navigator
					:url="'mention?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + account_id +'&account_type_id='+coinInfo.account_type_id"
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_draw == 1"
				>
					<image src="../../static/image/tb01.png" class="wt30 h30"></image>
					<view class="">{{ $t('assets.mention') }}</view>
				</navigator>
			</block>
			<!-- 极速合约账户 -->
			<block v-if="tradeType == 'micro_account' && coinInfo.account_type&&coinInfo.account_type.is_recharge == 1">
				<navigator :url="'transfer?accountTypeId=' + coinInfo.account_type_id+'&id='+coinInfo.id" class="flex1 tc" v-if="microTransfer == 1">
					<image src="../../static/image/hz01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.transfer') }}</view>
				</navigator>
				<!-- 充币 -->
				<navigator
					:url="'charge?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + id"
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_recharge == 1"
				>
					<image src="../../static/image/cb01.png" class="wt30 h30 "></image>
					<view class="">{{ $t('assets.charge') }}</view>
				</navigator>
				<!-- 提币 -->
				<navigator
					:url="'mention?currency=' + currency_id + '&name=' + coinInfo.currency.code + '&id=' + account_id +'&account_type_id='+coinInfo.account_type_id"
					class="flex1 tc"
					v-if="coinInfo.currency && coinInfo.currency.open_draw == 1"
				>
					<image src="../../static/image/tb01.png" class="wt30 h30"></image>
					<view class="">{{ $t('assets.mention') }}</view>
				</navigator>
			</block>
		</view>
	</view>
</template>

<script>
import { mapState } from 'vuex';
export default {
	data() {
		return {
			currency: '',
			tradeType: '',
			page: 1,
			isBottom: false,
			hasMore: true,
			coinInfo: {},
			logList: [],
			titles: [this.$t('assets.tradeacc'), this.$t('assets.leveracc'), this.$t('assets.legalacc'),this.$t('assets.microacc'),this.$t('assets.licai')],
			ExRate: 6.5,
			id: '',
			account_id: '',
			currency_id: '',
			logurl: '',
			changeTransfer: 0,
			legalTransfer: 0,
			leverTransfer: 0,
			microTransfer: 0,
			financialTransfer:0
		};
	},
	computed: {
		...mapState(['theme'])
	},
	onShow() {
		this.$utils.setThemeTop(this.theme);
		// this.$utils.setThemeBottom(this.theme)
	},
	onLoad(e) {
		this.id = e.id;
		this.tradeType = e.type;
		this.currency_id = e.currency_id;
		this.account_id = e.account_id;
		this.getDetail();
		var that = this;
		if (e.type == 'change_account') {
			that.logurl = 'change';
			uni.setNavigationBarTitle({
				title: that.titles[0]
			});
		} else if (e.type == 'lever_account') {
			that.logurl = 'lever';
			uni.setNavigationBarTitle({
				title: that.titles[1]
			});
		} else if (e.type == 'micro_account'){
			that.logurl = 'micro';
			uni.setNavigationBarTitle({
				title: that.titles[3]
			});
		}else if(e.type=='financial_account'){
			that.logurl = 'financial';
			uni.setNavigationBarTitle({
				title: that.titles[4]
			});
		} else{
			that.logurl = 'legal';
			uni.setNavigationBarTitle({
				title: that.titles[2]
			});
		}
		this.getLog();
		// uni.setNavigationBarTitle({
		// 	title:e.titleName
		// })
	},
	methods: {
		getDetail() {
			var that = this;
			that.$utils.initDataToken({ url: 'account/detail', data: { account_type_id: that.id, id: that.account_id } }, res => {
				uni.stopPullDownRefresh();
				that.coinInfo = res;
				if (res.currency.is_recharge_account && res.currency.account_types.length > 1) {
					var datas = res.currency.account_types;
					datas.forEach(function(item, index) {
						if (item.account_code == 'change_account' && item.is_recharge == 1) {
							that.changeTransfer = 1;
						} else if (item.account_code == 'legal_account' && item.is_recharge == 1) {
							that.legalTransfer = 1;
						} else if (item.account_code == 'lever_account' && item.is_recharge == 1) {
							that.leverTransfer = 1;
						} else if (item.account_code == 'micro_account' && item.is_recharge == 1) {
							that.microTransfer = 1;
						}
						else if (item.account_code == 'financial_account' && item.is_recharge == 1) {
							that.financialTransfer = 1;
						}
					});
				}
			});
		},
		goTrade() {
			let localData = uni.getStorageSync('tradeData') || {};
			let currency_name = this.coinInfo.currency_name;
			console.log(uni.getStorageSync('tradeData'), localData);
			if (localData.legal_name && localData.legal_name != currency_name) {
				console.log(123);
				localData.currency_name = currency_name;
				localData.currency_id = this.currency_id;
				uni.setStorageSync('tradeData', localData);
			}
			uni.switchTab({
				url: '/pages/trade/trade'
			});
		},
		getLog() {
			let that = this;
			let url = 'account_log/' + this.logurl;
			this.$utils.initDataToken({ url: url, data: { currency_id: this.currency_id, page: this.page, limit: 15 } }, res => {
				uni.stopPullDownRefresh();
				let data = res.data;
				that.isBottom = false;
				that.logList = that.page == 1 ? data : that.logList.concat(data);
				that.hasMore = res.last_page == res.current_page ? false : true;
			});
		}
	},
	onPullDownRefresh() {
		this.page = 1;
		(this.bottom = false), (this.hasMore = true), this.getLog();
		this.getDetail();
	},
	onReachBottom() {
		if (!this.hasMore) return;
		this.page++;
		this.getLog();
	}
};
</script>

<style></style>

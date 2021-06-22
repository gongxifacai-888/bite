<template>
	<view class="" :class="{ light: theme == 'light' }">
		<view class="plr15 ptb30 bgPart">
			<view class=" flex alcenter between bd2f">
				<view class="flex alcenter pl15 flex1">
					<view class="flex column alcenter ">
						<text class="wt5 h5 radius50p bgBlue"></text>
						<view class="h20 mt5 mb5 bdl2f"></view>
						<text class="wt5 h5 radius50p bgred"></text>
					</view>
					<view class="ml10 flex1">
						<view class="h40 lh40 bdb2f flex alcenter">
							<text class="blue pr10">{{ $t('assets.from') }}</text>
							<view :class="[{ animateDown: type == 1, animateOff: type == 0 }]" v-if="accountList1.length >0">
								<picker :value="index1" :range="accountList1" class=" flex1" @change="bindPickerChange1" range-key="name">
									<view class="ft12">{{ accountList1[index1].name }}</view>
								</picker>
							</view>
						</view>
						<view class="h40 lh40 flex alcenter">
							<text class="blue pr10">{{ $t('assets.to') }}</text>
							<view :class="[{ animateUp: type == 1, animateOff: type == 0 }]" v-if="accountList1.length >0">
								<picker :value="index2" :range="accountList1" class=" flex1" @change="bindPickerChange2" range-key="name">
									<view class="ft12">{{ accountList1[index2].name }}</view>
								</picker>
							</view>
						</view>
					</view>
				</view>
				<!-- <view class="plr15 bggray h80 flex alcenter jscenter">
					<view class="wt30 h30 radius50p bggray flex alcenter jscenter" @tap="tapChange"><image src="../../static/image/transfer1.png" class="wt15 h15"></image></view>
				</view> -->
			</view>
		</view>
		<!-- <view class="bgHeader h10"></view> -->
		<view class="mt10 plr15 bgPart pt20" style="min-height: 75.0vh;">
			<view class="">{{ $t('assets.transfernum') }}</view>
			<view class="flex alcenter between bdb1f mt10">
				<input type="number" class="h40 flex1" v-model="number" :placeholder="$t('assets.p_transfernum')" />
				<view class="flex alcenter">
					<text class="blue ft14 pr10 bdr_white50" v-if="coinInfo.currency.code">{{ coinInfo.currency.code || '--' }}</text>
					<view class="pl10" @tap="all">{{ $t('trade.all') }}</view>
				</view>
			</view>
			<view class="mt10 blue ft12" v-if="coinInfo.currency.code">
				{{ $t('trade.use') }}
				<text v-if="accountList1[index1].account_code == 'change_account'">{{ changeBalance ||'0.00'}}</text>
				<text v-if="accountList1[index1].account_code == 'legal_account'">{{ legalBalance ||'0.00'}}</text>
				<text v-if="accountList1[index1].account_code == 'lever_account'">{{ leverBalance ||'0.00'}}</text>
				<text v-if="accountList1[index1].account_code == 'micro_account'">{{ microBalance ||'0.00'}}</text>
				<text v-if="accountList1[index1].account_code == 'financial_account'">{{ financialBalance ||'0.00'}}</text>
				{{ coinInfo.currency.code }}
			</view>
			<view class="mt50 bgBlue radius4 ptb10 white ft14 tc mb10" @tap="transfer">{{ $t('assets.transfer') }}</view>
			<view class="plr0 ptb15 mt10 bgPart" style="">
				<view class="ft16">划转记录</view>
				<view class="mt10 pb100">
					<view class="flex alcenter ptb10 bdb_blue3 ">
						<view class="flex1">{{ $t('trade.num') }}</view>
						<view class="flex1 ">{{ $t('assets.record') }}</view>
						<view class="flex1 tr">{{ $t('trade.time') }}</view>
					</view>
					<view class="mt10 flex bdb_blue3 ptb5" v-for="(item, i) in logList" :key="i" v-if="logList.length > 0">
						<view class="flex1">{{ item.balance - 0 }}</view>
						<view class="flex1 wordbreak pr10">{{ item.from_name+'-'+item.to_name }}</view>
						<view class="flex1 tr">{{ item.created_at }}</view>
					</view>
					<view class="mt20 tc" v-if="logList.length == 0">
						<image src="../../static/image/anonymous.png" class="wt60 h60"></image>
						<view>{{ $t('home.norecord') }}</view>
					</view>
					<view class="tc gray7 ptb20" v-show="!hasMore && logList.length > 0">{{ $t('home.nomore') }}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
import { mapState } from 'vuex';
export default {
	data() {
		return {
			number: '',
			name: '',
			coinInfo: {},
			changeName: [this.$t('assets.legalacc'), this.$t('assets.tradeacc')],
			changeType: ['legal', 'change'],
			type: 0, //法币兑币币
			hasChange: 0,
			animateTab1: '',
			animateTab2: '',
			currencyLegal: {},
			currencyTrade: {},
			balance: '',
			wallet_id: '',
			accountList1: [],
			accountList2: [],
			accountTypeId:'',
			ids:'',
			index1:0,
			index2:1,
			currency_id:'',
			changeBalance:'',
			legalBalance:'',
			leverBalance:'',
			microBalance:'',
			financialBalance:'',
			logList: [],
			page:1,
			isBottom: false,
			hasMore: true,
			account_code:'',
			changeTransfer: 0,
		    legalTransfer: 0,
		    leverTransfer: 0,
			microTransfer: 0,
			financialTransfer: 0
		};
	},
	onLoad(e) {
		this.accountTypeId = e.accountTypeId;
		this.ids = e.id;
		this.account_code = e.account_code;
		uni.setNavigationBarTitle({
			title: this.$t('assets').transfer
		});
		
	},
	onPullDownRefresh() {
		this.getList();
		this.page = 1;
		(this.bottom = false), (this.hasMore = true), this.getLog();
	},
	computed: {
		...mapState(['theme'])
	},
	onShow() {
		this.$utils.setThemeTop(this.theme);
		this.init();
	},
	methods: {
		init() {
			var that = this;
			that.$utils.initDataToken({ url: 'account/detail', data: { account_type_id: that.accountTypeId, id: that.ids } }, res => {
				uni.stopPullDownRefresh();
				that.coinInfo = res;
				that.currency_id = res.currency_id;
				if (res.currency.is_recharge_account && res.currency.account_types.length > 1) {
					var datas = res.currency.account_types;
					that.accountList1 = res.currency.account_types;
					// that.accountList2 = res.currency.account_types;
					datas.forEach(function(item, index) {
						if (item.account_code == 'change_account' && item.is_recharge == 1) {
							that.changeTransfer = 1;
						} else if (item.account_code == 'legal_account' && item.is_recharge == 1) {
							that.legalTransfer = 1;
						} else if (item.account_code == 'lever_account' && item.is_recharge == 1) {
							that.leverTransfer = 1;
						} else if (item.account_code == 'micro_account' && item.is_recharge == 1) {
							that.microTransfer = 1;
						} else if (item.account_code == 'financial_account' && item.is_recharge == 1) {
							that.financialTransfer = 1;
						}
					});
				}
				this.getList();
				this.getLog();
			});
		},
		getList() {
			var that = this;
			that.$utils.initDataToken({ url: 'account/list' }, res => {
				uni.stopPullDownRefresh();
				// 获取法币余额和当前法币信息
				res.forEach(function(item,index){
					if(item.account_code=='change_account'){
						var change_account = item.accounts;
						var selectChange_wallet = change_account.filter(options => options.currency_id == that.currency_id);
						that.changeBalance = selectChange_wallet[0].balance;
					}else if(item.account_code=='legal_account'){
						var legal_account = item.accounts;
						var selectLegal_wallet = legal_account.filter(options => options.currency_id == that.currency_id);
						that.legalBalance = selectLegal_wallet[0].balance;
					}else if(item.account_code=='lever_account'){
						var lever_account = item.accounts;
						var selectLever_wallet = lever_account.filter(options => options.currency_id == that.currency_id);
						that.leverBalance = selectLever_wallet[0].balance;
					}else if(item.account_code=='micro_account'){
						var micro_account = item.accounts;
						var selectLever_wallet = micro_account.filter(options => options.currency_id == that.currency_id);
						that.microBalance = selectLever_wallet[0].balance;
					}else if(item.account_code=='financial_account'){
						var financial_account = item.accounts;
						var selectLever_wallet = financial_account.filter(options => options.currency_id == that.currency_id);
						that.financialBalance = selectLever_wallet[0].balance;
					}
				})
			});
		},
		bindPickerChange1(e){
			var that = this;
			var indexs= e.target.value;
			that.index1 = indexs;
			if(that.changeTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'change_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index2 = index;
							return false;
						}
					})
				}
			}else if(that.legalTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'legal_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index2 = index;
							return false;
						}
					})
				}
			}else if(that.leverTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'lever_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index2 = index;
							return false;
						}
					})
				}
			}else if(that.microTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'micro_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index2 = index;
							return false;
						}
					})
				}
			}else if(that.financialTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index2 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'financial_account'){
							that.index2 = index;
							return false;
						}
					})
				}
			}
		},
		bindPickerChange2(e){
			var that = this;
			var indexs= e.target.value;
			that.index2 = indexs;
			if(that.changeTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'change_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'change_account'){
							that.index1 = index;
							return false;
						}
					})
				}
			}else if(that.legalTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'legal_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'legal_account'){
							that.index1 = index;
							return false;
						}
					})
				}
			}else if(that.leverTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'lever_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'lever_account'){
							that.index1 = index;
							return false;
						}
					})
				}
			}else if(that.microTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'micro_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'micro_account'){
							that.index1 = index;
							return false;
						}
					})
				}
			}else if(that.financialTransfer == 1){
				if(that.accountList1[indexs].account_code == 'change_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'legal_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'lever_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'micro_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code== 'financial_account'){
							that.index1 = index;
							return false;
						}
					})
				}else if(that.accountList1[indexs].account_code == 'financial_account'){
					that.accountList1.forEach(function(item,index){
						if(item.account_code!= 'financial_account'){
							that.index1 = index;
							return false;
						}
					})
				}
			}
		},
		tapChange() {
			console.log(this.hasChange);
			this.type = this.type == 0 ? 1 : 0;
			this.changeType = this.changeType.reverse();
			console.log(this.changeType);
			if (this.type == 0) {
				this.balance = this.currencyLegal.legal_balance;
			} else {
				this.balance = this.currencyTrade.change_balance;
			}
			this.hasChange++;
		},
		all() {
			if(this.accountList1[this.index1].account_code == 'change_account'){
				this.number = this.changeBalance;
			}else if (this.accountList1[this.index1].account_code == 'legal_account'){
				this.number = this.legalBalance;
			}else if (this.accountList1[this.index1].account_code == 'lever_account'){
				this.number = this.leverBalance;
			}else if (this.accountList1[this.index1].account_code == 'micro_account'){
				this.number = this.microBalance;
			}else{
				this.number = this.financialBalance;
			}
		},
		transfer() {
			if (!this.number) {
				return this.$utils.showLayer(this.$t('assets.p_transfernum'));
			}
			this.$utils.initDataToken(
				{
					url: 'account/transfer',
					type: 'POST',
					data: {
						currency_id: this.currency_id,
						balance: this.number,
						from: this.accountList1[this.index1].account_code,
						to: this.accountList1[this.index2].account_code
					}
				},
				(res,msg) => {
					this.number = '';
					this.$utils.showLayer(msg);
					setTimeout(() => {
						this.getList();
						this.page = 1;
		                (this.bottom = false), (this.hasMore = true), this.getLog();
					}, 1500);
				}
			);
		},
		getLog() {
			let that = this;
			this.$utils.initDataToken({ url: 'account/transfer_log', data: { currency_id: this.currency_id, page: this.page,type:this.account_code,limit: 15 } }, res => {
				uni.stopPullDownRefresh();
				let data = res.data;
				that.isBottom = false;
				that.logList = that.page == 1 ? data : that.logList.concat(data);
				that.hasMore = res.last_page == res.current_page ? false : true;
			});
		},
		onReachBottom() {
			if (!this.hasMore) return;
			this.page++;
			this.getLog();
		}
	}
};
</script>

<style>
.animateUp {
	transform: translateY(-80upx);
	transition: all 0.5s;
}
.animateDown {
	transform: translateY(80upx);
	transition: all 0.5s;
}
.animateOff {
	transform: translateY(0);
	transition: all 0.5s;
}
</style>

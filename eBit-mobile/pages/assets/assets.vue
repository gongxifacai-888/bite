<template>
	<view class="">
		<view class="status_bar">
			<view class="top_view" :class="tabBg"></view>
		</view>
		<view class="header fixed bdb1f flex alcenter jscenter blue " :class="tabBg" id="tab-bar" :scroll-left="scrollLeft">
			<text :class="{'white':i==tabIndex}" class="plr10 ft12 bold" v-for="(tab,i) in wallet_data" :key="i" :id="'type'+i"  :data-current="i"  @click="tapTab">{{tab.name}}</text>
		</view>
		<!-- <scroll-view id="tab-bar" class="uni-swiper-tab" scroll-x :scroll-left="scrollLeft">
			<view v-for="(tab,index) in tabBars" :key="tab.id" class="swiper-tab-list" :class="tabIndex==index ? 'active' : ''"
			 :id="tab.id" :data-current="index" @click="tapTab">{{tab.name}}</view>
		</scroll-view> -->
		<view :class="{'light':theme=='light'}">
			<swiper :current="tabIndex" class="swiper-box pt50 myht100 " :duration="300" @change="changeTab">
				<!-- 币币账户 -->
				<swiper-item v-for="(item,index) in wallet_data" :key="index">
					<scroll-view class="list" scroll-y >
						<view :class="['ptb15 plr15','bg'+(index+1)]" >
							<view class="flex alcenter between">
								<view class="">
									<text class="ft14 gray9">{{item.name}}</text>
									<text class="pl10 gray9">{{$t('assets.zhehecny')}}（{{sign}}）</text>
								</view>
								<image src="../../static/image/shows.png" class="wt15 h15" v-if="!isClose" @tap="isClose=true"></image>
								<image src="../../static/image/hide.png" class="wt15 h15" v-else @tap="isClose=false"></image>
							</view>
							<view class="mt15 white ft16 bold">
								{{isClose?closeAccount:item.convert_usd}}
							</view>
							<!-- <view class="pt5 white blue">
								≈{{sign2}} {{isClose?closeAccount:item.convert_cny}}
							</view> -->
						</view>
						<view class="plr10 mt10">
							<view class="bgPart ptb10 plr10 radius4 mb10" v-for="(changeItem,index2) in wallet_data[index].accounts" :key="index2">
								<navigator :url="'tradeAccount?id='+wallet_data[index].id+'&titleName='+wallet_data[index].name+'&type='+wallet_data[index].account_code+'&account_id='+changeItem.id+'&currency_id='+changeItem.currency_id">
									<view class="flex alcenter between">
										<view class="ft16 bold blue2">{{changeItem.currency_code}}</view>
										<image src="../../static/image/arrrowr.png" class="wt15 h15"></image>
									</view>
									<view class="mt10 flex alcenter">
										<view class="flex1">
											<view class="blue">{{$t('trade.use')}}</view>
											<view class="mt5">{{isClose?closeAccount:changeItem.balance}}</view>
										</view>
										<view class="flex1 tc">
											<view class="blue">{{$t('assets.lock')}}</view>
											<view class="mt5">{{isClose?closeAccount:changeItem.lock_balance}}</view>
										</view>
										<!-- <view class="flex1 tr">
											<view class="blue">折合($)</view>
											<view class="mt5">{{isClose?closeAccount:(changeItem.change_balance-0)*(changeItem.usdt_price-0)}}</view>
										</view> -->
										<view class="flex1 tr">
											<view class="blue">{{$t('assets.zhehe')}}({{sign}})</view>
											<view class="mt5">{{isClose?closeAccount:changeItem.convert_usd | filterDecimals(4)}}</view>
										</view>
									</view>	
								</navigator>
							</view>
						</view>
					</scroll-view>
				</swiper-item>
			</swiper>
		</view>
	</view>
</template>
<script>
	import {mapState} from 'vuex'
	export default {
		data() {
			return {
				scrollLeft: 0,
				isClickChange: false,
				tabIndex: 0,
				wallet_data:[],
				isClose:false,
				tabBg:'bg1',
				closeAccount:'****',
				sign:'USD',
				sign2:'$',
			}
		},
		onLoad() {
			
		},
		onPullDownRefresh() {
			this.getAssets();
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
			this.$utils.setThemeTop(this.theme)
			this.$utils.setThemeBottom(this.theme)
			this.getAssets();
		},
		methods: {
			close(index1, index2) {
				uni.showModal({
					content: '是否删除本条信息？',
					success: (res) => {
						if (res.confirm) {
							this.newsitems[index1].data.splice(index2, 1);
						}
					}
				})
			},
			async changeTab(e) {
				let index = e.target.current;
				this.tabBg='bg'+(index+1)
				if (this.isClickChange) {
					this.tabIndex = index;
					this.isClickChange = false;
					return;
				}
				let tabBar = await this.getElSize("tab-bar"),
					tabBarScrollLeft = tabBar.scrollLeft;
				let width = 0;

				for (let i = 0; i < index; i++) {
					// console.log(index,'type'+i);
					let result = await this.getElSize('type'+i);
					width += result.width;
				}
				// console.log('type'+index)
				let winWidth = uni.getSystemInfoSync().windowWidth,
					nowElement = await this.getElSize('type'+index),
					nowWidth = nowElement.width;
				if (width + nowWidth - tabBarScrollLeft > winWidth) {
					this.scrollLeft = width + nowWidth - winWidth;
				}
				if (width < tabBarScrollLeft) {
					this.scrollLeft = width;
				}
				this.isClickChange = false;
				this.tabIndex = index; //一旦访问data就会出问题
			},
			getElSize(id) { //得到元素的size
				return new Promise((res, rej) => {
					uni.createSelectorQuery().select("#" + id).fields({
						size: true,
						scrollOffset: true
					}, (data) => {
						res(data);
					}).exec();
				})
			},
			async tapTab(e) { //点击tab-bar
				let tabIndex = e.target.dataset.current;
				if (this.tabIndex === tabIndex) {
					return false;
				} else {
					let tabBar = await this.getElSize("tab-bar"),
						tabBarScrollLeft = tabBar.scrollLeft; //点击的时候记录并设置scrollLeft
					this.scrollLeft = tabBarScrollLeft;
					this.isClickChange = true;
					this.tabIndex = tabIndex;
				}
				this.tabBg='bg'+(tabIndex+1)
			},
			getAssets() {
				this.$utils.initDataToken({url:'account/list'},res=>{
					console.log(res)
					uni.stopPullDownRefresh()
					// this.sign= res.quote_symbol;
					// this.sign2= res.quote_symbol_2;
					this.wallet_data = res;
				})
			}
		}
	}
</script>

<style>
	.bg1{
		background-color: #0f2441 !important;
	}
	.bg2{
		background-color: #15376f !important;
	}
	.bg3{
		background-color: #37365d !important;
	}
	.bg4{
		background-color: #1d1842 !important;
	}
	.bg5{
		background-color: #004150 !important;
	}
	.light .bg1{
		background-color: #0f2441 !important;
	}
	.light .bg2{
		background-color: #15376f !important;
	}
	.light .bg3{
		background-color: #37365d !important;
	}
	.light .bg4{
		background-color: #1d1842 !important;
	}
	.light .bg5{
		background-color: #004150 !important;
	}
	.myht100{
		/* height: 100.0vh; */
		overflow-y: scroll;
		min-height: 100.0vh !important;
	}
	/* uni-swiper .uni-swiper-wrapper{
		overflow-y: scroll !important; 
	} */
	swiper-item{
		overflow-y: scroll !important; 
	}
</style>

<template>
	<view :class="{ light: theme == 'light' }" class="content " >
		<view class="status_bar"><view class="top_view"></view></view>
		<view class="ptb10 w100 tc flex alcenter between fixed bgPart head pr10">
			<view class="pl10"><image class=" headImg" @click="show" src="/static/image/head.png" mode="widthFix"></image></view>
			<image src="/static/logo.png" style="width: 56upx;height:56upx"></image>
			<view class=" posRelt">
				<view class="flex alcenter" @tap="isshowlang = !isshowlang">
					<text>{{ languages[lang].name }}</text>
					<image :src="languages[lang].img" class="wt15 h12 ml5"></image>
				</view>
				<view class="abstrot bggray plr8 wt90 radius4" style="right: 0;top:60upx" v-show="isshowlang">
					<view class="flex alcenter  ptb10 between bdb1f langs" v-for="(item, i) in languages" :key="i" @tap="changeLang(i)">
						<text>{{ item.name }}</text>
						<image :src="item.img" class="wt15 h12 ml5"></image>
					</view>
				</view>
			</view>
		</view>
		<!--轮播-->
		<swiper
			class="swiper w100 mt50"
			previous-margin="20px"
			next-margin="20px"
			:indicator-dots="true"
			:autoplay="true"
			:interval="3000"
			:circular="true"
			indicator-color="#aaa"
			indicator-active-color="#eee"
		>
			<swiper-item v-for="(item, index) in bannerList" :key="index"><image class="w100 ht100" :src="item.banner"></image></swiper-item>
		</swiper>
		<!--公告哈哈test-->
		<view class="w100 plr10 bgPart">
			<view class=" flex alcenter bdb_blue3">
				<image src="../../static/image/new1.png" class="wt20 h20"></image>
				<swiper class="w100 h40 ml10" :vertical="true" :autoplay="true" :interval="3000" :circular="true">
					<swiper-item class="flex alcenter" v-for="(item, i) in noticeList" :key="i">
						<navigator :url="'/pages/home/newsDetail?id=' + item.id">{{ item.title }}</navigator>
					</swiper-item>
				</swiper>
			</view>
		</view>
		<!--涨幅榜-->
		<swiper display-multiple-items="3" class="ptb10 h100 w100 bgPart" v-if="quoList.length > 2">
			<swiper-item class="flex alcenter around" v-for="(item, i) in quoList" :key="i">
				<navigator
					:url="'/pages/market/kline?legal_id=' + item.quote_currency_id + '&currency_id=' + item.base_currency_id + '&symbol=' + item.base_currency_code + '/' + item.quote_currency_code + '&currency_match_id='+item.id"
					class="flex column alcenter"
				>
					<text class="ft12">{{ item.base_currency_code }}/{{ item.quote_currency_code }}</text>
					<text class="ft18 bold" v-if="item.currency_quotation" :class="{ red2: parseFloat(item.currency_quotation.change) < 0, green: parseFloat(item.currency_quotation.change) >= 0 || item.currency_quotation.change == '' }">{{ item.currency_quotation.close }}</text>
					<text class="ft12" v-if="item.currency_quotation" :class="{ red2: parseFloat(item.currency_quotation.change) < 0, green: parseFloat(item.currency_quotation.change) >= 0 || item.currency_quotation.change == '' }">
						{{ item.currency_quotation.change.substr(0, 1) == '-' ? '' : '+' }}{{ (item.currency_quotation.change - 0) | toFixed2 }}%
					</text>
					<!-- <text class="ft12 blue4" v-if="item.currency_quotation">≈ {{ ((item.currency_quotation.close - 0) * (cny_rate - 0)) | toFixed2 }} CNY</text> -->
				</navigator>
			</swiper-item>
		</swiper>
		<swiper class="ptb10 h100 w100 bgPart" v-else>
			<swiper-item class="flex alcenter around" v-for="(item, i) in quoList" :key="i">
				<navigator
					:url="'/pages/market/kline?legal_id=' + item.quote_currency_id + '&currency_id=' + item.base_currency_id + '&symbol=' + item.base_currency_code + '/' + item.quote_currency_code+'&currency_match_id='+item.id"
					class="flex column alcenter"
				>
					<text class="ft12">{{ item.base_currency_code }}/{{ item.quote_currency_code }}</text>
					<text class="ft18 bold" v-if="item.currency_quotation" :class="{ red2: parseFloat(item.currency_quotation.change) < 0, green: parseFloat(item.currency_quotation.change) >= 0 || item.currency_quotation.change == '' }">{{ item.currency_quotation.close }}</text>
					<text class="ft12" v-if="item.currency_quotation" :class="{ red2: parseFloat(item.currency_quotation.change) < 0, green: parseFloat(item.currency_quotation.change) >= 0 || item.currency_quotation.change == '' }">
						{{ item.currency_quotation.change.substr(0, 1) == '-' ? '' : '+' }}{{ (item.currency_quotation.change - 0) | toFixed2 }}%
					</text>
					<!-- <text class="ft12 blue4" v-if="item.currency_quotation">≈ {{ ((item.currency_quotation.close - 0) * (cny_rate - 0)) | toFixed2 }} CNY</text> -->
				</navigator>
			</swiper-item>
		</swiper>
		<!--模块-->
		<view class="flex alcenter plr10 w100 ptb5">
			<!-- <navigator url="../legal/legal" class="flex flex2 alcenter between bgPart plr10 radius4" style="height: 85px;">
				<view class="flex column ht100 jscenter">
					<text class="ft16 bold">{{ $t('home.legal') }}</text>
					<text class="blue4 ft12 mt5">{{ $t('home.zhichi') }}</text>
				</view>
				<image class="wt50 h40" src="/static/image/legal_card.png"></image>
			</navigator> -->
<!-- 			<navigator url="/pages/market/second" class="flex flex2 alcenter between bgPart plr10 radius4" style="height: 85px;">
				<view class="flex column ht100 jscenter">
					<text class="ft16 bold">{{ $t('micro.micro') }}</text>
					<text class="blue4 ft12 mt5">{{ $t('home.zhichi') }}</text>
				</view>
				<image class="wt50 h40" src="/static/image/legal_card.png"></image>
			</navigator> -->
			<view class="flex column between flex1 ml5">
				<!-- <navigator url="/pages/lever/lever" open-type="switchTab" class="flex alcenter bgPart plr10 radius4 h40 " style="height: 40px;">
					<image class="wt30 h25 mr10" src="/static/footer/gang1.png"></image>
					<text class="ft12">{{ $t('home.lever') }}</text>
				</navigator> -->
				<navigator url="../legal/legal" class="flex alcenter bgPart plr10 radius4 h40 " style="height: 40px;">
					<image class="wt30 h25 mr10" src="/static/footer/gang1.png"></image>
					<text class="ft12">{{ $t('home.legal') }}</text>
				</navigator>
				<navigator url="/pages/home/news" class="flex alcenter bgPart plr10 radius4 h40 mt5" style="height: 40px;">
					<image class="wt30 h30 mr10" src="/static/image/news.png"></image>
					<text class="ft12">{{ $t('home.news') }}</text>
				</navigator>
			</view>
		</view>
		<view class="w100 plr10 pb5 posRelt">
			<navigator url="/pages/deposit/index">
				<view class="abstrot zdx100 white" style="top:15%; left:10%;">
					<view class="ft16 bold">{{ $t('cun.title') }}</view>
					<view class="mt5" style="font-style: italic">{{$t('cun.notice')}}</view>
				</view>
				<image class="w100 h70" src="/static/image/banner.png"></image>
			</navigator>
		</view>
		<!--行情-->
		<view class=" w100 bgPart">
			<view class="ptb10 bdb_blue3 ft14 plr10 bold">{{ $t('home.updowns') }}</view>
			<view class="flex alcenter ft12 ptb5 blue4 plr10">
				<text style="flex: 1.5;">{{ $t('home.name') }}</text>
				<text class="flex1">{{ $t('home.new_price') }}</text>
				<text class="flex1 tr">{{ $t('home.fu') }}</text>
			</view>
			<navigator
				:url="'/pages/market/kline?legal_id=' + item.quote_currency_id + '&currency_id=' + item.base_currency_id + '&symbol=' + item.symbol+'&currency_match_id='+item.id"
				class="flex alcenter ft12 ptb10 blue4 plr10 bdb_blue3"
				v-for="(item, i) in quoList"
				:key="i"
			>
				<view class="" style="flex: 1.5;">
					<view class="">
						<text class="gray_e ft14 bold ft16">{{ item.base_currency_code }}</text>
						/{{ item.quote_currency_code }}
					</view>
					<!-- <view class="ft12">24H量 {{item.volume}}</view> -->
				</view>
				<text class="flex1 bold gray_e ft16" v-if="item.currency_quotation">{{ item.currency_quotation.close }}</text>
				<text class="flex1 tr flex jsend" v-if="item.currency_quotation">
					<text class="radius2 ptb5 white wt70 block tc" :class="{ bgRed: parseFloat(item.currency_quotation.change) < 0, bgGreen: parseFloat(item.currency_quotation.change) >= 0 || item.currency_quotation.change == '' }">
						{{ item.currency_quotation.change.substr(0, 1) == '-' ? '' : '+' }}{{ (item.currency_quotation.change - 0) | toFixed2 }}%
					</text>
				</text>
			</navigator>
		</view>
		<!--左侧弹框-->
		<view class="fixed w100 ht100 mask" @click.stop="hide" @touchmove.stop = "" :class="{ showMask: showLeft }">
			<view class="fixed bgPart w65 ht100 leftBox flex column" :class="{ isShow: showLeft }" @click.stop="stop">
				<view class="bgBlack pt60 pb20 plr20">
					<!-- <view class="tr mb10"><image @click.stop="hide" src="/static/image/arrowlf.png" class="wt30 h30 mr20" mode=""></image></view> -->
					<navigator url="/pages/mine/login" open-type="navigate" class="flex alcenter between" v-if="!token">
						<view>
							<view class="ft20 mb5">{{ $t('home.p_login') }}</view>
							<view class="ft14">{{ $t('home.welcome') }}</view>
						</view>
						<view><image src="/static/image/mores.png" class="wt20 h20"></image></view>
					</navigator>
					<navigator url="/pages/mine/userCenter" class="flex alcenter between" v-if="token">
						<view>
							<view class="ft16 mb5" v-if="info.mobile">{{ info.mobile }}</view>
							<view class="ft16 mb5" v-else>{{ info.email }}</view>
							<view class="ft12">UID:{{ info.uid }}</view>
						</view>
						<view><image src="/static/image/mores.png" class="wt20 h20"></image></view>
					</navigator>
				</view>
				<scroll-view scroll-y="true" show-scrollbar="true" class="flex1 pb30"  style="min-height: 300px;">
					<navigator v-if="token" url="/pages/mine/real_authentication" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/personal.png" class="wt20 h20"></image>
							<text class="ml10">{{ $t('authentication.renzheng') }}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					<navigator v-if="info.is_seller" url="/pages/legal/storeDetail" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/shops.png" class="wt20 h20"></image>
							<text class="ml10">{{ $t('home.myshop') }}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					<navigator v-if="token" :url="'/pages/mine/invite?code=' + info.invite_code" class="flex alcenter ptb15 between plr20 ft14 bdb27">
						<view class="flex alcenter">
							<image src="/static/image/share.png" class="wt20 h20"></image>
							<text class="ml10">{{ $t('home.myshare') }}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					<view  class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/sucerty.png" class="wt20 h20"></image>
							<text class="ml10">{{$t('about.theme')}}</text>
						</view>
						<switch @change="switchChange" :checked="theme=='light' ?  false:true "/>
					</view>
					<navigator url="/pages/mine/authorization_code" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/sucerty.png" class="wt20 h20"></image>
							<text class="ml10">{{$t('bind.codeauth')}}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					<navigator url="/pages/mine/security" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/sucerty.png" class="wt20 h20"></image>
							<text class="ml10">{{ $t('login.security') }}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					<navigator url="/pages/assets/bindMentionAddress" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/address.png" class="wt20 h20"></image>
							<text class="ml10">{{ $t('bind.bindAddr') }}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					<navigator url="/pages/mine/collect" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/receivables.png" class="wt20 h20"></image>
							<text class="ml10">{{ $t('collect.method') }}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					<!-- <navigator url="/pages/mine/workOrder" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/account_about_image.png" class="wt20 h20"></image>
							<text class="ml10">用户反馈</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator> -->
					<navigator url="/pages/mine/about" class="flex alcenter ptb15 between plr20 ft14 bdb_blue3">
						<view class="flex alcenter">
							<image src="/static/image/account_about_image.png" class="wt20 h20"></image>
							<text class="ml10">{{ $t('about.abt') }}</text>
						</view>
						<image src="/static/image/mores.png" class="wt15 h15"></image>
					</navigator>
					
					<view class="mt30 plr20" v-if="token">
						<button type="primary" size="default" class="ft14" @click="logout">{{ $t('home.logout') }}</button>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
import { mapState } from 'vuex';
export default {
	data() {
		return {
			bannerList: [],
			noticeList: [],
			quoList: [],
			title: 'Hello',
			showLeft: false,
			showMask: false,
			token: '',
			info: '',
			cny_rate: '',
			languages: {
				zh: { name: '中文', img: '/static/image/zh.png' },
				en: { name: 'English', img: '/static/image/en.png' },
				hk: { name: '繁體中文', img: '/static/image/hk.png' },
				jp: { name: '日本語', img: '/static/image/jp.png' }
			},
			lang: '',
			isshowlang: false
		};
	},
	filters: {
		toFixed2: function(value, options) {
			value = Number(value);
			return value.toFixed(2);
		}
	},
	computed: {
		...mapState(['theme'])
	},
	onLoad() {
		var token = uni.getStorageSync('token');
		this.lang = uni.getStorageSync('lang') || 'zh';
		this.changeFooter();
		if (token) {
			this.getUserInfo();
		}
		
	},
	onShow() {
		this.$utils.setThemeTop(this.theme);
		this.$utils.setThemeBottom(this.theme);
		this.showLeft = false;
		this.showMask = false;
		this.token = uni.getStorageSync('token');
		this.getBannerImg();
		this.getNoticeList();
		this.quotation();
		this.$socket.listenFun([{ type: "sub", params: "DAY_MARKET" }], res => {
			var msg = JSON.parse(res);
			for (var i = 0; i < this.quoList.length; i++) {
				if (this.quoList[i].id == msg.currency_match_id) {
					this.quoList[i].currency_quotation.close = msg.quotation.close;
					this.quoList[i].currency_quotation.change = msg.quotation.change;
				}
			}
		});
	},
	methods: {
		changeFooter() {
			uni.setTabBarItem({
				index: 0,
				text: this.$t('market.home')
			});
			uni.setTabBarItem({
				index: 1,
				text: this.$t('market.market')
			});
			uni.setTabBarItem({
				index: 2,
				text: this.$t('trade.bibi')
			});
			uni.setTabBarItem({
				index: 3,
				text: this.$t('assets.lever')
			});
			uni.setTabBarItem({
				index: 4,
				text: this.$t('assets.assets')
			});
		},
		// 切换主题
		switchChange(e){
			var ui  = (this.theme == 'light') ? 'dark' : 'light';
			this.$store.dispatch("changeTheme",ui);
			this.$utils.setThemeTop(this.theme);
			this.$utils.setThemeBottom(this.theme);
		},
		// 语言切换
		changeLang(lang) {
			// this.$utils.initData({ url: 'lang/set', data: { lang }, type: 'POST' }, res => {
				console.log(lang);
				this.lang = lang;
				uni.setStorageSync('lang', lang);
				this.$i18n.locale = lang;
				this.isshowlang = false;
				this.changeFooter();
				this.showLeft = false;
				this.showMask = false;
				this.token = uni.getStorageSync('token');
				this.getBannerImg();
				this.getNoticeList();
			// });
		},
		//轮播图
		getBannerImg() {
			uni.showLoading();
			this.$utils.initData({ url: 'news/list', data: { category_id: 27 }}, (res, msg) => {
				uni.stopPullDownRefresh();
				this.bannerList = res.data;
				uni.hideLoading();
			});
		},
		//公告
		getNoticeList() {
			this.$utils.initData({ url: 'news/list', data: { category_id: 22 }}, (res, msg) => {
				uni.stopPullDownRefresh();
				this.noticeList = res.data;
			});
		},
		//行情交易对
		quotation() {
			this.$utils.initData({ url: 'market/currency_matches'}, (res, msg) => {
				uni.stopPullDownRefresh();
				let usdtData = res.find(item => item.code == 'USDT');
				this.cny_rate = usdtData.cny_price;
				let matches = usdtData.matches.filter((item)=>{
					return item.open_change==1;
				})
				this.quoList = matches.sort(function(a, b) {
					return b.currency_quotation.change - a.currency_quotation.change;
				});
			});
		},
		show() {
			if (this.token) {
				this.getUserInfo();
			}
			this.showLeft = !this.showLeft;
		},
		hide() {
			this.showLeft = !this.showLeft;
		},
		stop() {},
		//获取用户信息
		getUserInfo() {
			var that = this;
			this.$utils.initDataToken({ url: 'user/info', data: {}, type: 'get' }, (res, msg) => {
				uni.stopPullDownRefresh();
				uni.setStorageSync('uid', msg.id);
				that.info = res;
			});
		},
		//退出登录
		logout() {
			this.$utils.initDataToken({ url: 'user/logout', data: {}, type: 'post' }, (res, msg) => {
				uni.clearStorageSync();
				this.$utils.showLayer(msg);
				uni.reLaunch({
					url: '/pages/mine/login'
				})
			});
		}
	},
	onHide() {
		this.$socket.closeSocket();
	},
	onPullDownRefresh() {
		this.getBannerImg();
		this.getNoticeList();
		this.quotation();
	}
};
</script>

<style>
.head {
	top: var(--status-bar-height);
	left: 0;
	z-index: 999;
}
.content {
	/* display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center; */
	/* padding-bottom: 20px; */
}
.headImg {
	width: 50upx;
	height: 50upx;
	/* left: 10px; */
}
.swiper {
	height: 300upx;
}
.bgIndex {
	background: #0a1b2b;
}
.bgParts {
	background: #061623;
}
.leftBox {
	left: -100%;
	top: 0;
	z-index: 9999;
	transition: all 0.5s ease-out;
	opacity: 0.5;
}
.isShow {
	left: 0;
	top: 0;
	z-index: 9999;
	transition: all 0.5s ease-out;
	opacity: 1;
}
.mask {
	top: 0;
	left: -100%;
	background: rgba(0, 0, 0, 0.4);
	z-index: 999;
}
.showMask {
	left: 0;
	top: 0;
	z-index: 999;
	opacity: 1;
}
.langs:last-child {
	border-bottom: none;
}
</style>

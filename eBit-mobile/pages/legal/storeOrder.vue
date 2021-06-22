<template>
	<view class="" :class="{'light':theme=='light'}">
		<view class="status_bar"><view class="top_view"></view></view>
		<view class="header fixed flex alcenter between plr15 bg_legal">
			<view @click="back()" >
				<image src="../../static/image/forward.png" mode="aspectFit" class="wt20 h20" v-if="theme=='light'"></image>
				<image src="../../static/image/arrows.png" mode="aspectFit" class="wt20 h20" v-else></image>
			</view>
			<view>
				<image src="../../static/image/screen1.png" mode="aspectFit" class="wt20 h20" @click="isshow()"></image>
			</view>
		</view>
		<view class="pt50">
			<view class="h50">
				<text class=" ft24 plr15 bold">{{$t('store.orderlist')}}</text>
			</view>
			<view>
				<view class="gray75 flex column w100 plr15 ptb15 bdb27" v-for="(item,index) in orderlist" :key="index" @click="orderDetail(item.id)">
					<view class="flex between">
						<view class=" flex alcenter ft13 bold">
							<text :class="['blue21 mr5 ft14',{'redColor':item.way=='SELL'}]">{{item.way=='SELL' ? $t('legal.buy'):$t('legal.sell')}}</text>
							<text>{{item.currency_name}}</text>
						</view>
						<view class="flex alcenter">
							<!-- 0 交易中 1已付款 2延期确认 3维权 4取消 5确认 -->
							<text class="" v-if="item.status==0">{{$t('store.notdone')}}</text>
							<text class="" v-if="item.status==1">{{$t('trade.has_pay')}}</text>
							<text class="" v-if="item.status==2">延期确认</text>
							<text class="" v-if="item.status==3">{{$t('store.protection')}}</text>
							<text class="" v-if="item.status==4">{{$t('trade.has_cancel')}}</text>
							<text class="" v-if="item.status==5">{{$t('store.done')}}</text>
							<image src="../../static/image/mores.png" mode="aspectFit" class="wt15 h15"></image>
						</view>
					</view>
					<view class="flex between mt10">
						<view class="flex column flexstart">
							<text>{{$t('trade.time')}}</text>
							<text class="b7c ft13 mt5">{{item.created_at}}</text>
						</view>
						<view class="flex column flexstart">
							<text>{{$t('trade.num')}}({{item.currency_name}})</text>
							<text class="b7c ft13 mt5">{{item.number}}</text>
						</view>
						<view class="flex column flexend">
							<text v-if="item.area_info">{{$t('legal.allmoney')}}({{item.area_info.currency}})</text>
							<text class="b7c ft13 mt5">{{item.amount}}</text>
						</view>
					</view>
					<view class="flex mt10">
						<!-- <text class="ft14 ">{{item.user_cash_info.real_name}}</text> -->
					</view>
				</view>
				<view :class="['tc pt30 pt100 pb100 hidden',{'block':orderlist.length==0&&over}]">
					<image src="/static/image/nodata.png" class="h50 wt50"></image>
					<view class="gray7">{{$t('home.norecord')}}</view>
				</view>
				<view class="tc gray7 ptb20" v-show="!hasMore && orderlist.length>10">{{$t('home.nomore')}}</view>
			</view>
		</view>
		<view class="sx_show" v-show="show" @click="isshow()">
		<!-- <view class="sx_show" @click="isshow()"> -->
			<view class="bgPart" @click.stop>
				<view class="h50 bdb1f" >
					<text class=" ft24 plr15 bold">{{$t('store.orderlist')}}</text>
				</view>
				<view class="mt15 plr10 ft10 ">
					<text class="gray75 ft14">{{$t('store.orderstatus')}}</text>
					<view class="select deal_statu mt10 mb10 flex wraps ">
						<!-- 0 交易中 1已付款 2延期确认 3维权 4取消 5确认 -->
						<view :class="status===0?'active':'baseBg'" @click="changestatu(0)">{{$t('store.notdone')}}</view>
						<view :class="status===1?'active':'baseBg'" @click="changestatu(1)">{{$t('trade.has_pay')}}</view>
						<view :class="status===2?'active':'baseBg'" @click="changestatu(2)">延期确认</view>
						<view :class="status===3?'active':'baseBg'" @click="changestatu(3)">{{$t('store.protection')}}</view>
						<view :class="status===4?'active':'baseBg'" @click="changestatu(4)">{{$t('trade.has_cancel')}}</view>
						<view :class="status===5?'active':'baseBg'" @click="changestatu(5)">{{$t('store.done')}}</view>
					</view>
				</view>
				<view class="flex alcenter bdt2f">
					<view class="flex1 tc ptb10 reset  gray75" @click="reset()">{{$t('store.chongzhi')}}</view>
					<view class="active flex1 tc ptb10 bgBlue2 white confirm" @click="confirm">{{$t('login.e_confrim')}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default {
		data() {
			return {
			   id:'',
	           show: false,
			   status:-1,
			   orderlist:[],
			   page: 1,
			   hasMore: true,
			   over: false,
			};
		},
		onLoad(e) {
			this.id = e.id;
			this.getData();
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
			this.$utils.setThemeTop(this.theme)
		},
		methods: {
			getData() {
				let that = this;
				// let status = (!this.status&&this.status!=0) ? '-1' :this.status;
				that.$utils.initDataToken({url: 'otc/master_details',
					data: {
						page:that.page,
						master_id: that.id,
						status:that.status
					}
				}, res => {
					var data = res.data;
					uni.stopPullDownRefresh();
					that.orderlist = (that.page == 1) ? data : that.orderlist.concat(data);
					that.over = true;
					that.hasMore = (res.page >= res.pages) ? false : true;
					console.log(that.hasMore)
				});
			},
			back(){
				uni.navigateBack();
			},
			detail(){
				uni.navigateTo({
				    url: '/pages/legal/finished'
				});
			},
			isshow(){
				this.show = !this.show;
			},
			changestatu(status){
				this.status= (this.status===status) ? (this.status = -1) : (this.status = status);
			},
			reset(){
				this.status = '';
				// this.type = '';
				this.page = 1;
				this.hasMore = true,
				this.over = false,
				this.show = !this.show;
				this.getData();
			},
			confirm(){	
				this.show = false;
				this.page = 1;
				this.hasMore = true,
				this.over = false,
				this.getData();
			},
			orderDetail(id){
				uni.navigateTo({
				    url: '/pages/legal/orderDetail?id='+id,
					"animationType": "pop-in",
					"animationDuration": 300
				});
			},
			onPullDownRefresh() {
				this.page = 1;
				this.getData();
			},
			onReachBottom() {
				if (!this.hasMore) return;
				this.page++;
				this.getData();
			},
		}
	};
</script>

<style>
	page {
		background-color: #131f30;
	}
	 .sx_show{
		  position: fixed;
		  top: calc(var(--status-bar-height) + 100upx);
		  width: 100%;
		  left: 0;
		  right: 0;
		  bottom: 0;
		  background-color: rgba(0, 0, 0, .7);
	  }
	  .sx_box{
		background-color: #131f30;
	  }
	   .select view{
			width: 30%;
			height: 60upx;
			margin-bottom: 20upx;
			text-align: center;
			margin: 10upx 10upx 20upx;
			/* color: #d7e8f5; */
			/* background: #0b1726; */
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.select .active{
			background: none;
			border: 1px solid #217dc1;
			color: #217dc1;
			border-radius: 4upx;
		}
</style>

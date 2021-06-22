<template>
	<view class="" :class="{'light':theme=='light'}">
		<view class="flex flexend between plr15 ptb10 w100 bdb_1e fixed lf0 bgPart zdx100">
			<view class="flex flexend">
				<view :class="['ft14 gray7',{'ft18 white':current==0}]" @click="change(0)">{{$t('trade.all_delegate')}}</view>
			    <view :class="['pl10 ft14 gray7',{'ft18 white':current==1}]" @click="change(1)">{{$t('trade.his_delegate')}}</view>
			</view>
			<view class="flex alcenter ml10 ft14" v-show="current==0">
				<text class="gray7" @click="togglePopup('bottom-entrust')" data-position="bottom">{{enType=='in' ? $t('trade.buy') : $t('trade.sell')}}</text>
				<image src="/static/image/pull.png" class="h10 wt10 ml5"></image>
			</view>
		</view>
		<uni-popup :show="popType === 'bottom-entrust'" position="bottom" @hidePopup="togglePopup('')">
			<view class="bottom-content">
				<view class="bottom-content-box tc gray7">
				    <view :class="['bdb_1e ptb10 ft14 bgPart',{'blueColor':enType=='in'}]" @click="changeEntrustType('in')">{{$t('trade.buy')}}</view>
					<view :class="['ptb10 ft14 bgPart',{'blueColor':enType=='out'}]" @click="changeEntrustType('out')">{{$t('trade.sell')}}</view>
		            <view class="pt5 pb10">
		            	<view class="ptb10 ft14 bgPart" @click="togglePopup('')">{{$t('trade.cancel')}}</view>
		            </view>
				</view>
			</view>
		</uni-popup>
		<view class="en-ul pl15 pt50" v-show="current==0">
			<view class="ptb10 bdb_1e pr15" v-for="(item,index) in enList" :key="item.id">
				<view class="flex between alcenter">
					<view class="flex flex flexend">
						<text :class="['ft14 redColor',{'greenColor':enType=='in'}]">{{enType=='in'? $t('trade.buy') : $t('trade.sell')}}</text>
						<text class="gray_e pl5 ft14" v-if="item.currency_match">{{item.currency_match.base_currency_code}}/{{item.currency_match.quote_currency_code}}</text>
						<text class="gray4 pl5 ft12">{{item.created_at.substring(10,16)}} {{item.created_at.substring(5,7)}}/{{item.created_at.substring(8,11)}}</text>
					</view>
					<view class="blueColor bgBlack radius2 plr20 ptb5" @click="revoke(index,item.id)">{{$t('trade.back')}}</view>
				</view>
				<view class="mt15 flex">
					<view class="flex1">
						<text class="gray4" v-if="item.currency_match">{{$t('trade.price')}}({{item.currency_match.quote_currency_code}})</text>
						<view class="mt5">{{item.price}}</view>
					</view>
					<view class="flex1 tc">
						<text class="gray4" v-if="item.currency_match">{{$t('trade.num')}}({{item.currency_match.base_currency_code}})</text>
						<view class="mt5">{{item.number}}</view>
					</view>
					<view class="flex1 tr">
						<text class="gray4" v-if="item.currency_match">{{$t('trade.real_num')}}({{item.currency_match.base_currency_code}})</text>
						<view class="mt5">{{item.transacted_amount}}</view>
					</view>
				</view>
			</view>
			<view class="tc ptb30" v-if='enList.length==0'>
				<image src="/static/image/nodata.png" class="h50 wt50"></image>
				<view class="gray7">{{$t('home.norecord')}}</view>
			</view>
			<view class="tc gray7 ptb20" v-show="isBottom">{{$t('home.loading')}}...</view>
			<view class="tc gray7 ptb20" v-show="!hasMore && enList.length>10">{{$t('home.nomore')}}</view>
		</view>
		<view class="en-ul pl15 pt50" v-show="current==1">
			<view class="ptb10 bdb_1e pr15" v-for="item in hisList" :key="item.id">
				<view class="flex between alcenter">
					<view class="flex flex flexend">
						<!-- <text :class="['ft14 redColor',{'greenColor':item.way==1}]" v-if="item.from_user_id==uid">{{item.way==1?'买入':'卖出'}}</text> -->
						<text :class="['ft14 redColor',{'greenColor':item.way==1}]">{{item.way_name}}</text>
						<text class="white pl5 ft14">{{item.currency_match.base_currency_code}}/{{item.currency_match.quote_currency_code}}</text>
						<text class="pl5 gray7">{{item.created_at.substring(10,16)}} {{item.created_at.substring(5,7)}}/{{item.created_at.substring(8,11)}}</text>
					</view>
					<view class="gray7 ptb5">{{item.transacted_amount==0?$t('trade.revoked'):$t('trade.successed')}}</view>
				</view>
				<view class="mt15 flex">
					<view class="flex1">
						<text class="gray4">{{$t('trade.fee')}}</text>
						<view class="mt5">{{item.fee}}</view>
					</view>
					<view class="flex1 tc">
						<text class="gray4">{{$t('trade.delegate_price')}}({{item.currency_match.quote_currency_code}})</text>
						<view class="mt5">{{item.price}}</view>
					</view>
					<view class="flex1 tr">
						<text class="gray4">{{$t('trade.w_num')}}({{item.currency_match.base_currency_code}})</text>
						<view class="mt5">{{item.number}}</view>
					</view>
				</view>
				<view class="mt10 flex">
					<view class="flex1">
						<text class="gray4">{{$t('trade.cj_total')}}({{item.currency_match.quote_currency_code}})</text>
						<view class="mt5">{{item.transacted_volume}}</view>
					</view>
					<view class="flex1 tc">
						<text class="gray4">{{$t('trade.cj_price')}}({{item.currency_match.quote_currency_code}})</text>
						<view class="mt5">{{item.avg_price}}</view>
					</view>
					<view class="flex1 tr">
						<text class="gray4">{{$t('trade.cj_vol')}}({{item.currency_match.base_currency_code}})</text>
						<view class="mt5">{{item.transacted_amount}}</view>
					</view>
				</view>
			</view>
			<view class="tc ptb30" v-if='hisList.length==0'>
				<image src="/static/image/nodata.png" class="h50 wt50"></image>
				<view class="gray7">{{$t('home.norecord')}}</view>
			</view>
			<view class="tc gray7 ptb20" v-show="isBottom">{{$t('home.loading')}}...</view>
			<view class="tc gray7 ptb20" v-show="!hasMore && hisList.length>10">{{$t('home.nomore')}}</view>
		</view>
	</view>
</template>

<script>
	import uniPopup from '@/components/uni-popup2.vue';
	import {mapState} from 'vuex'
    export default {
		components: {
			uniPopup
		},
		data() {
			return {
				uid:'',
				current:0,//全部/历史
				popType:'',//底部弹窗
				enType:'in',//全部委托类型
				enList:[],//当前委托
				hisList:[],//历史委托
				page:1,
				isBottom:false,
				hasMore:true,
			}
		},
		filters:{
			toFixed2: function (value, options) {
				value = Number(value);
				return value.toFixed(2);
			},  
		},
		computed:{
		   ...mapState(['theme']),
		},
		onLoad() {
			
		},
		onShow(){
			this.uid = uni.getStorageSync('uid');
			this.$utils.setThemeTop(this.theme);
			this.entrustList();
		},
		methods: {
			change(current){
				if(this.current==current){
					return;
				}
				this.current = current;
				this.page = 1;
				current==0 ? this.entrustList() : this.hisenList();
			},
			togglePopup(popType){//弹窗
				this.popType = popType;
			},
			changeEntrustType(type){//委托类型切换
			    this.enType = type;
				this.togglePopup('');
				this.page=1;
				this.isBottom=false,
				this.hasMore=true,
				this.enList=[];
				this.entrustList();
			},
			entrustList(){//委托
				let that = this;
				let url;
				that.enType == 'out' ? url='tx_out/list' : url='tx_in/list'
				that.$utils.initDataToken({ url: url,data:{page:that.page}}, res => {
					var data = res.data;
					uni.stopPullDownRefresh();
					that.isBottom=false;
					that.enList = (that.page == 1) ? data : that.enList.concat(data);
					that.hasMore = (res.last_page == res.current_page) ? false : true;
					console.log(that.hasMore)
				});
			},
			hisenList(){//历史委托
				let that = this;
				that.$utils.initDataToken({ url: 'tx_history/list',data:{page:that.page}}, res => {
					var data = res.data;
					uni.stopPullDownRefresh();
					that.isBottom=false;
					that.hisList = (that.page == 1) ? data : that.hisList.concat(data);
					that.hasMore = (res.last_page == res.current_page) ? false : true;
					console.log(that.hasMore)
				});
			},
			onPullDownRefresh(){
				this.page=1;
				this.isBottom=false,
				this.hasMore=true,
				this.currentrent==0 ? this.entrustList() : this.hisenList();
			},
			onReachBottom() {
				if(!this.hasMore) return;
				this.page++;
				this.currentrent==0 ? this.entrustList() : this.hisenList();
			},
			revoke(index,id){//撤单
				let that = this;
				let url = that.enType == 'out' ? 'tx_out/cancel' : 'tx_in/cancel';
				that.$utils.initDataToken({url: url,data:{id:id}}, (res,msg)=> {
					that.$utils.showLayer(msg);
					that.enList.splice(index, 1);//动态删掉这个订单
				});
			}
		},
	}
</script>

<style>
	page{
		background: #102030;
	}
</style>

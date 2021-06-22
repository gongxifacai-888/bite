<template>
	<view :class="{ light: theme == 'light' }">      
		<view class="bgPart">
			<view class="ptb10 bdb_1e plr15" v-for="(item,index) in logList" :key="item.id">
				<view class="flex between alcenter">
					<view class="flex flex flexend">
						<text>{{item.storage_currency.name}}</text>
						<text class="gray9 pl10">{{item.created_at.substring(10,16)}} {{item.created_at.substring(5,7)}}/{{item.created_at.substring(8,11)}}</text>
					</view>
					<view :class="['gray',{'red':item.status==1}]">
						{{item.status==1?$t('cun.ing'):$t('cun.loss')}}
					</view>
				</view>
				<view class="mt15 flex">
					<view class="flex1">
						<text class="gray4">{{$t('cun.num')}}</text>
						<view class="mt5">{{item.storage_currency.limit_number}}</view>
					</view>
					<view class="flex1 tc">
						<text class="gray4">{{$t('cun.leiji')}}</text>
						<view class="mt5">{{item.pile_income}}</view>
					</view>
					<view class="flex1 tr">
						<text class="gray4">{{$t('cun.lastday')}}({{$t('cun.day')}})</text>
						<view class="mt5">{{item.surplus_days}}</view>
						<!-- <view class="mt5">{{item.end_time.substring(10,16)}} {{item.end_time.substring(5,7)}}/{{item.end_time.substring(8,11)}}</view> -->
					</view>
				</view>
			</view>
			<view class="tc pt30 pb100" v-if='logList.length==0'>
				<image src="/static/image/nodata.png" class="h50 wt50"></image>
				<view class="gray7">{{$t('home.norecord')}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				index:0,
				logList:[],
				page:1,
				isBottom:false,
				hasMore:true,
				type:1
			}
		},
		onLoad(e){
			uni.setNavigationBarTitle({//改变标题
			   title:this.$t('cun.log')
			})
			this.getLog();
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
	        this.$utils.setThemeTop(this.theme)
		},
		methods:{
			getLog(){
				let that = this;
				this.$utils.initDataToken({url:'storage_currency/history',data:{page:that.page}},(res)=>{
					let data = res.data;
					uni.stopPullDownRefresh();
					that.logList = (that.page == 1) ? data : that.logList.concat(data);
					that.hasMore = (res.last_page == res.current_page) ? false : true;
				})
			},
			onPullDownRefresh(){
				this.page=1;
				this.bottom=false,
				this.hasMore=true,
				this.getLog();
			},
			onReachBottom() {
				if(!this.hasMore) return;
				this.page++;
				this.getLog();
			},
		}
	}
</script>

<style>

</style>

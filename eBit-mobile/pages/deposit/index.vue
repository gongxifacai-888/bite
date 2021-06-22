<template>
	<view class="plr10 ptb10" :class="{'light':theme=='light'}">
		<view class="ptb10 plr10 ft16 flex alcenter between">
			<text>{{$t('cun.ding')}}</text>
			<navigator class="ft12" url="/pages/deposit/coinlog">{{$t('cun.log')}} <uni-icons type="arrowright"></uni-icons></navigator>
		</view>
		<view class="radius6 bgPart mb10" v-for="(item,index) in list" :key="index">
			<view class="ptb10 bdb27 plr10  flex alcenter between">
				<view class="flex alcenter between">
					<image src="../../static/image/money.png" mode="" class="wt40 h40"></image>
					<text class="ft16 pl20">{{item.name}}</text>
				</view>
				<view class="bgBlue white radius20 ptb5 plr20" @click="buy(item.id)">{{$t('cun.buy')}}</view>
			</view>
			<view class=" ptb20 plr20">
				<view class="">
					<view class="ft16">{{$t('cun.circle')}} {{item.limit_days}}{{$t('cun.day')}}</view>
				</view>
				<view class="flex alcenter between ft14 mt15">
					<text>{{$t('cun.jin')}} {{item.display_rate}}</text>
				    <text>{{$t('cun.num')}} {{item.limit_number}}</text>
				</view>
			</view>
		</view>
<!-- 		<view class="ptb10 plr10 ft16 mt20">
			活期存储
		</view> -->
		
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				list:[]
			}
		},
		onLoad() {
			uni.setNavigationBarTitle({
				title:this.$t('cun').title
			})
			this.getList();
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
		    this.$utils.setThemeTop(this.theme)
		},
		methods: {
			getList() {
				var that = this;
				this.$utils.initDataToken({url:'storage_currency/lock_list',data:{limit:30}},(res,msg)=>{
					that.list = res.data;
				})
				this.$utils.initDataToken({url:'storage_currency/current_setting'},(res,msg)=>{
				
				})
			},
			buy(id){
				uni.showModal({
				    title: '',
				    content: this.$t('cun.ifbuy'),
				    success: (res)=> {
				        if (res.confirm) {
				           this.$utils.initDataToken({url:'storage_currency/submit_lock',type:'post',data:{id:id}},(res,msg)=>{
				               this.$utils.showLayer(msg);
				           })
				        } else if (res.cancel) {
				            // console.log('用户点击取消');
				        }
				    }
				});
				
			}
		},
		onReachBottom() {
			
		}
	}
</script>

<style>
</style>

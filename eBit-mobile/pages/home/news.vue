<template>
	<view class="plr10 ptb10" :class="{'light':theme=='light'}">
		<navigator :url="'/pages/home/newsDetail?id='+item.id" v-for="(item,i) in list" :key="i" class=" plr8 bdb_blue3 ptb20">
			<view class="ft14 color1 ellipsis">{{item.title}}</view>
			<!-- <view class="ft12 blue4 tr">2012-02-30</view> -->
		</navigator>
		<view v-if="show" class="tc mtb20 blue4 ft12">{{more}}</view>
		<!-- <uni-load-more :loadingType="status"></uni-load-more> -->
	</view>
</template>

<script>
	import uniLoadMore from "../../components/uni-load-more.vue"
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				list:[],
				page:1,
				more:this.$t('home').up,
				show:true
			}
		},
		components: {
			uniLoadMore
		},
		onLoad() {
			uni.setNavigationBarTitle({
				title:this.$t('home').news
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
				uni.showLoading();
				this.$utils.initData({url:'news/list',data:{page:this.page,category_id: 24}},(res,msg)=>{
					uni.hideLoading();
					// if(that.page == 1){
					// 	if(res.current_page==res.last_page){
					// 		that.show = false;
					// 	}
					// }
					that.list = that.list.concat(res.data);
					if(res.current_page!=res.last_page){
						that.more = that.$t('home').up;
						that.show = true;
					}else{
						that.more = that.$t('home').nomore
					}
				})
			}
		},
		onReachBottom() {
			this.status = 'loading'
			this.page ++;
			this.getList();
		}
	}
</script>

<style>
</style>

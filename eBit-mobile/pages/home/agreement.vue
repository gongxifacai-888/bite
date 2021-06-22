<template>
	<view class="plr10 ptb10" :class="{'light':theme=='light'}">
		<view class="tc mb10 ft16 pt10">{{title}}</view>
		<view v-html="news"></view>
		<view class="mt30 pr15 ft12 tr blue4">{{time}}</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				news:'',
				time:'',
				title:''
			}
		},
		onLoad(option) {
			uni.setNavigationBarTitle({
				title:this.$t('home').detail
			})
			this.getid()
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
		    this.$utils.setThemeTop(this.theme)
		},
		methods:{
			getid(){
				this.$utils.initData({url:'news/list',data:{category_id:26}},(res,msg)=>{
					 console.log(res)
					 var id = res.data[0].id;
					 this.getDetail(id)
				})
			},
			getDetail(id){
				this.$utils.initData({url:'news/info',data:{id:id}},(res,msg)=>{
					this.news = res.content;
					this.time = res.update_time;
					this.title = res.title;
				})
			}
		}
	}
</script>

<style>
</style>

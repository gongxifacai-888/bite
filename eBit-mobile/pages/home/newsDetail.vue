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
			this.getDetail(option.id);
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
		    this.$utils.setThemeTop(this.theme)
		},
		methods:{
			getDetail(id){
				this.$utils.initData({url:'news/info',data:{id:id}},(res,msg)=>{
					// this.news = res.content;
					this.news = res.content.replace(/\<img/gi, '<img style="max-width:100%;height:auto" ')
					this.time = res.updated_at;
					this.title = res.title;
				})
			}
		}
	}
</script>

<style>
</style>

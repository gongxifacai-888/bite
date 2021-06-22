<template>
	<view :class="{'light light_white':theme=='light'}">
		<view class="ptb20 plr10 bdb_blue3 flex alcenter between">
			<text>{{$t('about.concat')}}</text>
			<text>{{email}}</text>
		</view>
		<navigator url="/pages/mine/policy" class="ptb20 plr10 bdb_blue3 flex alcenter between">
			<text>{{$t('about.privat')}}</text> 
			<view>
				<image src="/static/image/mores.png" class="wt20 h20"></image>
			</view>
		</navigator>
		<navigator url="/pages/mine/workOrder" class="ptb20 plr10 bdb_blue3 flex alcenter between">
			<text>{{$t('feed.feed')}}</text> 
			<view>
				<image src="/static/image/mores.png" class="wt20 h20"></image>
			</view>
		</navigator>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				mobile:'',
				email:''
			}
		},
		onLoad() {
			this.getConnect();
			this.getConnect2();
			uni.setNavigationBarTitle({
				title:this.$t('about').abt
			})
		},
		methods:{
			getConnect(){
				var that = this;
				that.$utils.initDataToken({url:'default/setting',type:'GET',data:{key:'contact_mobile'}},(res,msg)=>{
					that.mobile = res;
				})
			},
			getConnect2(){
				var that = this;
				that.$utils.initDataToken({url:'default/setting',type:'GET',data:{key:'contact_email'}},(res,msg)=>{
					that.email = res;
				})
			},
		},
		onShow() {
		   this.$utils.setThemeTop(this.theme)
		},
		computed:{
		   ...mapState(['theme']),
		},
	}
</script>

<style>
</style>

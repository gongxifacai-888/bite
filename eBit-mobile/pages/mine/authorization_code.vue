<template>
	<view :class="{'light':theme=='light'}">
		<view class="bgHeader mt20 gray7 ptb15 ft12 plr15 mlr10 radius4">
			{{$t('bind.codetip')}}
		</view>
		<view class="ptb20 plr10  flex alcenter jscenter mt20">
			<text>{{$t('bind.codeauth')}}：</text>
			<text class="ft16 bold pl5 red2">{{code}}</text>
		</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				code:''
			}
		},
		onLoad() {
			uni.setNavigationBarTitle({
				title:this.$t('bind.codeauth')
			})
		},
		onShow() {
		   this.$utils.setThemeTop(this.theme)
		   this.getInfo()
		},
		methods:{
			getInfo(){
				this.$utils.initDataToken({url:'user/authorization_code'},res=>{
					this.code=res;
				})
			}
		},
		computed:{
		   ...mapState(['theme']),
		},
	}
</script>

<style>
</style>

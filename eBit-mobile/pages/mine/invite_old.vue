
<template xlang="wxml">
	<view class="" :class="{'light':theme=='light'}">
		<view class="flex column alcenter jscenter" >
			<view class="mt100">
				<!-- <image :src="codeImg" class="wt160 h160"></image> -->
				<view class="qrimg">
				    <tki-qrcode
				    ref="qrcode"
				    :val="val"
					:size="size"
					:unit="unit"
					:background="background"
					:foreground="foreground"
					:pdground="pdground"
					:icon="icon"
					:iconSize="iconsize"
					:lv="lv" 
					:onval="onval"
					:loadMake="loadMake"
				    @result="qrR" />
				</view>
			</view>
			<view class="mtb20 tc">{{$t('bind.codes')}}：{{code}}</view>
			<view class="tc ">{{$t('bind.tip')}}</view>
		</view>
	</view>
	
</template>

<script>
	import QR from '../../common/wxqrcode.js'
	import {domain} from '@/common/domain.js'
	import tkiQrcode from "@/components/tki-qrcode.vue"
	import {mapState} from 'vuex'
	export default{
		components: {tkiQrcode},
		data() {
			return {
				code:'',
				codeImg:'',
				val:'',
				size: 300, // 二维码大小
				unit: 'upx', // 单位
				background: '#000000', // 背景色
				foreground: '#ffffff', // 前景色
				pdground: '#ffffff', // 角标色
				icon: '', // 二维码图标
				iconsize: 40, // 二维码图标大小
				lv: 3, // 二维码容错级别 ， 一般不用设置，默认就行
				onval: false, // val值变化时自动重新生成二维码
				loadMake: true, // 组件加载完成后自动生成二维码
			}
		},
		onLoad(option) {
			this.code = option.code;
				uni.setNavigationBarTitle({
				title:this.$t('bind').tuiguang
			})
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
			this.$utils.setThemeTop(this.theme)
			// this.code = option.code;
			// var url=domain+'/mobile/register.html?code=' + this.code;
			// this.codeImg = QR.createQrCodeImg(url)
			// console.log(domain);
			var url=domain+'/h5/#/pages/mine/register?invite_code=' + this.code;
			this.val = url;
		},
		methods: {
			qrR(res) {
				// console.log(res)
			},
		},
	}
</script>

<style>
	.wt160{
		width: 320upx;
	}
	.qring{
		 border: 4px solid #313131;
	}
</style>

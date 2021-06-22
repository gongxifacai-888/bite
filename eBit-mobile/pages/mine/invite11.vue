<template>
	<view class="flex column alcenter jscenter">
		<view class="mt100">
			<image :src="codeImg" class="wt160 h160"></image>
		</view>
		<view class="mtb20 tc">{{$t('bind.codes')}}：{{code}}</view>
		<view class="tc ">{{$t('bind.tip')}}</view>
	</view>
</template>

<script>
	import QR from '../../common/qrcode.js'
	import {domain} from '@/common/domain.js'
	export default{
		data() {
			return {
				code:'',
				codeImg:''
			}
		},
		onLoad(option) {
			this.code = option.code;
			var url=domain+'/mobile/register.html?code=' + this.code;
		    this.codeImg = QR.createQrCodeImg(url);
			uni.setNavigationBarTitle({
				title:this.$t('bind').tuiguang
			})
		},
		methods: {
			
		},
	}
</script>

<style>
	.wt160{
		width: 320upx;
	}
</style>

<template>
	<view class="">
		<view class="status_bar">
			<view class="top_view"></view>
		</view>
		<view class="bg1 header fixed bdb_blue2 flex alcenter jscenter blue">
			<text :class="{'white':i==activeIndex}" class="plr10" v-for="(item,i) in tabList" :key="i">{{item}}</text>
		</view>
		<view class="pt50 bg1">
			
		</view>
	</view>
</template>

<script>
	export default{
		data(){
			return {
				tabList:['币币','合约','法币'],
				activeIndex:0,
			}
		}
	}
</script>

<style>
	.bg1{
		background: #0f2441;
	}
	.bg2{
		background: #15376f;
	}
	.bg3{
		background: #37365d;
	}
</style>

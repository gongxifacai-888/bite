<template>
	<view :class="{'light light_white':theme=='light'}">
		<view class="plr15 ft12 bgPart pt20 pb50">
			<!-- <view class="flex alcenter mt10 h40 bgPart between" @tap="actionSheetTap">
				<view class="">币种</view>
				<view class="flex alcenter">
					<text class="plr10" v-if="currency_list[index]">{{currency_list[index].code}}</text>
				    <uni-icons type="arrowdown" color="#ddd" size="20" />
				</view>
			</view> -->
			<view>申请币种</view>
			<view class="flex tc alcenter wraps mt10">
				<view class="w20 mr10 mt10 bd3a ptb5 posRelt" v-for='(item,index) in currency_list' :key='index' @click="changePay(index)">
				   <image src="/static/image/checks.png" v-if="item.select" class="h15 wt15 abstrot btm0 rt0"></image>
				   <text>{{item.code}}</text> 
			    </view>
			</view>
			<view class="mt15">
				<view>商家名称</view>
				<view class="flex between alcenter mt15 plr20 radius4 baseBg">
					<input type="text" placeholder="商家名称" class="address h40 flex1" v-model="name">
				</view>
			</view>
			<view class="tc bgGreen white h40 lh40 radius4 mt50 w90 mauto" @click="confirm">确认</view>
		</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				index:0,
				currency_list:[],
				listItem:[],
				name:''
			}
		},
		onShow() {
		   this.$utils.setThemeTop(this.theme)//改变导航栏背景色
		},
		onLoad(e){
			this.getWallet();
		},
		computed:{
			...mapState(['theme']),//获取当前UI颜色
		},
		methods:{
			changePay(index){//选择支付方式
				this.currency_list[index].select=!this.currency_list[index].select;
			},
			getWallet(){
				let that = this;
				this.$utils.initDataToken({url:'otc/currencies'},(res)=>{
					let data = res.filter(function(item){
						return item.legal_account_display;
					})
					data.map(function (item) {
						that.$set(item, 'select', false);
					})
					that.currency_list = data;
				})
			},
			actionSheetTap() {
				uni.showActionSheet({
					title:'',
					itemList: this.listItem,
					success: (e) => {
						this.index = e.tapIndex;
					}
				})
			},
			confirm(){
				let that = this;
				var currency_list = that.currency_list.filter(function (val) {//筛选所选支付方式
					return val.select
				});
				if(currency_list.length==0){
					return that.$utils.showLayer('请选择币种');
				}
				if(!this.name){
					return 	this.$utils.showLayer('请输入商家名');
				}
				let currencies = [];
				currency_list.map(function (item) {
					currencies.push(item.id)
				})
				this.$utils.initDataToken({url:'otc/seller_apply',type:'post',data:{name:this.name,currencies:currencies}},(res,msg)=>{
					this.$utils.showLayer(msg);
				})
			}
		}
	}
</script>

<style>
.rt0{right: 0;}
</style>

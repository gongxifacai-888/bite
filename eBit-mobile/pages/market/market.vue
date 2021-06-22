<template>
	<view class="" :class="{'light':theme=='light'}">
		<view class="bgHeader pb10 pb5 plr20 flex alcenter bdb1f">
			<view class="pt10 pb5 ft16 bold mr20 posRelt" :class="{'blue2':isActive==i}" v-for="(item,i) in marketLists" :key="i" @click="changeTab(i,item.code)">
				<image src="/static/line_blue.png" class="myline" v-if="isActive==i"></image>
				{{item.code}}
			</view>
		</view>
		<view class="plr10 ">
			<view class="flex alcenter ft12 blue3 ptb10">
				<view class="flex alcenter" style="flex: 1.5;">
					<view class="flex alcenter"  @tap="tapFilters(1,'currency_name')">
						<text class="pr5">{{$t('home.name')}}</text>
						<view class="">
							<image src="../../static/image/updown0.png" class="wt8 h10" v-if="isfilter1==0"></image>
							<image src="../../static/image/updown1.png" class="wt8 h10" v-else-if="isfilter1==1"></image>
							<image src="../../static/image/updown2.png" class="wt8 h10" v-else></image>
						</view>
					</view>
				</view>
				<view class="flex1 flex alcenter">
					<view class="flex alcenter" @tap="tapFilters(2,'now_price')">
						<text class="pr5">{{$t('home.new_price')}}</text>
						<view class="" >
							<image src="../../static/image/updown0.png" class="wt8 h10" v-if="isfilter2==0"></image>
							<image src="../../static/image/updown1.png" class="wt8 h10" v-else-if="isfilter2==1"></image>
							<image src="../../static/image/updown2.png" class="wt8 h10" v-else></image>
						</view>
					</view>
				</view>
				<view class="wt70 flex alcenter jsend">
					<view class="flex alcenter" @tap="tapFilters(3,'change')">
						<text class="pr5">{{$t('home.fu')}}</text>
						<view class="" >
							<image src="../../static/image/updown0.png" class="wt8 h10" v-if="isfilter3==0"></image>
							<image src="../../static/image/updown1.png" class="wt8 h10" v-else-if="isfilter3==1"></image>
							<image src="../../static/image/updown2.png" class="wt8 h10" v-else></image>
						</view>
					</view>
				</view>
			</view>	
			<navigator :url="'/pages/market/kline?legal_id='+item.quote_currency_id+'&currency_id='+item.base_currency_id+'&symbol='+item.base_currency_code+'/'+item.quote_currency_code+'&currency_match_id='+item.id" class="mb10 flex alcenter bdb1f ptb10" v-for="(item,i) in matches" v-if="marketLists[isActive]" :key="i" v-show="marketLists&&marketLists[isActive]&&item.open_change==1">
				<view class="" style="flex: 1.5;">
					<view><text class="ft14 bold">{{item.base_currency_code}}</text><text class="ft12 blue pl5">/{{item.quote_currency_code}}</text></view>
					<view class="gray9 blue pt5 ft12" v-if="item.currency_quotation">{{$t('market.volume')}} {{item.currency_quotation.volume | toFixed4}}</view>
				</view>
				<view class="flex1">
					<view class="" v-if="item.currency_quotation"> 
						<text class="ft14 bold">{{item.currency_quotation.close}}</text>
						<!-- <view class="pt5 ft12 blue">￥{{Math.floor(item.currency_quotation.close*fiat_convert_cny*100)/100}}</view> -->
					</view>
					<!-- <view class="gray9 ft10">24H额:￥{{item.volume}}</view> -->
				</view>
				<view class="wt70">
					<view class="tc h30 lh30 white wt70 radius2"  v-if="item.currency_quotation" :class="[(item.currency_quotation.change.substr(0,1))=='-'?'bgRed':'bgGreen']">{{(item.currency_quotation.change.substr(0,1))=='-' ? '' : '+'}}{{(item.currency_quotation.change-0) | toFixed2}}%</view>
				</view>					
			</navigator>
		</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default{
		data(){
			return {
				isActive:0,
				marketLists:[],
				fiat_convert_cny:6.8,
				legal_name:'',
				isfilter1:0,
				isfilter2:0,
				isfilter3:0,
				matches:[],
			}
		},
		computed:{
		   ...mapState(['theme']),
		},
		filters:{
			toFixed2: function (value) {
				value = Number(value);
				return value.toFixed(2);
			},  
			toFixed3: function (value) {
				value = Number(value);
				return value.toFixed(3);
			},  
			toFixed4: function (value) {
				value = Number(value);
				return value.toFixed(4);
			},  
		},
		onLoad() {
			uni.setNavigationBarTitle({
				title:this.$t('market').market
			})
		},
		onShow() {
			this.$utils.setThemeTop(this.theme);
			this.$utils.setThemeBottom(this.theme);
			this.getList();
			this.$socket.listenFun([{type: "sub", params: "DAY_MARKET"}],(res)=>{
				var res=JSON.parse(res);
				// console.log(this.marketLists)
				if(res.symbol){ //首次连接成功数据
					let legal_name = res.symbol.split('/')[1];
					if(legal_name==this.legal_name){
						// console.log(this.marketLists[this.isActive].matches);
						var currencyQuotation=this.marketLists[this.isActive].matches? this.marketLists[this.isActive].matches:[];
						for(var i in currencyQuotation){
							if(currencyQuotation[i].id==res.currency_match_id){
								// console.log(res.quotation.change)
								currencyQuotation[i].currency_quotation.volume=res.quotation.volume-0;
								currencyQuotation[i].currency_quotation.close=res.quotation.close-0;
								currencyQuotation[i].currency_quotation.change=res.quotation.change;
							}
						}
					}
				}
				
			})
		},
		onHide() {
			this.$socket.closeSocket();
		},
		onPullDownRefresh() {
			this.getList();
		},
		methods:{
			// 过滤
			filterList(name,isflag){
				if(this.marketLists[this.isActive].matches){
					this.marketLists[this.isActive].matches.sort(function(i,j){
						if(name=='currency_name'){
							if(isflag==1){
								return i['base_currency_code'].charCodeAt(0) - j['base_currency_code'].charCodeAt(0);
							}else{
								return j['base_currency_code'].charCodeAt(0) - i['base_currency_code'].charCodeAt(0);
							}
						}else{
							if(name=='now_price'){
								if(isflag==1){
									return i['currency_quotation'].close-j['currency_quotation'].close;
								}else{
									return j['currency_quotation'].close-i['currency_quotation'].close;
								}
							}else{
								if(isflag==1){
									return i['currency_quotation'].change-j['currency_quotation'].change;
								}else{
									return j['currency_quotation'].change-i['currency_quotation'].change;
								}
							}
						}
					})
				}
				
			},
			// 点击切换
			tapFilters(e,name){
				console.log(e,name);
				if(e==1){
					this.isfilter2=0;
					this.isfilter3=0;
					this.isfilter1=this.isfilter1==1?2:1;
					this.filterList(name,this.isfilter1);
				}
				if(e==2){
					this.isfilter1=0;
					this.isfilter3=0;
					this.isfilter2=this.isfilter2==1?2:1;
					this.filterList(name,this.isfilter2);
				}
				if(e==3){
					this.isfilter2=0;
					this.isfilter1=0;
					this.isfilter3=this.isfilter3==1?2:1;
					this.filterList(name,this.isfilter3);
				}
				
			},
			getList(){
				this.$utils.initData({url:'market/currency_matches'},res=>{
					uni.stopPullDownRefresh()
					this.marketLists=res;
					this.matches = this.marketLists[this.isActive].matches?this.marketLists[this.isActive].matches:[] //zzy
					if(res.length>0){
						this.legal_name=res[0].code;
						this.fiat_convert_cny=res[0].cny_price-0;
						// this.legal_price=res[0].legal_price;
					}
				})
			},
			changeTab(e,name){
				this.isfilter3=0;
				this.isfilter2=0;
				this.isfilter1=0;
				this.fiat_convert_cny=this.marketLists[e].cny_price-0;
				// this.legal_price=this.marketLists[e].legal_price-0;
				this.isActive=e;
				this.legal_name = name;
				this.matches = this.marketLists[this.isActive].matches?this.marketLists[this.isActive].matches:[] //zzy
			}
		}
	}
</script>

<style>
	page{
		background: #102030;
	}
</style>

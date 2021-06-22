<template>
	<view class="" :class="{'light':theme=='light'}">
		<view class="status_bar">
			<view class="top_view"></view>
		</view>
		<!-- 头部 -->
		<view class="header bgPart fixed flex alcenter between plr15 zdx100">
			<view class="flex alcenter" @click="showLeft=true">
				<image src="/static/image/list.png" class="h20 wt20"></image>
				<text class="ft18 ml10 bold">{{currency_name}}/{{legal_name}}</text>
				<text :class="['ml10 radius2 p2 dwbg redColor ft12',{'upbg greenColor':change.substr(0,1)!='-'}]" v-if="change">{{change.substr(0,1)!='-'?'+':''}}{{change}}%</text>
			</view>
			<navigator :url="'/pages/market/kline?legal_id='+legal_id+'&currency_id='+currency_id+'&symbol='+currency_name+'/'+legal_name+'&currency_match_id='+match_id">
				<image src="/static/image/datamap.png" class="h20 wt20"></image>
			</navigator>
		</view>
		<view class="flex pt50 pb15 bgPart">
			<!-- 左侧 -->
			<view class="flex1 plr15">
				<view class="flex alcenter tc overxy posRelt bggray gray7">
					<view :class="['flex1 h35 lh35 ft14',{'bggreen white':type=='buy'}]" @click="changeType('buy')">{{$t('trade').buy}}</view>
					<view class="bor abstrot mt5 bgPart"></view>
					<view :class="['flex1 h35 lh35 ft14',{'bgred white':type=='sell'}]" @click="changeType('sell')">{{$t('trade').sell}}</view>
				</view>
				<view class="flex alcenter ptb15 ft14">
					<text class="gray7" @click="togglePopup('bottom-share')" data-position="bottom">{{priceType=='limit' ? $t('trade').limit : $t('trade').shi}}</text>
					<image src="/static/image/pull.png" class="h10 wt10 ml5"></image>
				</view>
				<!-- 限市价切换弹窗 -->
				<uni-popup :show="popType === 'bottom-share'" position="bottom" @hidePopup="togglePopup('')">
					<view class="bottom-content">
						<view class="bottom-content-box tc gray7">
						    <view :class="['bdb_1e ptb10 ft14 bgPart',{'blueColor':priceType=='limit'}]" @click="changePriceType('limit')">{{$t('trade').limit}}</view>
							<view :class="['ptb10 ft14 bgPart',{'blueColor':priceType=='market'}]" @click="changePriceType('market')">{{$t('trade').shi}}</view>
				            <view class="pt5 pb10">
				            	<view class="ptb10 ft14 bgPart" @click="togglePopup('')">{{$t('trade').cancel}}</view>
				            </view>
						</view>
					</view>
				</uni-popup>
				<view class="">
					<view class="" v-show="priceType=='limit'">
						<view class="bd_input flex alcenter radius2">
							<input type="number" v-model='price' :placeholder="$t('trade.price')" class="h40 flex1 plr10 bdr_input" />
							<view class="wt80 flex alcenter tc">
								<view class="flex1 sub h12 flex alcenter jscenter" @click="sub">
									<image src="/static/image/sub.png" class="h12 wt12"></image>
								</view>
								<view class="flex1 h12 flex alcenter jscenter" @click="plus">
									<image src="/static/image/plus.png" class="h12 wt12"></image>
								</view>
							</view>
						</view>
						<!-- <view class="gray4 mt7">≈{{myCNY}}CNY</view> -->
					</view>
					<view class=""  v-show="priceType=='market'">
						<view class="bd_input flex alcenter radius2 bgBlack">
							<input type="text" :value="$t('trade.best')" disabled class="h40 flex1 plr10" />
						</view>
					</view>
					
					<view class="bd_input flex alcenter between mt15 plr10 radius2">
						<input type="number" v-model="number" :placeholder="$t('trade.num')" class="h40 flex1" @input="inputNum" />
						<view class="gray7 ft16">{{currency_name}}</view>
					</view>
					<view class="gray7 mt7">{{$t('trade.use')}} {{balance}} {{type=='buy' ? legal_name :currency_name}}</view>
					<view class="ptb15">
						<!-- <slider value="0" @change="sliderChange" step="25" block-color="#02c289" backgroundColor="#253247" activeColor="#02c289" block-size="15" /> -->
						<view class="flex alcenter around tc tab-box gray7">
							<view :class="['bd_input flex1 radius2',{'bggreen white bd_green': percent==item&&type=='buy'},{'bgred white bd_red': percent==item&&type=='sell'},{'ml10':index>0}]" v-for="(item,index) in percentList" :key='index' @click="changePercent(item)">{{item}}%</view>
						</view>
					</view>
					<!-- <view class="bd_input flex alcenter between plr10 radius2">
						<input type="text" password class="h40 flex1" v-model="tradePassword" :placeholder="$t('login.e_pdeal')" />
					</view> -->
					<view class="gray7 flex alcenter mt10">
						<text>{{$t('trade.amout')}}</text>
						<text class="nwhite pl5">{{tradeNum || '--'}}{{legal_name}}</text>
					</view>
					<view :class="['btn-default mt10 bgred',{'bggreen':type=='buy'}]" @click="submit">{{type=='buy'?$t('trade.buy'):$t('trade.sell')}}{{currency_name}}</view>
				</view>
			</view>
			<!-- 盘口 -->
			<view class="trade-left flex column">
				<view class="flex between gray4 pr15 ft12">
					<text>{{$t('trade.price')}}</text>
					<text>{{$t('trade.num')}}</text>
				</view>
				<view class="ft12 flex flex1 mt5 column">
					<view class="flex1 flex column jsend asks" v-if="ptype!=1"><!-- 取最后的五条记录 -->
						<view class="flex between hg20 alcenter mtn gray7 pr15" v-if="ptype==0 ? (sellData.length>5 ? (index>(sellData.length-6)&&index<10) : index<5) : index<10" :style="'background-size:'+parseInt((item.amount/sellMax)*100)+'% 100%'" :key="index" v-for="(item,index) in sellData" @click="changeNum(item.price)">
							<text class="redColor">{{item.price}}</text>
							<text v-if="item.amount>0">{{item.amount}}</text>
							<text v-else>{{item.amount}}</text>
						</view>
					</view>
					<view class="ptb5">
						<view :class="['redColor ft18',{'greenColor':change.substr(0,1)!='-'}]">{{last_price}}</view>
						<!-- <text class="gray4">≈{{last_price * cny| toFixed2}}CNY</text> -->
					</view>
					<view class="flex1 flex column bids" v-if="ptype!=2">
						<view class="flex between hg20 alcenter mtn gray7 pr15" v-if="ptype==0 ? index<5 : index<10" :style="'background-size:'+parseInt((item.amount/buyMax)*100)+'% 100%'"  :key="index" v-for="(item,index) in buyData" @click="changeNum(item.price)">
							<text class="greenColor">{{item.price}}</text>
							<text v-if="item.amount>0">{{item.amount}}</text>
							<text v-else>{{item.amount}}</text>
						</view>
					</view>
				</view>
				<view class="flex alcenter mt5">
					<view :class="['wt20 h20 flex alcenter jscenter radius2 bd3a',{'bdActive':ptype==0}]" @click="ptype=0">
						<image src="/static/image/pan1.svg" mode="" class="wt15 h15"></image>
					</view>
					<view :class="['wt20 h20 flex alcenter jscenter radius2 bd3a ml5',{'bdActive':ptype==1}]" @click="ptype=1">
						<image src="/static/image/pan2.svg" mode="" class="wt15 h15"></image>
					</view>
					<view :class="['wt20 h20 flex alcenter jscenter radius2 bd3a ml5',{'bdActive':ptype==2}]" @click="ptype=2">
						<image src="/static/image/pan3.svg" mode="" class="wt15 h15"></image>
					</view>
				</view>
			</view>
		</view>
		<!-- 左侧边栏交易对 -->
		<view class="left-mode">
			<uni-drawer :visible="showLeft" style="background: rgb(16, 32, 48,.5);" mode="left" @close="showLeft = false">
				<view class="flex column ht100">
					<view class="pt20">
						<view class="ft18 pl15 ptb10">{{$t('trade.bibi')}}</view>
						<view class="flex plr15 bdb_1e bold gray7">
							<view :class="['mr20 pb5 ft14',{'blueColor bdb_active':current==index}]" v-for="(item,index) in quotationList" :key="item.id" @click="changeLegal(index,item.code)">{{item.code}}</view>
						</view>
					</view>
					<view class="flex1 overyscroll" v-for="(market,index) in marketList" :key="index" v-if="current==index">
						<view :class="['flex alcenter between ptb15 plr15  bdb_1e',{' bggray':legal_id==item.quote_currency_id && currency_id==item.base_currency_id}]" :key="idx" v-for="(item,idx) in market" v-if="item.open_change==1" @click="changeCurrency(idx)">
							<view class="flex flexend">
								<text class="ft14">{{item.base_currency_code}}</text>
								<text class="gray7">/{{item.quote_currency_code}}</text>
							</view>
							<view :class="[item.currency_quotation.change>=0 ? 'greenColor' : 'redColor' ]" v-if="item.currency_quotation">{{item.currency_quotation.close}}</view>
						</view>
					</view>
				</view>
			</uni-drawer>
		</view>
		<!-- 当前委托 -->
		<view class="mt10 bgPart">
			<view class="flex alcenter between ptb10  plr15">
				<view class="flex flexend">
					<text class="ft18">{{$t('trade.delegate')}}</text>
					<view class="flex alcenter ml10 ft14">
						<text class="gray7" @click="togglePopup('bottom-entrust')" data-position="bottom">{{enType=='in' ? $t('trade.buy') : $t('trade.sell')}}</text>
						<image src="/static/image/pull.png" class="h10 wt10 ml5"></image>
					</view>
				</view>
				<view class="flex alcenter gray7">
					<image src="/static/image/all.png" class="h20 wt20 ml5"></image>
					<navigator url="entrust">{{$t('trade.all')}}</navigator>
				</view>
			</view>
			<!-- 委托类型切换弹窗 -->
			<uni-popup :show="popType === 'bottom-entrust'" position="bottom" @hidePopup="togglePopup('')">
				<view class="bottom-content">
					<view class="bottom-content-box tc gray7">
					    <view :class="['bdb_1e ptb10 ft14 bgPart',{'blueColor':enType=='in'}]" @click="changeEntrustType('in')">{{$t('trade.buy')}}</view>
						<view :class="['ptb10 ft14 bgPart',{'blueColor':enType=='out'}]" @click="changeEntrustType('out')">{{$t('trade.sell')}}</view>
			            <view class="pt5 pb10">
			            	<view class="ptb10 ft14 bgPart" @click="togglePopup('')">{{$t('trade.cancel')}}</view>
			            </view>
					</view>
				</view>
			</uni-popup>
			<view class="en-ul">
				<view class="ptb10 bdb_1e plr15" v-for="(item,index) in enList" :key="item.id">
					<view class="flex between alcenter">
						<view class="flex flex flexend">
							<text :class="['ft14 redColor',{'greenColor':enType=='in'}]">{{enType=='in'?$t('trade.buy') : $t('trade.sell')}}</text>
							<text class="gray4 pl10">{{item.created_at.substring(10,16)}} {{item.created_at.substring(5,7)}}/{{item.created_at.substring(8,11)}}</text>
						</view>
						<view class="blueColor bgBlack radius2 plr20 ptb5" @click="revoke(index,item.id)">{{$t('trade.back')}}</view>
					</view>
					<view class="mt15 flex">
						<view class="flex1">
							<text class="gray4" v-if="item.currency_match">{{$t('trade.price')}}({{item.currency_match.quote_currency_code}})</text>
							<view class="mt5">{{item.price}}</view>
						</view>
						<view class="flex1 tc">
							<text class="gray4" v-if="item.currency_match">{{$t('trade.num')}}({{item.currency_match.base_currency_code}})</text>
							<view class="mt5">{{item.number}}</view>
						</view>
						<view class="flex1 tr">
							<text class="gray4" v-if="item.currency_match">{{$t('trade.real_num')}}({{item.currency_match.base_currency_code}})</text>
							<view class="mt5">{{item.transacted_amount}}</view>
						</view>
					</view>
				</view>
				<view class="tc pt30 pb100" v-if='enList.length==0'>
					<image src="/static/image/nodata.png" class="h50 wt50"></image>
					<view class="gray7">{{$t('home.norecord')}}</view>
				</view>
				<view class="tc gray7 ptb20" v-show="isBottom">{{$t('home.loading')}}...</view>
				<view class="tc gray7 ptb20" v-show="!hasMore && enList.length>10">{{$t('home.nomore')}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	import uniDrawer from '@/components/uni-drawer.vue';
	import uniIcon from '../../components/uni-icon.vue';
	import uniPopup from '@/components/uni-popup2.vue';
	import {mapState} from 'vuex';
	export default {
		components: {
			uniDrawer,
			uniIcon,
			uniPopup
		},
		data() {
			return {
				rate:80,
				current:0,//法币index
				type:'buy',//买卖类型
				ptype:0,//盘口类型
				cny:6.9,//兑换率
				price: '',//单价
				number: '',//数量
				showLeft: false,//左侧弹窗
				popType:'',//底部弹窗
				priceType:'limit',//限价limit市价market
				enType:'in',//当前委托类型
				percentList:[25,50,75,100],//百分比
				percent:'',//选择的百分比
				balance:'',//余额
				last_price:'',//最新价
				change:'',//涨跌幅
				quotationList:[],//币种列表
				marketList:[],//交易对列表
				enList:[],//当前委托
				page:1,
				isBottom:false,
				hasMore:true,
				legal_name:'',
				currency_name:'',
				legal_id:'',
				currency_id:'',
				match_id:'',
				sellData: [['--', '--'], ['--', '--'], ['--', '--'], ['--', '--'],['--', '--']],//，卖盘
				buyData: [['--', '--'], ['--', '--'], ['--', '--'], ['--', '--'],['--', '--']],//买盘
				first:true,
				usdt_price:1,
				tradePassword:"",
				timer:null
			}
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
		},
		computed:{
			myCNY(){//输入价格折合cny
				return (this.price*this.cny).toFixed(4) || '0.00';
			},
			tradeNum(){//交易额 单价*数量
			    // console.log(this.price,this.number)
				return (this.price*this.number).toFixed(8) || '0.0000000';
			},
			buyMax(){//买盘最大数量
				let list=[],data = this.buyData;
				data.map((item)=>{
					list.push(item.amount)
				});
				return Math.max.apply(null, list)
			},
			sellMax(){//卖盘最大数量
				let list=[],data = this.sellData;
				data.map((item)=>{
					list.push(item.amount)
				});
				return Math.max.apply(null, list)
			},
			...mapState(['theme']),
		},
		onLoad(e) {
			uni.setNavigationBarTitle({
				title:this.$t('trade').trade
			})
		},
		onShow() {
			this.$utils.setThemeTop(this.theme)
			this.$utils.setThemeBottom(this.theme)
			if(!uni.getStorageSync('token')){
				uni.navigateTo({
					url: '/pages/mine/login',
				});
				return;
			}
			this.initList();
		},
		onHide() {
			this.$socket.closeSocket();//关闭socket
			// if(this.timer){
			//   clearInterval(this.timer);
			// }
			this.showLeft = false;
			this.popType = '';
		},
		onPullDownRefresh(){
			this.initList();
		},
		// onReachBottom() {
		// 	if(!this.hasMore) return;
		// 	this.page++;
		// 	this.entrustList();
		// },
		methods: {
			sliderChange(e) {//滑动条
				console.log('value 发生变化：' + e.detail.value)
			},
			togglePopup(popType){//弹窗
				this.popType = popType;
			},
			onInput(e){//限制价格小数点长度
			    console.log(e)
				let price = e.detail.value;
				let length = this.last_price.toString().split(".")[1].length || 8;//最新价小数点长度
				if(length==2){
					this.price = (price.match(/^\d*(\.?\d{0,2})/g)[0]) || null;
				}else if(length==4){
					this.price = (price.match(/^\d*(\.?\d{0,4})/g)[0]) || null;
				}else if(length==6){
					this.price = (price.match(/^\d*(\.?\d{0,6})/g)[0]) || null;
				}else{
					this.price = (price.match(/^\d*(\.?\d{0,8})/g)[0]) || null;
				}
			},
			changePercent(percent){//切换百分比
			    if(this.balance==0){
					return this.$utils.showLayer(this.$t('trade.notenough'))
				}
				if(this.priceType=='market'){//市价取近的价格
				   this.getPrice();
				   if(!this.price || this.price==''){
				   	return this.$utils.showLayer(this.$t('trade.notbest'));
				   } 
				}else{
					if(!this.price){
						return this.$utils.showLayer(this.$t('trade.p_price'));
					}
				}
				this.percent = percent;
				var newNum;
				if(this.type=='buy'){//数量等于可用余额除以价格*百分比
					newNum = Number(this.balance/this.price*(this.percent)/100);
				}else{//数量等于可用余额*百分比
					newNum = Number(this.balance*(this.percent)/100);
				}
				this.number = ((parseInt( newNum * 10000 ) / 10000 )-0).toFixed(4);
			},
			changeType(type){//切换买入卖出//更新价格/更新余额
				this.type = type;
				this.number = '';
				this.percent = '';
				uni.setStorageSync('tradeType',type);
				this.getPrice();
				this.currencyDetail();
			},
			changePriceType(type){//限市价切换
			    this.priceType = type;
				this.togglePopup('');
				this.getPrice();
			},
			changeEntrustType(type){//委托类型切换
			    this.enType = type;
				this.togglePopup('');
				this.clear();
				this.enList=[];
				this.entrustList();
			},
			changeNum(price){//点击盘口
			    if(this.priceType=='limit'){
					this.price = price;
				}
			},
			plus(){//加号
				let price = Number(this.price)*10*10;
				price += 1;
				this.price = (price/100).toFixed(4);
			},
			sub(){//减号
				let price = Number(this.price)*10*10;
				if(price<=0){return this.price = 0;};
				price -= 1;
				this.price = (price/100).toFixed(4);
			},
			inputNum(e){//数量输入
                this.percent = '';
				let number = Number(e.detail.value);
				let totalNum = Number(number*this.price);
				console.log(totalNum,this.balance)
				if(this.type=='buy'){
					if(totalNum>(this.balance-0)){
						return this.$utils.showLayer('余额不足');
					}
				}else{
					if(number>(this.balance-0)){
						return this.$utils.showLayer('余额不足');
					}
				}
				
			},
			getPrice(){//帮助用户筛选最新的交易价
				this.price = (this.type=='buy') ? (this.sellData.length>0 ? this.sellData[this.sellData.length-1].price : '') : (this.buyData.length>0 ? this.buyData[0].price : '');
			},
			changeLegal(index){//法币切换
				this.current = index;
			},
			changeCurrency(idx){//交易对切换
			    let data0 = this.marketList[this.current];
				let data1 = data0.filter(function(item,index){
					return item.open_change == 1;
				})
				let data = data1[idx];
				this.legal_name = data.quote_currency_code;
				this.legal_id = data.quote_currency_id;
				this.currency_id = data.base_currency_id;
				this.currency_name = data.base_currency_code;
				this.match_id = data.id;
				let localData={
					legal_name:this.legal_name,
					legal_id:this.legal_id,
					currency_name:this.currency_name,
					currency_id:this.currency_id,
					match_id:this.match_id,
				}
				uni.setStorageSync('tradeData',JSON.stringify(localData));
				this.$socket.closeSocket();
				this.percent='';
				this.price='';
				this.number='';
				this.first=true;
				this.tradeDetail();
				this.socketData();
				this.showLeft = false;
				this.currencyDetail();
				this.clear();
				this.entrustList();
			},
			initList(){//币种数据
				let that = this;
				that.$utils.initData({ url: 'market/currency_matches' }, res => {
					if (res.length > 0) {
						var datas = res.filter(function(item, index) {
							return item.is_quote == 1;
						});
						// var datas = res;
						that.quotationList = datas;
						var arr = [];
						for(var i=0;i<datas.length;i++){
							arr[i] = datas[i].matches
						};
						// console.log(arr)
						let arr0 = arr[0].filter(function(item,index){
							return item.open_change == 1;
						})
					    var arrFrist=arr0[0];//第一对交易对						
						that.marketList = arr; //交易对列表
						that.marketList.map((item,index)=>{//筛选过滤open_change==1
							item.map((itm,inx)=>{
								if(itm.open_change==0){
									item.splice(inx,1)
								}
							})
						})
						//开始
						let localData = uni.getStorageSync('tradeData'); // string
						console.log(localData+'8888888')
						// #ifdef APP-PLUS
						   localData = plus.storage.getItem('tradeData');
						// #endif
						if(localData !=''){
							localData = JSON.parse(localData)
						}
						let tradeType = uni.getStorageSync('tradeType');
						
						// #ifdef APP-PLUS
						// localData = uni.getStorageSync('tradeData') || JSON.parse(plus.storage.getItem('tradeData'));//本地交易对
						// tradeType = uni.getStorageSync('tradeType') || plus.storage.getItem('tradeType');//本地交易类型
						tradeType = plus.storage.getItem('tradeType');//本地交易类型
						// #endif
						if(tradeType){
							that.type=tradeType;
						}
						if(localData){
							that.legal_name = localData.legal_name;
							that.legal_id = localData.legal_id;
							that.currency_id = localData.currency_id;
							that.currency_name = localData.currency_name;
							that.match_id = localData.match_id;
							datas.map(function(item,index){//判断当前法币
								if(item.id == localData.legal_id){
									return that.current = index;
								}
							})
						}else{
							that.legal_name = arrFrist.quote_currency_code;
							that.legal_id = arrFrist.quote_currency_id;
							that.currency_id = arrFrist.base_currency_id;
							that.currency_name = arrFrist.base_currency_code;
							that.match_id = arrFrist.id;
						}
						if(uni.getStorageSync('token')){
							that.sellData = [['--', '--'], ['--', '--'], ['--', '--'], ['--', '--'],['--', '--']];//，卖盘
				            that.buyData = [['--', '--'], ['--', '--'], ['--', '--'], ['--', '--'],['--', '--']];//买盘
							this.percent = '';
							this.price = '';
							this.number = '';
							that.first = true;
							that.clear();
							that.currencyDetail();
							that.tradeDetail();
							that.socketData();
							that.entrustList();
							// if(that.timer == null){
							// 	that.timer = setInterval(function(){
							// 		that.entrustList();
							// 	},6000)
							// }
							// that.$utils.initData({ url: 'market/kline',data:{currency_match_id:that.match_id,period:'1min'}}, (res,msg) => {
							// 	// console.log(res.data)
							// });
						}
					}
				});
			},
			submit(){//下单
				let that = this;
				this.price = this.price.toString().match(/^\d*(\.?\d{0,8})/g)[0] || null;
				if(!that.number){
					return that.$utils.showLayer(this.$t('trade.p_num'));
				}
				if(that.priceType=='market'){//市价取近的价格
				   this.getPrice();
				   if(!this.price || this.price==''){
				   	return that.$utils.showLayer(this.$t('trade.notbest'));
				   } 
				}else{
					if(!that.price){
						return that.$utils.showLayer(this.$t('trade.p_price'));
					}
				}
				let url;
				that.type == 'sell' ? url='tx_out/add' : url='tx_in/add'
				that.$utils.initDataToken({ url: url,data:{price:that.price,number:that.number,currency_match_id:that.match_id},type:'post' }, (res,msg) => {
					console.log(res,msg)
					that.$utils.showLayer(msg);
					that.number='';
					setTimeout(function() {
						that.currencyDetail();
					}, 1000);
					that.clear();
					that.entrustList();
				});
			},
			currencyDetail(){//币种余额
			    let that = this;
				var currency,account_type_id;
				this.type=='buy' ? currency = this.legal_id : currency = this.currency_id;
				// this.type=='buy' ? account_type_id = 2 : account_type_id = 1;
	            that.$utils.noshowToken({ url: 'account/detail',data:{account_type_id:1,currency_id:currency},load:false }, res => {
					// that.cny = res.ExRate;
					that.balance = res.balance;
				});
			},
			tradeDetail(){//盘口历史数据/最新价历史数据
			    let that = this;
				that.$utils.initDataToken({ url: 'market/deal',data:{currency_match_id:that.match_id}}, res => {
					// that.cny = res.cny_usd-0;
					that.cny = res.quotation.currency_match.quote_currency.cny_price;
					that.usdt_price = res.quotation.currency_match.quote_currency.usd_price;
					that.last_price = res.quotation.close;
					that.change = res.quotation.change;
					that.buyData = res.depth.in;
					that.sellData = (res.depth.out).reverse();
					// console.log(that.sellData);
					// that.getPrice();
				});
			},
			socketData(){//socket连接
				var that = this;
				var tokens = uni.getStorageSync('token');
				let symbol = this.currency_name+'/'+this.legal_name;
				that.$socket.listenFun([
					{type: "login", params: tokens},
					{type: "sub",params: "MARKET_DEPTH."+symbol},
					{type: "sub", params: "DAY_MARKET"},
					{type: "sub", params: "KLINE."+symbol},
					{type: "sub", params: "TX_MATCHED"},
				],(res)=>{
					let msg = JSON.parse(res);
					// setInterval(()=>{
					// 	uni.sendSocketMessage({
					// 	  data: 'AAA'
					// 	});
					// },10000)
					if (msg.type == "MARKET_DEPTH") {//更新盘口
							that.buyData = msg.depth.in;
							that.sellData = (msg.depth.out).reverse();
						if(that.first){//第一次进来默认根据当前买卖类型自动填充最优价格（1次）
							that.getPrice();
							that.first = false;
						}
					}
					if (msg.type == "DAY_MARKET") {
						if(that.match_id==msg.currency_match_id){//更新最新价
							that.last_price = msg.quotation.close;
							that.change = msg.quotation.change;
						}
						that.marketList[that.current].map((item,index)=>{//更新行情
							if(item.id == msg.currency_match_id){
								// console.log(msg);
								return that.marketList[that.current][index].currency_quotation.close = msg.quotation.close;
							}
						})
					}
					if (msg.type == "TX_MATCHED"){
						console.log(msg)
						let order = msg.order;
						if(this.type=='buy'){
							this.balance = order.quote_account.balance
						}else{
							this.balance = order.base_account.balance
						}
						this.enList.map((item,index)=>{
							if(item.id==order.id){
								that.enList[index].number = order.number;
								that.enList[index].transacted_amount = order.transacted_amount;
								if(order.number==order.transacted_amount){
									that.enList.splice(index, 1);//动态删掉这个订单
								}
							}
						})
					}
				})
			},
			clear(){//恢复委托列表默认值
				let that = this;
				that.page = 1;
				that.isBottom = false;
				that.hasMore = true;
			},
			entrustList(){//委托
				let that = this;
				let url;
				that.enType == 'out' ? url='tx_out/list' : url='tx_in/list'
				that.$utils.noshowToken({ url: url,data:{currency_match_id:that.match_id,page:that.page,limit:30}}, res => {
					var data = res.data;
					uni.stopPullDownRefresh();
					that.isBottom=false;
					that.enList = (that.page == 1) ? data : that.enList.concat(data);
					that.hasMore = (res.last_page == res.current_page) ? false : true;
					// console.log(that.enList,that.hasMore)
				});
			},
			revoke(index,id){//撤单
				let that = this;
				let url = that.enType == 'out' ? 'tx_out/cancel' : 'tx_in/cancel';
				that.$utils.initDataToken({ url: url,data:{id:id}}, (res,msg) => {
					that.$utils.showLayer(msg);
					that.enList.splice(index, 1);//动态删掉这个订单
				});
			}
		},
	}
</script>

<style>
	.trade-left {
		width: 300upx;
	}
	.w270 {
		width: 270upx;
	}
	uni-slider {
		margin: 0 0 0 14upx;
	}
	.tab-box view {
		padding: 4upx 0;
	}
	.sub{
		border-right: 1px solid #253247;
	}
	.bor {
		width: 32upx;
		height: 140upx;
		left: calc(50% - 20upx);
		transform: rotate(25deg);
		-ms-transform: rotate(25deg);
		-moz-transform: rotate(25deg);
		-webkit-transform: rotate(25deg);
		-o-transform: rotate(25deg);
	}
	.en-ul>.bdb_1e:last-child{
		border-bottom: none;
	}
	.p2{
		padding: 0 4upx;
	}
	.dwbg{
		background: rgba(250,82,82,.1);
	}
	.upbg{
		background: rgba(18,184,134,.1);
	}
	.asks>view{
		background-image: linear-gradient(rgba(250,82,82,.1),rgba(250,82,82,.1));
	}
	.bids>view{
		background-image: linear-gradient(rgba(18,184,134,.1),rgba(18,184,134,.1));
	}
	.asks>view,.bids>view{
		background-position: 100%;
		background-repeat: no-repeat;
		background-size: 0% 100%;
	}
</style>

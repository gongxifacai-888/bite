<template>
	<view class=""  :class="{'light':theme=='light'}">
		<view class="status_bar bgBlue3">
			<view class="top_view bgBlue3"></view>
		</view>
		<view class="header fixed flex alcenter between plr15 bgBlue3">
			<view>
				<image src="../../static/image/forward.png" mode="aspectFit" class="wt20 h20" @click="back()"></image>
			</view>
		</view>
		<view class="">
			<view class="done flex between alcenter pt60 plr15 pb15 bgBlue3">
				<view class="flex column flexstart">
					<view class="flex alcenter">
						<!-- 0 交易中 1已付款 2延期确认 3维权 4取消 5确认 -->
						<image src="../../static/image/naozhong.png" mode="aspectFit" class="wt20 h20"></image>
						<text class="white ft24 bold ml5" v-if="data.status === 0 && data.way=='BUY'">{{$t('store.p_pay')}}</text>
						<text class="white ft24 bold ml5" v-if="data.status === 0 && data.way=='SELL'">{{$t('store.p_waitpay')}}</text>
						<text class="white ft24 bold ml5" v-if="data.status == 1">{{$t('trade.has_pay')}}</text>
						<text class="white ft24 bold ml5" v-if="data.status == 2">延期确认</text>
						<text class="white ft24 bold ml5" v-if="data.status == 3">{{$t('store.protection')}}</text>
						<text class="white ft24 bold ml5" v-if="data.status == 4">{{$t('trade.has_cancel')}}</text>
						<text class="white ft24 bold ml5" v-if="data.status === 5">{{$t('trade.has_done')}}</text>
					</view>
					<view class="pt5">
						<!-- <text class="white" v-if="data.status === 0 && data.way=='BUY'">{{$t('store.t_pay')}}</text> -->
						<text class="white" v-if="data.status === 0 && data.way=='SELL'">{{$t('store.t_waitpay')}}</text>
						<text class="white" v-if="data.status === 0 && data.way=='BUY'">请在<text class="plr5">{{timer}}</text>内付款给商家，超时自动取消。</text>
						<text class="white" v-if="(data.status === 1 || data.status==2) && data.way=='SELL'">请在<text class="plr5">{{timer}}</text>内确认收款，超时自动确认。</text>
						<text class="white" v-if="data.status === 1 && data.way=='BUY'">{{$t('store.t_havepay')}}</text>
						<text class="white" v-if="data.status == 2">还未收到买家打款，延期确认中</text>
						<text class="white" v-if="data.status == 3">还未收到买家打款，订单申请维权中</text>
						<text class="white" v-if="data.status == 4">{{$t('store.t_paycancel')}}</text>
						<text class="white" v-if="data.status === 5">{{$t('store.t_hasdone')}}</text>
					</view>
					<!-- <text class="ft12 white mt5">请在<text class="ft14">14:10</text>内付款给商家</text> -->
				</view>
				<view class="flex column alcenter" @click="call">
					<image src="../../static/image/lianxi.png" mode="aspectFit" class="wt30 h25"></image>
					<text class="ft12 white mt5">{{$t('store.call')}}</text>
				</view>
			</view>
			<view class="price flex column plr15 ptb15">
				<view class="flex alcenter">
					<text class="mr5 blue21 ft18 bold" v-if="data.area_info">{{data.amount}} {{data.area_info.currency}}</text>
					<!-- <image src="../../static/image/fuzhi.png" mode="aspectFit" class="wt15 h15"></image> -->
				</view>
				<view class="flex between alcenter mt10 ft13 price_b">
					<view class="flex column between gray75" @click="togglePopup('bottom', 'share')">
						<text>{{$t('legal.price')}}</text>
						<text class="pt5">{{$t('legal.num')}}</text>
					</view>
					<view class="flex column between">
						<text v-if="data.area_info">{{data.price}} {{data.area_info.currency}}</text>
						<text class="pt5">{{data.number}} {{data.currency_name}}</text>
					</view>
					<view class="flex column alcenter between">
						<image src="../../static/image/account_about_image.png" mode="aspectFit" class="wt20 h20"></image>
						<text class="">{{data.currency_name}}</text>
					</view>
				</view>
			</view>
			<view class="h15 bggray" ></view>
			<view class="info flex column plr15 ft12">
				<view class="">
					<view class="bdb1f ptb10 " v-for="(item,index) in data.otc_payways" :key='index'>
						<view class="flex alcenter between ">
							<view class="flex alcenter">
								<image src="../../static/image/zhi.png" mode="aspectFit" v-if="item.pay_code=='alipay'" class="wt15 h15 mr5"></image>
								<image src="../../static/image/chat.png" mode="aspectFit" v-else-if="item.pay_code=='wxpay'" class="wt15 h15 mr5"></image>
								<image src="../../static/image/card.png" mode="aspectFit" v-else class="wt15 h15 mr5"></image>
								<text class="mr5 gray75">{{item.payway.name}}</text>
							</view>
							<view class="flex alcenter" v-if="item.pay_code!='bank'">
								<image src="../../static/image/erweima.png" mode="aspectFit" class="wt20 h20" @click="isshow(item.raw_data.qrcode)"></image>
							</view>
						</view>
						<view class="flex alcenter mt10">
							<text class="tl mr5 flex1" v-if="item.pay_code!='bank'">{{item.raw_data.account}}</text>
							<text class="tl mr5 flex1" v-else>{{item.raw_data.card_no}}</text>
							<image src="../../static/image/fuzhi.png" mode="aspectFit" class="wt15 h15" v-if="item.pay_code!='bank'" @click="copy(item.raw_data.account)"></image>
							<image src="../../static/image/fuzhi.png" mode="aspectFit" class="wt15 h15" v-else @click="copy(item.raw_data.card_no)"></image>
						</view>
						<view class="mt5 flex alcenter between" v-if="item.pay_code=='bank'">
							<view>
								<text class="gray75">收款人：</text> 
								{{item.raw_data.real_name}}
							</view>
							<text>{{item.raw_data.bank_name}}({{item.raw_data.bank_address}})</text>
						</view>
					</view>
				</view>
				<view class="flex between alcenter ptb10 bdb1f">
					<text class="gray75">{{$t('store.paycancode')}}</text>
					<view class="flex alcenter">
						<text class="mr5">{{data.id}}</text>
					</view>
				</view>
				<view class="flex between alcenter ptb10 bdb1f">
					<text class="gray75">{{$t('store.dotime')}}</text>
					<text class="mr5">{{data.created_at}}</text>
				</view>
				<view class="flex between alcenter ptb10 bdb1f" v-if="data.status!=4">
					<text class="gray75">支付凭证</text>
					<view class="mr5 bgBlue radius4 ptb5 plr10 white" v-if="(data.way=='BUY')&&(pay_voucher=='')" @tap="uploadImg">上传</view>
					<image :src="pay_voucher" mode="aspectFit" class="wt20 h20" @click="pay_img=true" v-if="pay_voucher!=''"></image>
					<text v-if="data.way=='SELL'&&pay_voucher==''">暂无</text>
				</view>
				<uni-collapse ref="add" class="warp">
					<uni-collapse-item title="状态跟踪" :show-animation="true" class="gray75">
						<view class="example-body">
							<uni-steps :options="list" active-color="#007AFF" :active="list.length-1" direction="column" />
						</view>
					</uni-collapse-item>
				</uni-collapse>
				<view class="flex alcenter ptb10" v-if="data.status==0 && data.way=='BUY'">
					<!-- <text class="white">{{$t('store.t_self')}}{{data.way}}{{$t('store.t_transfer')}}</text> -->
					<text class="white">请向以上支付方式自行转账</text>
				</view>
			</view>
		</view>
		<view class="bggray w100 fixed lf0 btm0" v-if="(data.status==0 || data.status==2 || data.status==3) && data.way=='BUY'">
			<view class="ptb10 plr15 gray75">
				{{$t('store.t_tip')}}。
			</view>
			<view class="flex between alcenter white bold plr15 ft14 ptb10 baseBg2">
				<view class="baseBg3 radius2 ptb10 flex1 tc mr10" @click="togglePopup('center', 'ceil'),payType='ceil'">{{$t('store.cancelorder')}}</view>
				<view class="bgBlue2 radius2 ptb10 flex1 tc doit pointer ml5" @click="togglePopup('center', 'ceil'),payType='ipay'" v-if="data.status==0">{{$t('store.ipay')}}</view>
			</view>
		</view>
		<!-- 确认收款 -->
		<view class="bggray w100 fixed lf0 btm0" v-if="(data.status==1 || data.status==2 || data.status==3) && data.way=='SELL'">
			<view class="ptb10 plr15 gray75 tc">
				{{$t('store.t_look')}}
			</view>
			<view class="flex between alcenter white bold plr15 ft14 ptb10 baseBg2">
				<!-- 1已付款 2延期确认 3维权 -->
				<!-- 延期确认 -->
				<view class="bgBlue2 radius2 ptb10 flex1 tc doit pointer ml5" @click="togglePopup('center', 'ceil'),payType='depay'" v-if="data.status==1">延期确认</view>
				<!-- 申请维权(在延期之后才有) -->
				<view class="bgBlue2 radius2 ptb10 flex1 tc doit pointer ml5" @click="togglePopup('center', 'ceil'),payType='wepay'" v-if="data.status==2">{{$t('store.apply')}}</view>
				<!-- 确认完成 -->
				<view class="bgBlue2 radius2 ptb10 flex1 tc doit pointer ml5" @click="togglePopup('center', 'ceil'),payType='fpay'">{{$t('store.t_con_collect')}}</view>
			</view>
		</view>
		<!-- 付款码弹窗 -->
		<view class="sx_show flex column alcenter jscenter" v-show="pay_code" @click="isshow()">
			<view class="center-box ptb15 plr15 tc bgWhite" @click.stop>
				<image :src="code_img" class="w100" style="width: 440upx;max-height: 440upx;"></image>
			</view>
			<view class="xian bgWhite"></view>
			<image src="../../static/image/cuo.png" mode="aspectFit" class="wt30 h30" @click="isshow()" @click.stop></image>
		</view>
		<!-- 支付凭证弹窗 -->
		<view class="sx_show flex column alcenter jscenter" style="top: 0;z-index: 99999999;" v-show="pay_img" @click="pay_img=false">
			<view class="w100 ptb15 plr15 tc bgWhite" @click.stop>
				<image :src="pay_voucher" mode="aspectFit" class="w100"></image>
			</view>
		</view>
		<!-- 取消交易弹窗 -->
		<uni-popup ref="ceil" :type="type" :custom="true">
			<view class="uni-tip" v-if="payType=='ceil'">	<!-- 取消交易弹窗 -->
				<view class="uni-tip-title">{{$t('store.cancel_confrim')}}</view>
				<view class="uni-tip-content flex column white ft12">
					<text class="blue21">{{$t('store.t_tip1')}}</text>
					<text class="tl mt5">{{$t('store.t_rule')}}:{{$t('store.t_tip2')}}。</text>
					<!-- <view class="mt5 flex alcenter">
						<checkbox value="cb" :checked="is_ok" @tap="tapChecked" style="transform:scale(0.7);color:'#1881d2'" />
						<text>我确认还没有付款给对方</text>
					</view> -->
				</view>
				<view class="uni-tip-group-button">
					<view class="uni-tip-button white" @click="cancel('ceil')">{{$t('store.ithink')}}</view>
					<view class="uni-tip-button blue21" @click="submit">{{$t('login.e_confrim2')}}</view>
				</view>
			</view>
			<!-- 延期确认弹窗 -->
			<view class="uni-tip" v-if="payType=='depay'">	
				<view class="uni-tip-title">延期确认</view>
				<view class="uni-tip-content white tc ft12">
					<text class="blue21">如已收到买家打款请不要点击确认按钮。</text>
					<view class="mt5">{{$t('store.lockacc')}}。</view>
				</view>
				<view class="uni-tip-group-button">
					<view class="uni-tip-button white" @click="cancel('ceil')">{{$t('trade.cancel')}}</view>
					<view class="uni-tip-button blue21" @click="submit">{{$t('login.e_confrim2')}}</view>
				</view>
			</view>
			<!-- 申请维权弹窗 -->
			<view class="uni-tip" v-if="payType=='wepay'">	
				<view class="uni-tip-title">申请维权</view>
				<view class="uni-tip-content white tc ft12">
					<text class="blue21">如已收到买家打款请不要点击确认按钮。</text>
					<view class="mt5">{{$t('store.lockacc')}}。</view>
				</view>
				<view class="uni-tip-group-button">
					<view class="uni-tip-button white" @click="cancel('ceil')">{{$t('trade.cancel')}}</view>
					<view class="uni-tip-button blue21" @click="submit">{{$t('login.e_confrim2')}}</view>
				</view>
			</view>
			<!-- 确认付款弹窗 -->
			<view class="uni-tip" v-if="payType=='ipay'">	
				<view class="uni-tip-title">{{$t('store.confrim_ipay')}}</view>
				<view class="uni-tip-content white tc ft12">
					<text class="blue21">{{$t('store.donotconfrim')}}。</text>
					<view class="mt5">{{$t('store.lockacc')}}。</view>
				</view>
				<view class="uni-tip-group-button">
					<view class="uni-tip-button white" @click="cancel('ceil')">{{$t('trade.cancel')}}</view>
					<view class="uni-tip-button blue21" @click="submit">{{$t('login.e_confrim2')}}</view>
				</view>
			</view>
			<!-- 确认收款弹窗 -->
			<view class="uni-tip" v-if="payType=='fpay'">	
				<view class="uni-tip-title">{{$t('store.t_con_collect')}}</view>
				<view class="uni-tip-content white tc ft12">
					<text class="blue21">{{$t('store.notpayconfrim')}}。</text>
					<view class="mt5">{{$t('store.hedui')}}。</view>
				</view>
				<view class="uni-tip-group-button">
					<view class="uni-tip-button white" @click="cancel('ceil')">{{$t('trade.cancel')}}</view>
					<view class="uni-tip-button blue21" @click="submit">{{$t('login.e_confrim')}}</view>
				</view>
			</view>
		</uni-popup>
		<!-- 底部密码弹窗 -->
		<uni-popup ref="share" :type="type" :custom="true">
			<view class="uni-share bgPart">
				<view class="uni-share-content">
					<view class="uni-share-title tc h40 lh40 bdb_1e ft16">{{$t('login.s_dealpwd')}}</view>
					<view class="flex alcenter ptb20 plr20">
						<text>{{$t('login.s_dealpwd')}}</text>
						<input type="password" class="h40 flex1 plr15 ml10 bd_input radius2" v-model="password" :placeholder="$t('login.e_pdeal')" />
					</view>
					<view class="bgBlue tc h40 lh40 white" @click="submit">{{$t('login.e_confrim')}}</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import {domain} from '@/common/domain.js'
	import uniPopup from '@/components/uni-popup3.vue';
	import uniCollapse from '@/components/uni-collapse.vue'
	import uniCollapseItem from '@/components/uni-collapse-item.vue'
	import uniSteps from '@/components/uni-steps.vue'
	import {mapState} from 'vuex'
	export default {
		components: {
			uniPopup,
			uniCollapse,
			uniCollapseItem,
			uniSteps
		},
		data() {
			return {
				id: '',
				password:'',
				show: false,
				type: '',
				pay_code: false,
				is_ok: false,
				data: {},
				cash: {},
				user_cash:{},
				payType:'',
				pay_voucher:'',
				pay_img:false,
				code_img:'',
				timer:'',
				setTimer:null,
				list: []
			};
		},
		onLoad(e) {
			this.id = e.id;

		},
		onShow() {
			this.$utils.setThemeTop(this.theme)
			this.getDetail();
			this.getActions();
		},
		computed:{
		   ...mapState(['theme']),
		  },
		methods: {
			back() {
				uni.navigateBack();
			},
			iSure(){
				this.cancel('ceil');
				this.togglePopup('bottom', 'share');
			},
			checkTime(i){
				if (i<10) {
					i = "0" + i;
				}
				return i;
			},
			downTime(startTime,endTime){//倒计时
				let that = this;
				//var startTime=(Date.parse(new Date())/1000);//开始时间
				var startTime = new Date(startTime); //开始时间
				var endTime = new Date(endTime); //结束时间
				startTime = (startTime.getTime()/1000);
				endTime = (endTime.getTime()/1000);
				if(endTime-startTime<=0){
					return;
				}
				that.setTimer=setInterval(function(){
					var ts =endTime-startTime;//计算剩余的毫秒数
					console.log(ts)
					//var dd = parseInt(ts  / 60 / 60 / 24, 10);//计算剩余的天数
					var hh = parseInt(ts  / 60 / 60 % 24, 10);//计算剩余的小时数
					var mm = parseInt(ts  / 60 % 60, 10);//计算剩余的分钟数
					var ss = parseInt(ts  % 60, 10);//计算剩余的秒数
					//dd = that.checkTime(dd);
					hh = that.checkTime(hh);
					mm = that.checkTime(mm);
					ss = that.checkTime(ss);
					if(ts>0){
						if(hh>0){
							that.timer =  hh + "时" + mm + "分" + ss + "秒";
						}else{
							that.timer = mm + "分" + ss + "秒";
						}
						startTime++;
					}else if(ts<=0){
						that.timer="00:00";
						clearInterval(that.setTimer);
						that.getDetail();
					}
				},1000);
			},
			getDetail() { //详情
				this.$utils.initDataToken({url: 'otc/detail',data: {detail_id: this.id}}, res => {
					let way = res.way;
					res.way = way == 'BUY'?'SELL':'BUY';
					this.data = res;
					this.pay_voucher = res.pay_voucher;
					if((res.confirm_countdown==null) && res.status==0 && this.data.way=="BUY"){//买家要付款
						this.downTime(res.server_now,res.pay_countdown);
					}else if((res.status==1 || res.status==2) && this.data.way=="SELL"){//卖家该收款
						this.downTime(res.server_now,res.confirm_countdown);
					}
				});
			},
			getActions(){//订单状态追踪
				this.$utils.initDataToken({url: 'otc/detail_actions',data: {detail_id: this.id}}, res => {
					this.list = res;
				});
			},
			sendUrl(url){
				let that = this;
				let data={
					detail_id: this.id
				}
				if(url=='otc/pay'){//付款
					if(!that.pay_voucher){
						return that.$utils.showLayer('请先上传支付凭证');
					}
					data.pay_voucher=that.pay_voucher;
				}
				that.$utils.initDataToken({url: url,data: data,type: 'post'}, res => {
					that.password='';
					this.cancel('ceil');
					that.cancel('share');
					that.$utils.showLayer(res);
					setTimeout(() => {
						that.getDetail();
					}, 1000)
				});
			},
			submit(){
				let that = this;
				// if(that.password.length<6){
				// 	return that.$utils.showLayer(that.$t('login.e_pdeal'));
				// }
				if(that.payType=='ceil'){//取消
				    that.sendUrl('otc/cancel');
				}
				if(that.payType=='ipay'){ //付款
					that.sendUrl('otc/pay');
				}
				if(that.payType=='depay'){//延期确认
				    that.sendUrl('otc/defer');
				}
				if(that.payType=='wepay'){//维权
				    that.sendUrl('otc/arbitrate');
				}
				if(that.payType=='fpay'){//收款
					that.sendUrl('otc/confirm');
				}
			},
			call() { //打电话
			    let num = this.data.way=='BUY'?this.data.sell_user_account : this.data.buy_user_account;
			    console.log(num)
				uni.makePhoneCall({
					phoneNumber: num
				});
			},
			copy(text) { //复制
				let that = this;
				uni.setClipboardData({
					data: text,
					success: function() {
						that.$utils.showLayer(that.$t('assets.copy_success'));
					}
				});
			},
			isshow(code) {//二维码弹窗
			    if(code){
					console.log(code)
					this.code_img = code;
				}
				this.pay_code = !this.pay_code;
			},
			togglePopup(type, open) {//type弹窗位置
				this.type = type;
				if (open === 'tip') {
					this.show = true;
				} else {
					this.$refs[open].open();
				}
			},
			cancel(type) {
				if (type === 'tip') {
					return this.show = false;
				}
				this.$refs[type].close();
			},
			tapChecked() {
				this.is_ok = !this.is_ok;
			},
			apply(){
				let that = this;
				that.$utils.initDataToken({
					url: 'legal/arbitrate',
					data:{
					  id :that.id
					},
					type: 'post',
				}, res => {
					that.$utils.showLayer(res);
					setTimeout(()=>{
						that.getDetail();
					},1000)
				});
			},
			uploadImg(){
				var that=this;
				uni.chooseImage({
					count: 1,
					sizeType: ['compressed'],
					success: (chooseImageRes) => {
						const tempFilePaths = chooseImageRes.tempFilePaths;
						uni.uploadFile({
							url: '/api/common/image_upload', //仅为示例，非真实的接口地址
							// #ifdef APP-PLUS
							url:domain+'/api/common/image_upload',
							// #endif
							filePath: tempFilePaths[0],
							name: 'file',
							formData: {
								'user': 'test'
							},
							success: (uploadFileRes) => {
								var data=JSON.parse(uploadFileRes.data);
								if(data.code==1){
									that.pay_voucher = data.data.url;
								}
							}
						});
					}
				});
			},
		}
	};
</script>

<style>
	page {
		background-color: #131f30;
	}
	.info .infos {
		border-bottom: 1px solid #263243;
	}
	.pay_order {
		background-color: #152a42;
		z-index: 11;
	}
	/* 付款码弹窗 */
	.sx_show {
		position: fixed;
		top: calc(var(--status-bar-height) + 100upx);
		width: 100%;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, .7);
		z-index: 102;
	}

	.sx_show .xian {
		height: 40upx;
		width: 4upx;
	}

	/* 取消交易弹窗 */
	.uni-tip {
		padding: 30upx;
		width: 600upx;
		background: #131e34;
		box-sizing: border-box;
		border-radius: 20upx;
	}

	.uni-tip-title {
		text-align: center;
		font-weight: bold;
		font-size: 32upx;
		color: #fff;
	}

	.uni-tip-content {
		padding: 30upx;
	}

	.uni-tip-group-button {
		margin-top: 20upx;
		display: flex;
	}

	.uni-tip-button {
		width: 100%;
		text-align: center;
		font-size: 28upx;
	}

	.center-box {
		width: 500upx;
		height: 500upx;
	}

	.center-box .image {
		width: 100%;
		height: 100%;
	}
</style>

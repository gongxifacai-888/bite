<template>
	<view class="plr20 " :class="{'light':theme=='light'}">
		<!-- 支付宝 -->
		<view class="" v-if="types=='alipay'">
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.name')}}：</text>
				<input type="text"  v-model="name" class="h40 lh40 flex1 pr10 tr " :placeholder="$t('collect.p_name')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.alipay')}}：</text>
				<input type="text"  v-model="alipay_account" class="h40 lh40 flex1 pr10 tr " :placeholder="$t('collect.p_alipay')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.alipayCode')}}：</text>
				<image :src="alipay_code" mode="aspectFit" class="wt100 h100" @tap="uploadImg('alipay')"></image>
			</view>
		</view>
		<!-- 微信 -->
		<view class="" v-else-if="types=='wxpay'">
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.name')}}：</text>
				<input type="text"  v-model="name" class="h40 lh40 flex1 pr10 tr " :placeholder="$t('collect.p_name')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.wechat')}}：</text>
				<input type="text"  v-model="wechat_account" class="h40 lh40 pr10 tr flex1" :placeholder="$t('collect.p_wechat')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10">
				<text class="tr">{{$t('collect.wechatCode')}}：</text>
				<image :src="wechat_code" mode="aspectFit" class="wt100 h100" @tap="uploadImg('wechat')"></image>
			</view>
		</view>
		<!-- 银行卡 -->
		<view class="" v-else>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.name')}}：</text>
				<input type="text"  v-model="name" class="h40 lh40 flex1 pr10 tr " :placeholder="$t('collect.p_name')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.bank')}}：</text>
				<input type="text"  v-model="kaihu" class="h40 lh40 flex1 pr10 tr " :placeholder="$t('collect.p_bank')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">{{$t('collect.no')}}：</text>
				<input type="text"  v-model="card_no" class="h40 lh40 flex1 pr10 tr " :placeholder="$t('collect.p_no')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text class="tr">开户支行：</text>
				<input type="text"  v-model="bank_adress" class="h40 lh40 flex1 pr10 tr " placeholder="请输入开户支行名称">
			</view>
		</view>
		<view class="mt30 h40 lh40 tc white bgDark radius28 ft14" @tap="confirm">{{$t('login.e_confrim')}}</view>
	</view>
</template>

<script>
	import {domain} from '@/common/domain.js'
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				name:'',
				mobile:'',
				kaihu:'',
				card_no:'',
				bank_adress:'',
				wechat_account:'',
				alipay_account:'',
				wechat_code:'/static/image/addImg.png',
				alipay_code:'/static/image/addImg.png',
				weichat_name:'',
				typeList:[],
				imgSrc:domain,
				ids:'',
				types:''
			}
		},
		onLoad(e) {
			this.ids = e.id;
			this.types = e.types;
			this.getInfo();
			uni.setNavigationBarTitle({
				title:this.$t('collect').method
			})
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
		   this.$utils.setThemeTop(this.theme)
		},
		methods:{
			//获取收款信息
			getInfo(){
				var that = this;
				that.$utils.initDataToken({url:'pay_method/get_method_by_type',type:'GET',data:{type:that.ids}},(res,msg)=>{
					if(that.types=='alipay'){
						that.name = res.raw_data.real_name;
						that.alipay_account = res.raw_data.account;
						that.alipay_code = res.raw_data.qrcode;
					}else if(that.types=='wxpay'){
						that.name = res.raw_data.real_name;
						that.wechat_account = res.raw_data.account;
						that.wechat_code = res.raw_data.qrcode;
					}else{
						that.name = res.raw_data.real_name;
						that.kaihu = res.raw_data.bank_name;
						that.card_no = res.raw_data.card_no;
						that.bank_adress = res.raw_data.bank_address;
					}
				})
				
			},
			uploadImg(type){
				console.log(domain);
				var that=this;
				uni.chooseImage({
					count: 1,
					// sizeType: ['original', 'compressed'],
					sizeType: [ 'compressed'],
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
								console.log(typeof uploadFileRes.data);
								var data=JSON.parse(uploadFileRes.data);
								if(data.code==1){
									if(type=='wechat'){
										that.wechat_code=data.data.url;
									}else{
										that.alipay_code=data.data.url;
									}
								}
							}
						});
					}
				});
			},
			confirm(){
				var that = this;
				var datas = '';
				var obj={};
				if(that.types=='alipay'){
					if(!that.name){
						return that.$utils.showLayer(that.$t('collect.p_name'))
					}
					if(!that.alipay_account){
						return that.$utils.showLayer(that.$t('collect.p_alipay'))
					}
					if(that.alipay_code=='/static/image/addImg.png'){
						return that.$utils.showLayer(that.$t('collect.up_alipayCode'))
					}
					obj.real_name=that.name;
					obj.account = that.alipay_account;
					obj.qrcode = that.alipay_code;
				}else if(that.types=='wxpay'){
					if(!that.name){
						return that.$utils.showLayer(that.$t('collect.p_name'))
					}
					if(!that.wechat_account){
						return that.$utils.showLayer(that.$t('collect.p_wechat'))
					}
					if(that.wechat_code=='/static/image/addImg.png'){
						return that.$utils.showLayer(that.$t('collect.up_wechatCode'))
					}
					obj.real_name=that.name;
					obj.account = that.wechat_account;
					obj.qrcode = that.wechat_code;
				}else{
					if(!that.name){
						return that.$utils.showLayer(that.$t('collect.p_name'))
					}
					if(!that.kaihu){
						return that.$utils.showLayer(that.$t('collect.p_bank'))
					}
					if(!that.card_no){
						return that.$utils.showLayer(that.$t('collect.p_no'))
					}
					if(!that.bank_adress){
						return that.$utils.showLayer('请输入开户支行名称')
					}
					obj.real_name=that.name;
					obj.card_no = that.card_no;
					obj.bank_name = that.kaihu;
					obj.bank_address = that.bank_adress;
				}
				datas=JSON.stringify(obj)
				that.$utils.initDataToken({url:'pay_method/save',type:'POST',data:{
					type:that.ids,
					raw_data:obj
					// real_name:this.name,
					// bank_name:this.kaihu,
					// bank_account:this.card_no,
					// alipay_account:this.alipay_account,
					// wechat_nickname:this.weichat_name,
					// wechat_account:this.wechat_account,
					// wechat_collect:this.wechat_code,
					// alipay_collect:this.alipay_code,
				}},(res,msg)=>{
					this.$utils.showLayer(msg);
					setTimeout(()=>{
						// uni.navigateBack({
						// 	delta: 1
						// });
					},1500)
				})
			}
		}
	}
</script>

<style>
</style>

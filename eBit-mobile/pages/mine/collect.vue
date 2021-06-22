<template>
	<view :class="{'light':theme=='light'}">
			<navigator :url="'/pages/mine/collect_money?id='+item.id+'&types='+item.code" v-for="item in typeList" :key="item.id" class="ptb20 ft14 flex alcenter between plr20 bdb_blue3">
				<view class="flex alcenter">
					<image src="../../static/image/zhi.png" mode="aspectFit" class="wt15 h15 mr10" v-if="item.code=='alipay'"></image>
					<image src="../../static/image/wechat.png" mode="aspectFit" class="wt15 h15 mr10" v-else-if="item.code=='wxpay'"></image>
					<image src="../../static/image/card.png" mode="aspectFit" class="wt15 h15 mr10" v-else></image>
					<span>{{item.name}}</span>
				</view>
				<span class="flex alcenter"><image src="../../static/image/mores.png" class="wt15 h15 ml10"></image></span>
			</navigator>
			
		

	</view>
</template>

<script>
	import {domain} from '@/common/domain.js'
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				typeList:[],
				
			}
		},
		onLoad() {
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
				that.$utils.initDataToken({url:'pay_method/types',type:'GET',data:{}},(res,msg)=>{
					that.typeList = res;
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
				if(!this.name){
					return this.$utils.showLayer(this.$t('collect.p_name'))
				}
				// if(!this.mobile){
				// 	return this.$utils.showLayer('请输入手机号')
				// }
				if(!this.kaihu){
					return this.$utils.showLayer(this.$t('collect.p_bank'))
				}
				if(!this.card_no){
					return this.$utils.showLayer(this.$t('collect.p_no'))
				}
				
				if(!this.alipay_account){
					return this.$utils.showLayer(this.$t('collect.p_alipay'))
				}
				if(!this.weichat_name){
					return this.$utils.showLayer(this.$t('collect.p_nick'))
				}
				if(!this.wechat_account){
					return this.$utils.showLayer(this.$t('collect.p_wechat'))
				}
				if(this.wechat_code=='/static/image/addImg.png'){
					return this.$utils.showLayer(this.$t('collect.up_wechatCode'))
				}
				if(this.alipay_code=='/static/image/addImg.png'){
					return this.$utils.showLayer(this.$t('collect.up_alipayCode'))
				}
				this.$utils.initDataToken({url:'pay_method/save',type:'POST',data:{
					real_name:this.name,
					bank_name:this.kaihu,
					bank_account:this.card_no,
					alipay_account:this.alipay_account,
					wechat_nickname:this.weichat_name,
					wechat_account:this.wechat_account,
					wechat_collect:this.wechat_code,
					alipay_collect:this.alipay_code,
				}},(res,msg)=>{
					this.$utils.showLayer(msg);
					setTimeout(()=>{
						uni.navigateBack({
							delta: 1
						});
					},1500)
				})
			}
		}
	}
</script>

<style>
</style>

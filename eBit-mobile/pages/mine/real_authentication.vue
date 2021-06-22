<template>
	<view class="plr20 " :class="{'light':theme=='light'}">
		<view class="" v-if="authStatus==0">
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text>{{$t('authentication.name')}}：</text>
				<input type="text"  v-model="name" class="h40 lh40 pr10 tr flex1" :placeholder="$t('collect.p_name')">
			</view>
			<view class="flex bgwhite alcenter radius4 pl10 mt10 bdb_blue3">
				<text>{{$t('collect.cardno')}}：</text>
				<input type="text"  v-model="card_id" class="h40 lh40 pr10 tr flex1" :placeholder="$t('collect.p_cardno')">
			</view>
			<view class="mt10">{{$t('collect.up_card')}}</view>
			<view class="flex mt10 mywrap between">
				<view class="w48 ptb5 plr5 bd_dashed radius4 tc mb10" @tap="uploadImg(1)">
					<view class="" v-if="!hasUp1">
						<image :src="img" class="wt80 h80" ></image>
						<view class="mt10 tc">{{$t('collect.up_cardz')}}</view>
					</view>
					<image :src="img1" class="w95" mode="widthFix" v-else style="max-height: 100px;"></image>
				</view>
				<view class="w48 ptb5 plr5 bd_dashed radius4 tc mb10" @tap="uploadImg(2)">
					<view class="" v-if="!hasUp2">
						<image :src="img" class="wt80 h80" ></image>
						<view class="mt10 tc">{{$t('collect.up_cardf')}}</view>
					</view>
					<image :src="img2" class="w95 " mode="widthFix" v-else style="max-height: 100px;"></image>
				</view>
				<view class="w48 ptb5 plr5 bd_dashed radius4 tc mb10" @tap="uploadImg(3)">
					<view class="" v-if="!hasUp3">
						<image :src="img" class="wt80 h80" ></image>
						<view class="mt10 tc">{{$t('collect.up_cardhand')}}</view>
					</view>
					<image :src="img3" class="w95" mode="widthFix" v-else style="max-height: 100px;"></image>
				</view>
				
			</view>
			<view class="mt30 h40 lh40 tc white bgBlue radius28 ft14" @tap="confirm">{{$t('login.e_confrim')}}</view>
		</view>
		<view class="ft20 tc pt100" v-if="authStatus==1">
			审核中
		</view>
		<view class="ft20 tc pt100" v-if="authStatus==2">
			已认证
		</view>
	</view>
</template>

<script>
	import {domain} from '@/common/domain.js'
	import {mapState} from 'vuex'
	export default{
		data(){
			return{
				name:'',
				card_id:'',
				img:'/static/image/addImg.png',
				hasUp1:false,
				hasUp2:false,
				hasUp3:false,
				img1:'',
				img2:'',
				img3:'',
				authStatus: 0,
			}
		},
		onLoad() {
			uni.setNavigationBarTitle({
				title:this.$t('authentication').renzheng
			})
		},
		computed:{
		   ...mapState(['theme']),
		},
		onShow() {
		    this.$utils.setThemeTop(this.theme);
			this.init();
		},
		methods:{
			init(){
				var that = this;
				that.$utils.initDataToken({url:'user_real/center'},(res,msg)=>{
					that.authStatus = res.review_status;
				})
			},
			uploadImg(i){
				console.log(domain);
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
								console.log(typeof uploadFileRes.data);
								var data=JSON.parse(uploadFileRes.data);
								if(data.code==1){
									var img='img'+i;
									var hsup='hasUp'+i;
									console.log(data)
									that[img]=data.data.url;
									that[hsup]=true;
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
				if(!this.card_id){
					return this.$utils.showLayer(this.$t('collect.p_cardno'))
				}
				if(!this.img1){
					return this.$utils.showLayer(this.$t('collect.up_cardz'))
				}
				if(!this.img2){
					return this.$utils.showLayer(this.$t('collect.up_cardf'))
				}
				if(!this.img3){
					return this.$utils.showLayer(this.$t('collect.up_cardhand'))
				}
				this.$utils.initDataToken({url:'user_real/real_name',type:'POST',data:{
					name:this.name,
					card_id:this.card_id,
					front_pic:this.img1,
					reverse_pic:this.img2,
					hand_pic:this.img3,
				}},(res,msg)=>{
					this.$utils.showLayer(msg);
					setTimeout(()=>{
						uni.navigateBack({
							delta:1
						})
					},1500)
				})
			}
		}
	}
</script>

<style>
</style>

<template>
	<view :class="{'light':theme=='light'}">
		<view class="status_bar"><view class="top_view"></view></view>
		<view class="ptb10 w100 tc flex alcenter between fixed bgPart head pr10">
			<view class="pl10"><image  @click="back" class="wt20 h20" src="/static/image/return.png" mode="widthFix"></image></view>
			<view class="ft16 bold">
				{{$t('feed.feed')}}
			</view>
			<navigator url="/pages/mine/record" class="ft12">
				{{$t('assets.record')}}
			</navigator>
		</view>
		<view class="plr15 pt50">
			<view class="flex alcenter mt10 plr15 h40 bgPart between" @tap="actionSheetTap">
				<view class="">{{$t('feed.type')}}</view>
				<view class="flex alcenter">
					<text class="plr10" v-if="typeList[index]">{{typeList[index].name}}</text>
				    <uni-icons type="arrowdown" color="#ddd" size="20" />
				</view>
			</view>
			<view class="ptb10 ft13 mt15">{{$t('feed.titel')}}</view>
			<input type="text" value="" class="h40 plr20 bgPart w100" :placeholder="$t('feed.p_title')" v-model="title" />
			<view class="ptb10 ft13 mt15">{{$t('feed.content')}}</view>
			<view class="ptb20 bgPart">
				<textarea class="ft14 plr15" value="" :placeholder="$t('feed.min_content')" v-model="user_content" />
			</view>
			<!-- <view class="bgPart mt10 ptb20 plr20">
				<text>{{$t('wallet.printscreen')}}</text>
				<view class="bd_dashed wt70 h70 flex jscenter alcenter mt10">
					<image :src="user_image" mode="" class="wt70 h70" v-if="user_image" @click="upfile"></image>
					<uni-icons type="plusempty" color="#c7c7c7" size="70" v-else @click="upfile" />
				</view>
			</view> -->
			<view class="flex jscenter mt50">
				<button class="w90 bgBlue h45 white ft16 lh44" @click="confirm">{{$t('login.e_confrim')}}</button>
			</view>
		</view>
	</view>
</template>

<script>
	import {mapState} from 'vuex'
	export default {
		components:{
			// uniIcons
		},
		data() {
			return {
				title:'',
				user_content:'',
				user_image:'',
				index:0,
				listItem:[],
				typeList:[]
			}
		},
		onNavigationBarButtonTap(){
			uni.navigateTo({
				url:'./orderList'
			})
		},
		onShow() {
		   
		},
		onLoad() {
			this.$utils.setThemeTop(this.theme)//改变导航栏背景色
			// uni.setNavigationBarTitle({//翻译导航栏标题
			// 	title:'用户反馈'
			// })
			this.getType();
		},
		computed:{
		   ...mapState(['theme']),//获取当前UI颜色
		},
		methods: {
			back(){
				uni.navigateBack({
					delta: 1
				});
			},
			getType(){
				var that = this;
				that.$utils.initDataToken({url:'feedback/type',type:'GET'},(res,msg)=>{
					that.typeList = res;
					let arr = [];
					res.map(function(item){
						arr.push(item.name)
					})
					that.listItem = arr;
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
				if(!that.title){
					return this.$utils.showLayer(that.$t('feed.p_title'));
				}
				if(!that.user_content){
					return this.$utils.showLayer(that.$t('feed.p_content'));
				}
				this.$utils.initDataToken({
					url:'feedback/feedback',
					type:'post',
					data:{
						title:that.title,
						content:that.user_content,
						type_id:that.typeList[that.index].id
					}
				},function(res){
					that.title="",
					that.user_content="",
					uni.showModal({
						title: '',
						showCancel:false,
						content: that.$t('feed.ok'),
						success: function (res) {
							
						}
					});
				})
		    },
			upfile(){//上传凭证
				var that=this;
				uni.chooseImage({
					count: 1,
					sizeType: ['compressed'],
					success: (chooseImageRes) => {
						const tempFilePaths = chooseImageRes.tempFilePaths;
						uni.uploadFile({
							url: '/api/upload', //仅为示例，非真实的接口地址
							// #ifdef APP-PLUS
							url:domain+'/api/upload',
							// #endif
							filePath: tempFilePaths[0],
							name: 'file',
							formData: {
								'user': 'test'
							},
							success: (uploadFileRes) => {
								var data=JSON.parse(uploadFileRes.data);
								if(data.type==1){
									that.user_image='http://' + data.data.domain+data.data.file_url;
								}else{
									return that.$utils.showLayer('上传失败')
								}
							}
						});
					}
				});
			},
		}
	}
</script>

<style>
	.head {
		top: var(--status-bar-height);
		left: 0;
		z-index: 999;
	}
</style>
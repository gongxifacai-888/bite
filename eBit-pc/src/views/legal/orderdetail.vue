<template>
    <div class=" overx pt20">
        <div class="w60 mauto bgheader pb10 bdbfoptdashed25 posRelt radius6 ">
            <div class="ft16 flex alcenter between  ptb10 plr20 ">
                <div class="flex1 white">
                    <div class="mb20 ft24 bold flex alcenter">
                        <img src="../../assets/images/nock.png" alt="" class="wt30">
                        <!-- 0 交易中 1已付款 2延期确认 3维权 4取消 5确认 -->
						<span class="nwhite" v-if="data.status === 0 && data.way=='BUY'">{{$t('store.p_pay')}}</span>
						<span  class="nwhite" v-if="data.status === 0 && data.way=='SELL'">{{$t('store.p_waitpay')}}</span>
						<span  class="nwhite" v-if="data.status == 1">{{$t('store.has_pay')}}</span>
						<span  class="nwhite" v-if="data.status == 2">{{$t('store.delay')}}</span>
						<span  class="nwhite" v-if="data.status == 3">{{$t('store.protection0')}}</span>
						<span  class="nwhite" v-if="data.status == 4">{{$t('store.has_cancel')}}</span>
						<span  class="nwhite" v-if="data.status === 5">{{$t('store.done')}}</span>
                    </div>
                </div>
                <div class="pointer nwhite tr" @click="ishowcontact=true">
                    <img src="../../assets/images/contact.png" alt="" class="wt30">
                    <p>{{$t('store.call')}}</p>
                </div>
            </div>
            <div class="baselight2 plr20">
                <p @click="getDetail" :class="['mb20 green ft16',{'red':data.way=='SELL'}]">{{data.way=='BUY'?$t('legal.buy'):$t('legal.sell')}}{{data.currency_name}}</p>
                <p class="mb20 ft18 blue" v-if="data.area_info">{{data.amount}} {{data.area_info.currency}}</p>
                <p class="mb20">{{$t('legal.num')}}：{{data.number}}</p>
                <p class="mb20" v-if="data.area_info">{{$t('legal.price')}}：{{data.price}} {{data.area_info.currency}}</p>
            </div>
            <div class="wt15 ht15 radius50p bgpart abstort" style="left:-5px;bottom:-7.5px"></div>
            <div class="wt15 ht15 radius50p bgpart abstort" style="right:-5px;bottom:-7.5px"></div>
        </div>
         <div class=" pb20">
            <div class="ptb20 plr20 bgheader w60 mauto  radius6 ">
                <p class="baselight2 mb20 ft14" v-if="data.way=='BUY'">{{$t('store.following')}}</p>
                <p class="baselight2 mb20 ft14" v-else>{{$t('store.collection')}}</p>
                <div class="ft14">
                    <div class="bdbfoptdashed25 ptb10" v-for="(item,index) in data.otc_payways" :key="index">
                        <!-- <div class="mb20 flex alcenter between">
                            <span>支付方式</span>
                            <div class="flex alcenter  between">
                                <img src="../../assets/images/alipay.png" alt="" class="wt20" v-if="item.pay_code=='alipay'">
                                <img src="../../assets/images/wxpay.png" alt="" class="wt20" v-else-if="item.pay_code=='wxpay'">
                                <img src="../../assets/images/bank.png" alt="" class="wt20" v-else>
                                <span class="ml5 baselight2">{{item.payway.name}}</span>
                            </div>
                        </div> -->
                        <div class="flex between alcenter mb20">
                            <div class="flex alcenter">
                                <p class="bdline ptb2 plr5">
                                    <span :class="[{'blue':item.pay_code=='alipay','green':item.pay_code=='wxpay','yellow':item.pay_code=='bank'}]">{{item.payway.name}}</span>
                                </p>
                                <p class="ml10">
                                    <span class="nwhite" v-if="item.pay_code!='bank'">{{item.raw_data.account}}</span>
                                    <span class="nwhite" v-else>{{item.raw_data.card_no}}</span>
                                </p>
                            </div>
                            <div class="ft12 pointer flex alcenter" v-if="item.pay_code!='bank'">
                                <span class="mr5 blue">{{$t('store.qrCode')}}</span>
                                <div class="demo-image__preview wt20 ht20">
                                    <el-image 
                                        style="width: 20px; height: 20px"
                                        :src="item.raw_data.qrcode" 
                                        :preview-src-list="item.pay_qrcode">
                                    </el-image>
                                </div>
                            </div>
                        </div>
                        <div class="mb20 flex alcenter between" v-if="item.pay_code=='bank'">
                            <span>{{$t('store.collectper')}}</span>
                            <span class="nwhite">{{item.raw_data.real_name}}</span>
                        </div>
                        <div class="mb20 flex alcenter between" v-if="item.pay_code=='bank'">
                            <span>{{$t('store.bankName')}}</span>
                            <span class="nwhite">{{item.raw_data.bank_name}}</span>
                        </div>
                        <div class="mb20 flex alcenter between" v-if="item.pay_code=='bank'">
                            <span>{{$t('store.bankInfo')}}</span>
                            <span class="nwhite">{{item.raw_data.bank_address}}</span>
                        </div>
                    </div>
                </div>
                <div class="ptb10 bdbfoptdashed25 flex alcenter between" v-if="data.status!=4">
                    <span>{{$t('store.voucher')}}</span>
                    <p class="pointer bgblue nwhite wt60 ht20 lh20 tc radius4 posRelt" v-if="(data.way=='BUY')&&(pay_voucher=='')">
                        <span>{{$t('store.upload')}}</span> 
                        <input type="file" class="wt60 ht20 lh20 abstort lf0 btm0 opacity0 pointer" accept="image/*" name="file" @change="uploadImg"/>
                    </p>
                   <div class="demo-image__preview" v-if="pay_voucher!=''">
                        <el-image 
                            style="width: 50px; height: auto"
                            :src="pay_voucher" 
                            :preview-src-list="voucherList">
                        </el-image>
                    </div>
                    <p v-if="data.way=='SELL'&&pay_voucher==''">{{$t('store.nos')}}</p>
                </div>
                <el-collapse accordion>
                    <el-collapse-item :title="$t('store.tracking')" name="1">
                        <el-timeline :reverse="false">
                            <el-timeline-item
                                v-for="(activity, index) in activities"
                                :key="index"
                                :timestamp="activity.created_at">
                                {{activity.memo}}
                            </el-timeline-item>
                        </el-timeline>
                    </el-collapse-item>
                </el-collapse>
                 <div class="mt10 ft14">
                    <div class="">
                         <div class="mb20 flex alcenter between">
                            <span>{{$t('store.paycancode')}}</span>
                            <span class="nwhite">{{data.id}}</span>
                        </div>
                        <div class="mb20 flex alcenter between">
                            <span>{{$t('store.times')}}</span>
                            <span class="nwhite">{{data.created_at}}</span>
                        </div>
                    </div>
                    <div class="tips mb20" v-if="data.status==0 && data.way=='BUY'">*{{$t('store.t_tip')}}</div>
                    <div class="mt20 flex between alcenter">
                        <!-- 0 交易中 1已付款 2延期确认 3维权 4取消 5确认 -->
                        <div class="" >
                            <p class="ft16 baselight2 mb20" v-if="data.status === 0 && data.way=='SELL'">{{$t('store.waitingPay')}}</p>
                            <p class="ft16 baselight2 mb20" v-if="data.status === 0 && data.way=='BUY'">{{$t('store.please')}} <span class="red">{{timer}}</span>{{$t('store.timeIn')}}</p>
                            <p class="ft16 baselight2 mb20" v-if="(data.status === 1 || data.status == 2) && data.way=='SELL'">{{$t('store.please')}} <span class="red">{{timer}}</span>{{$t('store.completeIn')}}</p>
                            <p class="ft16 baselight2 mb20" v-if="data.status === 1 && data.way=='BUY'">{{$t('store.waitingConfirm')}}</p>
                            <p class="ft16 baselight2 mb20" v-if="data.status == 2">{{$t('store.postponed')}}</p>
                            <p class="ft16 baselight2 mb20" v-if="data.status == 3">{{$t('store.protection')}}</p>
                            <!-- <p class="ft16 baselight2 mb20" v-if="data.status == 4">订单已取消</p>
                            <p class="ft16 baselight2 mb20" v-if="data.status == 5">订单已完成</p> -->
                            <p class="" v-if="(data.status === 0) || (data.status === 1)">
                                <span class="green ft20">{{data.amount}} </span>
                                <span v-if="data.area_info">{{data.area_info.currency}}</span>
                            </p>
                        </div>
                        <div class="" v-if="(data.status==0 || data.status==2 || data.status==3) && data.way=='BUY'">
                            <div class="bgshadow ht48 lh48 tc white radius2 plr50  pointer" @click="doOrder('cancel')">{{$t('store.cancelorder')}}</div>
                            <div v-if="data.status==0" class="bgblue ht48 lh48 tc white radius2 plr50 mt20 pointer" @click="doOrder('pay')">{{$t('store.ipay')}}</div>
                        </div>
                        <div class="" v-if="(data.status==1 || data.status==2 || data.status==3) && data.way=='SELL'">
                            <!-- 1已付款 2延期确认 3维权 -->
                            <div class="bgshadow ht48 lh48 tc white radius2 plr50  pointer" v-if="data.status!=2" @click="doOrder('defer')">{{$t('store.delay')}}</div>
                            <div class="bgred ht48 lh48 tc white radius2 plr50 mt20 pointer" @click="doOrder('arbitrate')">{{$t('store.apply')}}</div>
                            <div class="bgblue ht48 lh48 tc white radius2 plr50 mt20 pointer" @click="doOrder('confirm')">{{$t('store.t_con_collect')}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 确认弹框 -->
        <el-dialog
            :title="title"
            :visible.sync="showLayer"
            width="30%"
            :before-close="handleClose">
            <div class="ft14" v-if="url=='cancel'"> <!--//取消交易弹框 -->
               <p class="mb20 blue">{{$t('store.t_tip1')}}</p>
            </div> 
            <div class="ft14" v-if="url=='pay'">  <!-- 确认已付款弹框 -->
               <p class="mb20">{{$t('store.donotconfrim')}}</p>
               <p class="mb20 blue">{{$t('store.lockacc')}}</p>
            </div> 
            <span slot="footer" class="dialog-footer ">
                <el-button class="radius4" @click="showLayer=false">{{$t('legal.cannel')}}</el-button>
                <el-button class=" radius4" type="primary" @click="confirm" >{{$t('legal.e_confrim2')}}</el-button>
            </span>
        </el-dialog>
         <!-- 联系对方 -->
        <el-dialog
            :title="$t('store.call')"
            :visible.sync="ishowcontact"
            width="30%"
            :before-close="handleClose">
            <div class="ft14 ">
               <p class="mb20 tc">{{$t('store.questions')}}</p>
               <p class="mb20 blue tc ft28">{{data.way=='BUY'?data.sell_user_account:data.buy_user_account}}</p>
            </div> 
        </el-dialog>
        
    </div>
</template>
<script>
export default {
    data(){
        return {
            id:'',
            data:{},
            pay_voucher:'',
            voucherList:[],
            showLayer:false,
            ishowcontact:false,
            title:'',
            url:'',
            setTimer:null,
            timer:'--',
            activities: []
        }
    },
    created(){
        this.id = this.$route.query.id;
        this.getDetail();
        this.getActions();
    },
    beforeDestroy(){
        clearInterval(this.setTimer)
    },
    methods:{
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
                var ts = endTime-startTime;//计算剩余的毫秒数
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
                        that.timer =  hh + that.$t('store.hours') + mm + that.$t('store.minutes') + ss + that.$t('store.seconds');
                    }else{
                        that.timer = mm + that.$t('store.minutes') + ss + that.$t('store.seconds');
                    }
                    startTime++;
                }else if(ts<=0){
                    that.timer="00:00";
                    clearInterval(that.setTimer);
                    that.getDetail();
                }
            },1000);
        },
        getDetail(){//获取详情
            let that = this;
             clearInterval(that.setTimer);
            this.$http.initDataToken({url:'otc/detail',data:{detail_id:this.id}},false)
            .then(res=>{
                res.otc_payways.map(function(item,index){
                    let payways = [];
                    if(item.raw_data.qrcode){
                        payways.push(item.raw_data.qrcode)
                        return that.$set(item, 'pay_qrcode', payways)
                    }
                })
                this.data = res;
                this.pay_voucher = res.pay_voucher;
                this.voucherList=[];
                this.voucherList.push(res.pay_voucher);
                if((res.confirm_countdown==null) && res.status==0 && this.data.way=="BUY"){//买家要付款
                    this.downTime(res.server_now,res.pay_countdown);
                }else if((res.status==1 || res.status==2) && this.data.way=="SELL"){//卖家该收款
                    this.downTime(res.server_now,res.confirm_countdown);
                }
            })
        },
        getActions(){//状态跟踪
            let that = this;
            this.$http.initDataToken({url:'otc/detail_actions',data:{detail_id:this.id}},false)
            .then(res=>{
                this.activities = res;
            })
        },
        handleClose(){//关闭弹窗
            this.showLayer = false;
            this.ishowcontact = false;
        },
        doOrder(url){
            this.url = url;
            let title;
            switch (url) {
                case 'cancel':
                    title = this.$t('store.cancelorder');
                    break;
                case 'pay':
                    title = this.$t('store.confrim_ipay');
                    break;
                case 'defer':
                    title = this.$t('store.delay');
                    break;
                case 'arbitrate':
                    title = this.$t('store.apply');
                    break;
                case 'confirm':
                    title = this.$t('store.t_con_collect');
            } 
            this.title = title;
            this.showLayer=true;
        },
        // 确认
        confirm(){
            this.showLayer=false;
            let data={
                detail_id: this.id
            }
            if(this.url=='pay'){//付款
                if(!this.pay_voucher){
                    this.$message(this.$t('store.uploadPay'));
                    return false;
                }
                data.pay_voucher=this.pay_voucher;
            }
            this.$http.initDataToken({url:'otc/'+this.url,data:data,type:'post'},true,true)
            .then(res=>{
                this.getDetail();
            })
        },
        //   上传图片
        uploadImg(options) {
            var that = this;
            var datas = new FormData();
            datas.append("file", event.target.files[0]);
            that.$http.initDataToken({url: "common/image_upload",data: datas,type: "POST"},false,true,true)
            .then(res => {
                that.pay_voucher = res.url;
                this.voucherList[0]=that.pay_voucher;
            });
        },
    }
}
</script>
<style lang="" >
    .el-collapse-item__header{
        background: transparent;
    }
    .el-collapse{
        border-top: none;
        border-bottom: none;
    }
    .el-collapse-item__wrap{
        background: transparent;
        border-bottom: 1px dashed rgba(255,255,255,0.15);
    }
    .el-collapse-item__header{
        color: #61688a;
        border-bottom: 1px dashed rgba(255,255,255,0.15);
    }
    .el-collapse-item__content{
        color: #606266;
        padding-left: 10px;
    }
    .el-timeline-item__content{
        color: #606266;
    }
    .el-timeline-item__timestamp {
        color: #61688a;
    }
    .el-timeline-item__node{
        background-color: #00734b;
    }
    .el-timeline-item__tail{
        border-left: 2px solid #00734b;
    }
</style>
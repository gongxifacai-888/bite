<template>
    <div class="plr20 ptb20">
        <div class="flex alcenter baselight2 ft14 ">
            <div class="mr30 pointer bd2px" v-for="(item,i) in statusList" :class="{'active2px':status==item.status}" @click="changeStatu(item.status)" :key="i">{{item.name}}</div>
        </div>
        <div class="mt20">
            <div class="flex alcenter ptb10" v-if="orderlist.length>0">
                <span class="flex1">{{$t('store.orderNumber')}}</span>
                <span class="flex1 tc">{{$t('legal.price')}}</span>
                <span class="flex1 tc">{{$t('legal.num')}}</span>
                <span class="flex1 tc">{{$t('legal.allmoney')}}</span>
                <!-- <span class="flex1 tc">交易对象</span> -->
                <span class="flex1 tc">{{$t('store.times')}}</span>
                <span class="flex1 tr">{{$t('store.orderstatus')}}</span>
            </div>
            <div class="baselight2">
                <div class="flex alcenter ptb10 bdb30 pointer" v-for="(item,i) in orderlist" :key="i">
                    <div class="flex1 ">
                        <span :class="['ft14 green',{'red':item.way=='BUY'}]">{{item.way=='SELL' ? $t('legal.buy'):$t('legal.sell')}}</span>
                        <span class="blue">{{item.currency_name}}</span>
                    </div>
                    <span class="flex1 tc">{{item.price}}</span>
                    <span class="flex1 tc">{{item.number}}</span>
                    <span class="flex1 tc green">{{item.amount}}</span>
                    <!-- <div class="flex1 tc">
                        <div class="blue">昵称</div>
                        <div class="green flex alcenter jscenter">
                            <img src="../../assets/images/person.png" class="wt10" alt="">
                            <span class="pl5">真是姓名</span>
                        </div>
                    </div> -->
                    <span class="flex1 tc">{{item.created_at}}</span>
                    <div :to='{name:"paymoney"}' class="flex1 tr" @click="goDetail(item.id)">
                        <!-- 0 交易中 1已付款 2延期确认 3维权 4取消 5确认 -->
                        <span class="" v-if="item.status==0">交易中</span>
                        <span class="" v-if="item.status==1">{{$t('store.has_pay')}}</span>
                        <span class="" v-if="item.status==2">{{$t('store.delay')}}</span>
                        <span class="" v-if="item.status==3">{{$t('store.protection')}}</span>
                        <span class="" v-if="item.status==4">{{$t('store.has_cancel')}}</span>
                        <span class="" v-if="item.status==5">{{$t('store.done')}}</span>
                        <img src="../../assets/images/arrowRight.png" alt="" class="wt10">
                    </div>
                </div>
                <div class="tc ptb40" v-if="orderlist.length==0">
                    <img src="../../assets/images/nodata.png" alt="" class="wt70">
                    <p>{{$t('legal.norecord')}}</p>
                </div>
            </div>
        </div>
        <div class="abstort w100 tc lf0 btm20 pages mt40" >
            <el-pagination
                background
                @current-change="handleCurrentChange"
                :current-page.sync="currentPage"
                :hide-on-single-page="last"
                :page-size='per_page'
                layout="prev, pager, next"
                :total="total">
            </el-pagination>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
            id:'',
            statusList:[{name:this.$t('legal.all'),status:'-1'},{name:this.$t('store.notdone'),status:0},{name:this.$t('store.has_pay'),status:1},{name:this.$t('store.delay'),status:2},{name:this.$t('store.protection0'),status:3},{name:this.$t('store.has_cancel'),status:4},{name:this.$t('store.done'),status:5}],
            status:-1,
            currentPage:1,
            per_page:10,
            total:0,
            last:false,
            orderlist:[],
        }
    },
    created(){
        this.id = this.$route.query.id;
        this.getlist();
    },
    methods:{
        changeStatu(status){
            this.status=status;
            this.currentPage=1;
            this.getlist();
        },
        getlist(){//获取历史发布列表
            this.$http.initDataToken({url:'otc/master_details',data:{page:this.currentPage,master_id:this.id,status:this.status}},false)
            .then(res=>{
                this.total = res.total;
                this.per_page = res.per_page;
                this.last = res.last_page==1 ? true : false;
                this.orderlist = res.data;
            }) 
        },
        handleCurrentChange(val){//点击页码
            this.getlist();
        },
        // 订单详情页面
        goDetail(id){
            this.$router.push({
                path:'shoporderdetail',
                query:{id:id}
            })
        }
    }
}
</script>
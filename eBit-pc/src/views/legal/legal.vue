<template>
    <div>
        <div class="w100 mauto baselight2 mt20 bgpart ptb10 flex alcenter between ft16 plr20 radius2">
            <p class="blue ptb10">{{$t('legal.trade')}}</p>
            <p class="ptb10 pointer" @click="goOrder">{{$t('legal.order')}}</p>
        </div>
        <div class="ht10 bgline"></div>
        <div class="radius2">
            <div class="bgheader plr20 radius2">
                <div class="ft16 ptb20 flex alcenter between">
                    <div class="flex baselight2 ">
                        <span class="bold mr20 pointer" @click="changeType('BUY')" :class="{'green':selectType=='BUY'}">{{$t('legal.buy')}}</span>
                        <span class="ht20 bdrfopt25" ></span>
                        <span class="bold ml20 pointer" @click="changeType('SELL')" :class="{'red':selectType=='SELL'}">{{$t('legal.sell')}}</span>
                    </div>
                    
                </div>
                <div class="flex alcenter ft16 bold  baselight2">
                    <div class="mr20 ptb10 pointer bd2px" v-for="(item,i) in coinlist" :key="i" :class="[i==coinIndex?(selectType=='BUY'?'bdbgreen':'bdbred'):'',i==coinIndex?(selectType=='BUY'?'green':'red'):'']" @click="changeCoin(i)">{{item.code}}</div>
                </div>
            </div>
            <div class="plr20 mt20 baselight2">
                <div class="flex alcenter ptb10" v-if="orderlist.length>0">
                    <span class="w10">{{$t('legal.business')}}</span>
                    <span class="flex1">{{$t('legal.num')}}</span>
                    <span class="flex1">{{$t('legal.limit')}}</span>
                    <span class="flex1">{{$t('legal.price')}}</span>
                    <span class="flex1">{{$t('legal.payWay')}}</span>
                    <span class="wt50 tr">{{$t('legal.operate')}}</span>
                </div>
                <div class="flex alcenter ptb10 bdb30" v-for="(item,i) in orderlist" :key='i'>
                    <div class="w10 flex alcenter">
                        <span class="wt30 ht30 radius50p white tc  flex alcenter jscenter" :style="{'background': 'rgb('+Math.random()*255+','+Math.random()*255+','+Math.random()*255+')'}">{{item.seller_name.substr(0,1)}}</span>
                        <span class="pl5">{{item.seller_name}}</span>
                    </div>
                    <div class="flex1"><span>{{item.remain_qty}}</span><span class="pl5">{{coinlist[coinIndex].code}}</span></div>
                    <div class="flex1">{{(item.min_number*item.price) | filterDecimals}}-{{(item.max_number*item.price) | filterDecimals}} <span class="pl5">{{item.area_currency}}</span></div>
                    <div class="flex1 green">{{item.price}}<span class="pl5">{{item.area_currency}}</span></div>
                    <div class="flex1 tc flex alcenter">
                        <p class="mr5 ptb2 " v-for="(itm,inx) in item.payways" :key="inx">
                        <img :src="API + itm.logo" width="20" alt="">
                            <!-- <span :class="[{'blue':itm.code=='alipay','green':itm.code=='wxpay','yellow':itm.code=='bank'}]">{{itm.name}}</span> -->
                        </p>
                    </div>
                    <div class="wt50 bgblue tc white radius2 ptb5 pointer" @click="getinfo(item)">{{selectType=='BUY'?$t('legal.buy'):$t('legal.sell')}}</div>
                </div>
            </div>
            <div class="tc ptb40" v-if="orderlist.length==0">
                <img src="../../assets/images/nodata.png" alt="" class="wt70">
                <p>{{$t('legal.norecord')}}</p>
            </div>
            <!-- 购买出售弹框 -->
            <el-dialog
                :title="selectType=='BUY'?$t('legal.buy'):$t('legal.sell')+coinlist[coinIndex].code"
                :visible.sync="dialogVisible"
                width="30%"
                :before-close="handleClose">
                <div class="">
                    <p class="mb15">{{$t('legal.price')}}：{{Data.price}}{{Data.area_currency}}</p>
                    <!-- <div class="flex">
                        <div class="flex ft16 mb15 bdbe7">
                            <span class=" mr20" :class="{'active2px':selectdealType==1}" @click="changedealtype(1)">CNY交易</span>
                            <span class="" :class="{'active2px':selectdealType==2}" @click="changedealtype(2)">出售数量</span>
                        </div>
                    </div> -->
                    <div>
                        <div class=" ht50 lh50 flex alcenter posRelt mb15">
                            <input type="number" class="flex1 h100 bde7 pl20 radius2" v-model="number" :placeholder="$t('legal.pl')+(selectType=='BUY'?$t('legal.buy'):$t('legal.sell'))+$t('legal.num')">
                            <div class="abstort rt0 btm0 ht50 flex alcenter">
                                <!-- <div class="plr10" v-show="selectdealType==1">CNY</div> -->
                                <div class="plr10" v-if="coinlist[coinIndex]">{{coinlist[coinIndex].code}}</div>
                                <div class="ht30 bdre7"></div>
                                <div class="plr10 pointer" @click="dealAll">{{$t('legal.all')}}</div>
                            </div>
                        </div> 
                        <p class="mb15">{{$t('legal.limit')}}：{{(Data.min_number*Data.price) | filterDecimals}}-{{(Data.max_number*Data.price) | filterDecimals}}  {{Data.area_currency}}</p>
                        <p class="">{{$t('legal.allmoney')}}：{{totalPrice | filterDecimals}}</p>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer flex">
                    <el-button class="flex1 mr30 radius4" @click="dialogVisible=false"><span>{{second}}</span> s{{$t('legal.cannel')}}</el-button>
                    <el-button type="primary" @click="confirm" class="flex1 radius4" >{{$t('legal.e_confrim2')}}</el-button>
                </span>
            </el-dialog>
            <!-- 交易密码 -->
            <el-dialog
                :title="$t('legal.payPassword')"
                :visible.sync="dialogVisiblepwd"
                width="30%"
                :before-close="handleClose">
                <div class=" ht50 lh50 flex alcenter  mb15">
                    <input type="text" class="flex1 h100 bde7 pl20 radius2" :placeholder="$t('legal.enterPassword')">
                </div> 
                <span slot="footer" class="dialog-footer ">
                    <!-- <el-button class="radius4" @click="dialogVisiblepwd=false">{{$t('legal.cannel')}}</el-button> -->
                    <el-button class="flex1 mr30 radius4" @click="dialogVisible=false"><span>{{second}}</span> {{$t('legal.cannel')}}</el-button>
                    <el-button type="primary" @click="confirmpwd" class=" radius4">{{$t('legal.e_confrim2')}}</el-button>
                </span>
            </el-dialog>
            <!-- <div class="abstort w100 tc lf0 btm20 pages" >
                <el-pagination  layout="prev, pager, next" :total="1000"></el-pagination>
            </div> -->
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
        <!-- 交易密码 -->
        <el-dialog
            :title="$t('legal.payPassword')"
            :visible.sync="dialogVisiblepwd"
            width="30%"
            :before-close="handleClose">
            <div class=" ht50 lh50 flex alcenter  mb15">
                <input type="text" class="flex1 h100 bde7 pl20 radius2" :placeholder="$t('legal.enterPassword')">
            </div> 
            <span slot="footer" class="dialog-footer ">
                <el-button class="radius4" @click="dialogVisiblepwd=false">{{$t('legal.cannel')}}</el-button>
                <el-button type="primary" @click="confirmpwd" class=" radius4">{{$t('legal.e_confrim2')}}</el-button>
            </span>
        </el-dialog>
    </div>
</template>
<script>
export default {
    data(){
        return{
            coinlist:[],
            currency_id:'',
            selectType:'BUY',//购买
            selectdealType:1,//cny交易
            coinIndex:0,
            currentPage:1,
            per_page:10,
            total:0,
            last:false,
            orderlist:[],
            dialogVisible:false,
            dialogVisiblepwd:false,
            second:60,
            interid:'',
            buysellpla:'',
            buypla:[this.$t('legal.enterMoney'),this.$t('legal.enterNumber')],
            sellpla:[this.$t('legal.sellMoney'),this.$t('legal.sellNumber')],
            Data:{
                price:0,
            },
            number:'',
        }
    },
    created(){
        this.getCurrency();
    },
    computed:{
        totalPrice(){
            let total = (this.number*this.Data.price) || 0.00;
            return total;
        },
    },
    methods:{
        // 买入卖出类型
        changeType(e){
            this.selectType=e;
            this.currentPage=1;
            this.getlist();
        },
        // 金额或数量购买
        changedealtype(e){
            this.selectdealType=e;
        },
        // 切换币种
        changeCoin(i){
            this.coinIndex=i;
            this.currency_id = this.coinlist[i].id;
            this.currentPage=1;
            this.getlist();
        },
        handleClose(done) {
            // this.$confirm('确认关闭？')
            // .then(_ => {
            //     done();
            // })
            // .catch(_ => {});
            this.dialogVisible = false;
            this.dialogVisiblepwd=false;
        },
         goOrder(){//查看订单
            this.$router.push({
                path: "orders",
                query: { id: this.currency_id}
            });
        },
        confirm(){
            let that = this;
            if(Number(this.number)<=0){
               this.$message(this.$t('legal.buyNumber'));
               return false;
            }
            this.$http.initDataToken({url:'otc/match_master',data:{master_id:this.Data.id,price:this.Data.price,number:this.number},type:'post'},true,true)
            .then(res=>{
                this.dialogVisible = false;
                that.$router.push({
                    path:'orderdetail',
                    query:{id:res.id}
                })
            })
        },
        // 确认密码
        confirmpwd(){

        },
        // 点击购买出售
        getinfo(item){
            let that = this;
            this.Data = item;
            this.number = '';
            this.dialogVisible=true;
            this.second=60;
            clearInterval(this.interid);
            this.interid=setInterval(()=>{
                if(this.second<=0) {
                    clearInterval(that.interid);
                    that.dialogVisible=false;
                    return;
                }
                this.second=this.second-1;
            },1000)
        },
        // 全部
        dealAll(){
            this.number = Number(this.Data.max_number);
        },
        getCurrency(){//获取可购买币种
            this.$http.initDataToken({url:'otc/currencies'},false)
            .then(res=>{
                this.coinlist = res;
                this.currency_id = res[0].id;
                this.getlist();
            })
        },
         getlist(){//获取购买列表
            let way = this.selectType=='BUY'?'SELL':'BUY';
            this.$http.initDataToken({url:'otc/masters',data:{page:this.currentPage,way:way,currency_id:this.currency_id}},false)
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
       
    }
}
</script>
<style lang="">
   /* .pages .el-dialog, .el-pager li{
        background: transparent;
    }
   .pages .el-pagination button{
        background: transparent !important;
        color: #b0b8db !important;
    }
   .pages .el-pagination button:disabled{
        color: #5a5a5a !important;
    }
   .pages .el-pagination{
        color:#b0b8db;
    } */
</style>
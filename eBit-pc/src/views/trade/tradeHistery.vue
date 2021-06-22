<template>
    <div>
        <div class="bgheader bdbheader plr15 pt10 baselight2 flex alcenter between">
            <div class='flex alcenter mb20'>
                <p class='ft16 mr30 pointer' :class="{'blue':curdelegate==0}"  @click='changedelegate(0)'>{{$t('trade.delegate')}}</p>
                <p class='ft16 mr30 pointer' :class="{'blue':curdelegate==1}" @click='changedelegate(1)'>{{$t('trade.all_delegate')}}</p>
                <p class='ft16 mr30 pointer' :class="{'blue':curdelegate==2}" @click='changedelegate(2)'>{{$t('trade.his_delegate')}}</p>
            </div>
           <div class='flex alcenter' v-show='curdelegate<2'>
                <div class="plr5 tc ft12 baselight2 plr10 pb5 mr30 pointer" :class="{'green bdbgreen':currentIndex==0}" @click='changetype(0)'>{{$t('trade.buy')}}</div>
                <div class="plr5 tc ft12 baselight2 plr10 pb5 pointer" :class="{'red bdbred':currentIndex==1}" @click='changetype(1)'>{{$t('trade.sell')}}</div>
            </div>
        </div>
        <div class="bgpart plr30">
            <div class="flex alcenter ptb10">
                <span class="flex12">{{$t('trade.time')}}</span>
                <span class="flex12 tc">{{$t('trade.dui')}}</span>
                <span class="flex12 tc">{{$t('trade.direction')}}</span>
                <span class="flex12 tc">{{$t('trade.price')}}({{legalName}})</span>
                <span class="flex12 tc">{{$t('trade.num')}}({{currencyName}})</span>
                <span class="flex12 tc">{{$t('trade.cj_vol')}}({{currencyName}})</span>
                <span class="flex12 tr" v-show='curdelegate==2'>{{$t('trade.cj_total')}}({{legalName}})</span>
                <span class="flex12 tr" v-show='curdelegate<2'>{{$t('trade.operate')}}</span>
                <span class="flex12 tr" v-show='curdelegate==2'>{{$t('assets.state')}}</span>
            </div>
            <div class='pb50 posRelt'>
                <div class="flex alcenter mt5" v-for="(item,i) in list" :key="i">
                    <span class="flex12">{{item.updated_at.substring(5)}}</span>
                    <span class="flex12 tc" v-if="item.currency_match">{{item.currency_match.symbol}}</span>
                    <div class='flex1 tc' v-show='curdelegate<2'>
                        <span class="green" v-if='currentIndex==0'>{{$t('trade.buy')}}</span>
                        <span class="red" v-else>{{$t('trade.sell')}}</span>
                    </div>
                    <span class="flex12 tc" v-show='curdelegate==2'>{{item.way_name}}</span>
                    <span class="flex12 tc">{{item.price}}</span>
                    <span class="flex12 tc">{{item.number}}</span>
                     <span class="flex12 tc">{{item.transacted_amount || '0.000'}}</span>
                    <span class="flex12 tr" v-show='curdelegate==2'>{{item.transacted_volume}}</span>
                    <div class="flex12 tr">
                        <div class='inblock ptb5 radius2 plr0 pointer' :class="[currentIndex==0?'green':'red']" v-if='curdelegate<2' @click='cancelOrder(item.id,i)'>撤销</div>
                        <!-- <div class='basecolor inblock ptb5 radius2 plr0 ' v-else-if='item.transacted_number==0' >已撤销</div> -->
                        <div class='inblock ptb5 radius2 plr0 blue' v-else>{{item.status_name}}</div>
                    </div>
                </div>
                <div class="tc ">
                   <div class='ptb40' v-show='list.length<=0'>
                        <img src="../../assets/images/nodata.png" alt="" class="wt70">
                        <p>{{$t('home.norecord')}}</p>
                   </div>
                   <!-- <div class='ptb20' v-show='hasmore'>
                        <p class='ptb20' @click='getmore'>加载更多</p>
                   </div> -->
                </div>
                <div class="abstort w100 tc lf0 btm10 pages" >
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
        </div>
    </div>
</template>
<script>
export default {
    props:['mathid','legalName','currencyName','order','neworder'],
    data(){
        return {
            list:[],
            page:1,
            hasmore:false,
            currentIndex:0,
            curdelegate:0,
            token:'',
            interid:'',
            currentPage:1,
            per_page:10,
            total:0,
            last:false,
        }
    },
    mounted(){
        this.token=localStorage.getItem('token');
        // eventBus.$on('doorderFun',msg){
        //     this.list.unshift(msg);
        // }
        if(this.mathid&&this.token){
            this.getlist();
        }
        if(this.token){
            // this.interid=setInterval(()=>{
            //     if(this.curdelegate==0){
            //         this.page=1;
            //         this.getlist()
            //     }
            // },6000)
        }
        
    },
    watch:{
        mathid:{
            handler(olddata,newdata){
                if(this.token){
                    this.getlist();
                }
            }
        },
        order:{     
            handler(olddata,newdata){
                console.log(olddata,newdata)
                this.list.map((item,index)=>{
                    if(item.id==olddata.id){
                        this.list[index].number = olddata.number;
                        this.list[index].transacted_amount =olddata.transacted_amount;
                        console.log(olddata.number-0,olddata.transacted_amount-0)
                        if((olddata.number-0)==(olddata.transacted_amount-0)){
                            console.log('index '+index)
                            this.list.splice(index, 1);//动态删掉这个订单
                        }
                    }
                })
            }
        },
        neworder:{
            handler(olddata,newdata){
                console.log(olddata,newdata+'00000000000000000')
                if(this.curdelegate!=2 && this.currentPage==1){
                    if((this.currentIndex==0&&olddata.way==1) || (this.currentIndex==1&&olddata.way==2) ){
                        this.list.unshift(olddata)
                    }
                }
            }
        //     if(msg.currency_match_id==that.mathid && that.curdelegate!=2){
        //                 //     let data=msg.quotation;
        //                 
                        
        //             }
        }
    },
    methods:{
        // 切换类型
        changetype(e){
            this.currentIndex=e;
            this.page=1;
            // this.list=[];
            this.getlist();
        },
        changedelegate(e){
            this.curdelegate=e;
            this.page=1;
            // this.list=[];
            this.getlist();
        },
        getlist(){
            let url='',data={};
            if(this.curdelegate==2){
                url='tx_history/list'
            }else{
                url=this.currentIndex==0?'tx_in/list':'tx_out/list';
                if(this.curdelegate==0){
                    data.currency_match_id=this.mathid;
                }
            }
            this.$http.initDataToken({url,data:{...data,page:this.currentPage,limit:10}},false,false)
            .then(res=>{
                // if(this.page==1){
                //     this.list=[]
                // }
                // this.hasmore=this.page==res.last_page?false:true;
                // this.list=this.list.concat(res.data);
                this.total = res.total;
                this.per_page = (res.per_page-0);
                this.last = res.last_page==1 ? true : false;
                this.list = res.data;
                
            })
        },
        getmore(){
            this.page++;
            this.getlist();
        },
        handleCurrentChange(val){//点击页码
            this.getlist();
        },
        // 取消订单
        cancelOrder(id,index){
            let that = this;
            let url=this.currentIndex==0?'tx_in/cancel':'tx_out/cancel';
            this.$http.initDataToken({url:url,data:{id}})
            .then(res=>{
                that.list.splice(index, 1);//动态删掉这个订单
                eventBus.$emit('changeBalance',true);
            })
        }
    },
    beforeDestroy(){
        // clearInterval(this.interid)
    }
}
</script>
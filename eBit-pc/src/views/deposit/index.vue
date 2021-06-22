<template>
    <div class="pt20">
        <div class="max1200 mt20 bgheader ptb20 plr20 radius4 ft16">
            <span>{{$t('cun.title')}}</span> 
            <router-link class="ml20 blue" :to="{name:'coinlog'}">{{$t('cun.log')}}</router-link>
        </div>
        <div class="max1200 mt20">
            <div class="bgpart radius4">
                <div class="ptb15 ft16 plr20 bgheader bdbheader flex alcenter between">
                   <span>{{$t('cun.ding')}}</span> 
                </div>
                <div class="bgPart bdbheader" v-for="(item,index) in list" :key="index">
                    <div class="ptb10 bgheader plr10  flex alcenter between">
                        <div class="flex alcenter">
                            <img src="../../../static/imgs/money.png" alt="" style="width:50px">
                            <span class="ft16 pl30">{{item.name}}</span>
                        </div>
                        <div class="bgblue radius4 ptb5 plr20 white pointer" @click="buy(item.id)">{{$t('cun.buy')}}</div>
                    </div>
                    <div class=" ptb20 plr20">
                        <div class="">
                            <div class="ft16">{{$t('cun.circle')}} {{item.limit_days}}{{$t('cun.day')}}</div>
                        </div>
                        <div class="flex alcenter between ft14 mt10">
                            <span>{{$t('cun.jin')}} {{item.display_rate}}</span>
                            <span>{{$t('cun.num')}} {{item.limit_number}} {{item.currency.code}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
           list:[]
        }
    },
    created(){
        this.getlist();
    },
    methods:{
        getlist(){//获取购买列表
            this.$http.initDataToken({url:'storage_currency/lock_list',data:{limit:30}},false)
            .then(res=>{
                this.list = res.data
            }) 
            this.$http.initDataToken({url:'storage_currency/current_setting',data:{limit:30}},false)
            .then(res=>{
                // this.list = res.data
            }) 
        },
        buy(id){
            this.$confirm(this.$t('cun.ifbuy'), this.$t('legal.buy'), {
                confirmButtonText: this.$t('lever.e_confrim2'),
                cancelButtonText: this.$t('lever.cannel'),
                type: 'success'
                }).then(() => {
                    this.$http.initDataToken({url:'storage_currency/submit_lock',type:'post',data:{id:id}})
                    .then((res,msg)=>{
                    }) 
                }).catch(() => {          
            });
        }
    }
}
</script>
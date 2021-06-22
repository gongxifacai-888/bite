<template>
    <div class="w80  flex minh350 mt50 mauto baselight  ">
        <div class="w20  tc ft16 assets bdr bgpart">
            <router-link  :to="{name:item.page}" class=" ptb20  block basecolor bgpart"  v-for="(item,i) in listLeft" :key="i">{{item.name}}</router-link>
        </div>
        <router-view class="flex1 bgpart ml10 minvh70"></router-view>
    </div>
</template>
<script>
export default {
    data(){
        return{
            listLeft:[
                {
                    name:this.$t('assets.tradeacc'),
                    page:'tradeAccount'
                },
                {
                    name:this.$t('assets.legalcurrencyacc'),
                    page:'legalAccount'
                },
                {
                    name:this.$t('assets.leveracc'),
                    page:'leverAccount'
                },
                {
                    name:this.$t('assets.microacc'),
                    page:'secondAccount'
                },
                {
                    name:this.$t('assets.financialacc'),
                    page:'financialAccount'
                },
                 {
                    name:this.$t('assets.transfer'),
                    page:'transfer'
                },
                {
                    name:this.$t('bind.bindAddr'),
                    page:'bindAddr'
                },
                // {
                //     name:'财务记录',
                //     page:'dealRecords'
                // },
            
            ],
            curpage:'tradeAccount'
        }
    },
   created(){

   },
    methods:{
        changepage(e){
            // this.curpage=e;
             
        }
    }
}
</script>
<style lang="">
    .assets .router-link-exact-active{
        background: #ebeef5;
        color:#357ce1;
    }
</style>

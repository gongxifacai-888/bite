<template>
    <div>
        <div class="bgheader bdbheader flex alcenter plr10 ht40 lh40 baselight2 ft16">全站成交 </div>
        <div class="plr10 ptb10 flex1 flex column w100">
             <div class="flex alcenter">
                <span class="flex1">时间</span>
                <span class="flex1 tc">价格</span>
                <span class="flex1 tr">数量</span>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props:['completelist','symbol'],
    data(){
        return {
            
        }
    },
    watch:{
        symbol:{
            handler(newInfo,oldInfo){
                if(oldInfo){
                    this.unbindSocket(oldInfo);
                }
                this.bindSocket(newInfo);
                this.legalname=newInfo.split('/')[1];
                this.currencyname=newInfo.split('/')[0];
            },
            immediate:true
        }
    }
}
</script>
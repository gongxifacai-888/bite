<template>
    <div>
        <div class=" between baselight2  ptb20 plr20 ft16 bgheader">{{$t('header.transactionLog')}}</div>
        <div class="bgpart plr15">
            <div class="flex alcenter ptb10">
                <span class="flex1">{{$t('store.times')}}</span>
                <span class="flex1 tc">{{$t('home.detail')}}</span>
                <span class="flex1 tc">{{$t('trade.num')}}</span>
            </div>
            <div>
                <div class="tc ptb40">
                    <img src="../../assets/images/nodata.png" alt="" class="wt70">
                    <p>{{$t('trade.noData')}}</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
            
        }
    }
}
</script>
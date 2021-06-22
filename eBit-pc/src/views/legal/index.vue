<template>
    <div>
        <!-- <div class="w70 mauto baselight2 mt20 bgpart ptb10 flex alcenter between ft16 plr20 app">
            <router-link :to="{name:'legal'}" class=" ptb10">交易大厅</router-link>
            <router-link :to="{name:'orders'}" class=" ptb10"></router-link>
        </div> -->
        <router-view class="w70 mauto baselight mt5 bgpart minvh80 pb60 posRelt"></router-view>
    </div>
</template>
<script>
export default {
    // name:'login',
    data(){
        return {

        }
    }
}
</script>
<template>
    <div class="bgheader mt5">
        <div class="w60 mauto ptb40 ">
            <div class=" flex basecolor ft16">
                <div class="flex1">{{$t('about.abt')}}</div>
                <div class="flex1 tc">{{$t('about.support')}}</div>
                <div class="flex1 tc">{{$t('home.news')}}</div>
                <div class="flex1 tc">{{$t('about.concat')}}</div>
                <div class="flex1 tc">{{$t('about.kefu')}}</div>
            </div>
            <div class="flex baselight ft14">
                <div class="flex1">
                    <div class="mt10 pointer" v-for='(item,index) in List1'  v-if="index<3" :key="index" @click="goDetail(item.id,25)">{{item.title}}</div>
                </div>
                <div class="flex1 tc">
                    <div class="mt10 pointer" v-for='(item,index) in List2'  v-if="index<3" :key="index" @click="goDetail(item.id,26)">{{item.title}}</div>
                </div>
                <div class="flex1 tc">
                    <div class="mt10 pointer" v-for='(item,index) in List3' v-if="index<3" :key="index" @click="goDetail(item.id,24)">{{item.title}}</div>
                </div>
                <div class="flex1 tc">
                    <div class="mt10" v-show="mobile">{{$t('about.mobile')}}：{{mobile}}</div>
                    <div class="mt10" v-show="email">{{$t('about.email')}}：{{email}}</div>
                </div>
                <div class="flex1 tc">
                    <div class="mt10">
                        <img src="../assets/images/kefu.jpg" alt="" style="width:80px">
                    </div>
                </div>
            </div>
            <div class="tc mt30">
                <p class="ft24 bold white">ebitcex</p>
                <p class="baselight">©Copyright 2020 ebitcex. All rights reserved.</p>
            </div>
        </div>
        
    </div>
</template>
<script>
export default {
    data(){
        return {
          List1:[],
          List2:[],
          List3:[],
          mobile:'',
          email:''
        }
    },
    created() {
        this.getList1()
        this.getList2()
        this.getList3()
        this.getConnect()
        this.getConnect2()
    },
    methods:{
		getList1() {
            this.$http.initDataToken({
                url: 'news/list',
                data: { category_id: 25 }
            },false).then(res=>{
               this.List1 = res.data;
            })
        },
        getList2() {
            this.$http.initDataToken({
                url: 'news/list',
                data: { category_id: 26 }
            },false).then(res=>{
               this.List2 = res.data;
            })
        },
        getList3() {
            this.$http.initDataToken({
                url: 'news/list',
                data: { category_id: 24 }
            },false).then(res=>{
               this.List3 = res.data;
            })
        },
        // 详情
        goDetail(id,category_id) {
            this.$router.push({
                name: "notice",
                query: { 
                    id: id,
                    category_id: category_id
                }
            });
        },
        getConnect() {
            this.$http.initDataToken({
                url: 'default/setting',
                data: { key:'contact_mobile' }
            },false).then(res=>{
               this.mobile = res;
            })
        },
        getConnect2() {
            this.$http.initDataToken({
                url: 'default/setting',
                data: { key:'contact_email' }
            },false).then(res=>{
               this.email = res;
            })
        },
    }
}
</script>
<template>
    <div class="plr20 ptb20 notice flex column alcenter jscenter">
        <!-- <div class="header flex alcenter wraps bgpart baselight">
                <span v-for='(item,index) in List' :key="index" @click="getNoticeList(item.id)" v-show="item.id!=23" :class="['pointer','ft16',{'select':current==item.id}]">{{item.name}}</span>
        </div> -->
        <div class="content flex baselight  mt10">
            <div class="left flex column plr20 bgpart">
                <h1 class="ft16 bold mt20">了解更多</h1>
                <span v-for='(item,index) in noticeList' :key="index" @click="getDetail(item.id)" :class="['pointer','ft16',{'select':id==item.id}]">{{item.title}}</span>
            </div>
            <div class="right flex1 bgpart ml20 flex column plr20 ptb20 baselight">
                 <p class="ft32 mt20">{{title}}</p>
                 <div v-html="news" class="mt20 ft16"></div>
                 <span class="flex jsend ft14 bold mt20">{{time}}</span>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data(){
        return {
           List:[],
           noticeList:[],
           news:'',
           time:'',
           title:'',
           id:'',
           current:''
        }
    },
    created() {
        this.id = this.$route.query.id;
        this.current = this.$route.query.category_id;
        this.getDetail(this.id);
        this.getNoticeList()
        this.getAllList()
    },
    methods:{
        //新闻分类
        getAllList() {
            this.$http.initDataToken({
                url: 'news/categories',
                data: { }
            },false).then(res=>{
               this.List = res;
            })
        },
         //公告
		getNoticeList() {
            // this.current = id;
            this.$http.initDataToken({
                url: 'news/list',
                data: { category_id: this.current }
            },false).then(res=>{
               this.noticeList = res.data;
            })
        },
        getDetail(id){
            this.id = id;
            this.$http.initDataToken({
                url: 'news/info',
                data: { id: id }
            },false).then(res=>{
                console.log(res)
                this.news = res.content;
                this.time = res.created_at;
                this.title = res.title;
            })
        }
    }
}
</script>
<style lang="scss">
    .header{
        width: 1200px;
        height: 180px;
        padding: 5px 15px;
        span{
            width: 155px;
            text-align: center;
            border-radius: 5px;
            line-height: 35px;
            border: 1px solid rgba(53, 124, 225, 1);
            margin: 0 20px;
        }
        span:hover{
            color: #fff;
            background-color: rgba(53, 124, 225, 1);
        }
        .select{
                color: #fff;
                background-color: rgba(53, 124, 225, 1);
        }
    }
    .content{
        width: 1200px;
        min-height: 80vh;
        .left{
            width: 20%;
            span{
                width: 100%;
                line-height: 25px;
                border-radius: 4px;
                display: block;
                font-weight: 300;
                margin-top: 10px;
                padding: 10px;
                word-break:break-all;
            }
            span:hover{
                color: #fff;
                background-color: rgba(53, 124, 225, 1);
            }
            .select{
                color: #fff;
                background-color: rgba(53, 124, 225, 1);
            }
        }
        .right{

        }
        .right::-webkit-scrollbar {
           
        }
    }
</style>
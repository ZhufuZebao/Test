<template>
  <div>
    <ECharts ref="chart" :options="orgOptions" :auto-resize="true"></ECharts>
    <div>
      <table class="contractcontain">
        <tr>
          <td>分類</td>
          <td>アカウント名</td>
          <td>ドキュメント容量</td>
          <td>チャット送信容量</td>
          <td>チャット添付容量</td>
        </tr>
        <tr :id="'s'+users.id" :key="users.id" class="clearfix btns" date-tgt="wd1" v-for="users in user">
          <td >{{ users.enterprise_admin_name}}</td>
          <td >{{users.name}}({{ users.email}})</td>
          <td >{{ toSize(users.storage.doc_storage)}}Mb</td>
          <td >{{ toSize(users.storage.chat_storage)}}Mb</td>
          <td>{{ toSize(users.storage.chat_file_storage)}}Mb</td>
        </tr>
        <tr :id="'i'+users.id" class="clearfix btns" date-tgt="wd1" v-for="users in invite" :style="{background:relation(users.relation)}">
          <td >協力会社</td>
          <td >{{users.name}}({{ users.email}})</td>
          <td >{{ toSize(users.storage.doc_storage)}}Mb</td>
          <td >{{ toSize(users.storage.chat_storage)}}Mb</td>
          <td>{{ toSize(users.storage.chat_file_storage)}}Mb</td>
        </tr>
        <tr :id="'f'+users.id" class="clearfix btns" date-tgt="wd1" v-for="users in friend" :style="{background:relation(users.relation)}">
          <td >職人</td>
          <td >{{users.name}}({{ users.email}})</td>
          <td >{{ toSize(users.storage.doc_storage)}}Mb</td>
          <td >{{ toSize(users.storage.chat_storage)}}Mb</td>
          <td>{{ toSize(users.storage.chat_file_storage)}}Mb</td>
        </tr>
        <tr :id="'p'+projects.id"  class="clearfix btns" date-tgt="wd1" v-for="projects in project">
          <td >案件</td>
          <td >{{projects.place_name}}</td>
          <td >{{ toSize(projects.storage.doc_storage)}}Mb</td>
          <td >{{ toSize(projects.storage.chat_storage)}}Mb</td>
          <td>{{ toSize(projects.storage.chat_file_storage)}}Mb</td>
        </tr>
        <tr v-if="delStorage">
          <td >削除済みユーザ使用分</td>
          <td >-</td>
          <td >{{ toSize(delStorage.doc_storage)}}Mb</td>
          <td >{{ toSize(delStorage.chat_storage)}}Mb</td>
          <td>{{ toSize(delStorage.chat_file_storage)}}Mb</td>
        </tr>
        <tr v-else>
          <td >削除済みユーザ使用分</td>
          <td >-</td>
          <td >{{ toSize(0)}}Mb</td>
          <td >{{ toSize(0)}}Mb</td>
          <td>{{ toSize(0)}}Mb</td>
        </tr>
        <tr >
          <td >合計（{{toSize(doc_storage+chat_storage+chat_file_storage)}}Mb）</td>
          <td ></td>
          <td >{{toSize(doc_storage)}}Mb</td>
          <td >{{toSize(chat_storage)}}Mb</td>
          <td>{{toSize(chat_file_storage)}}Mb</td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script>
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../../mixins/Messages";
    import Calendar from "../../../mixins/Calendar";
    import ECharts from 'vue-echarts'
    import 'echarts/lib/chart/pie'
    import 'echarts/lib/chart/bar'
    import 'echarts/lib/component/legend'

    export default {
        name: "ContractContain",
        components: {
            UserProfile, ECharts
        },
        mixins: [Messages,Calendar],
        props: {
          enterprise_id:{
            Type: String,
          }
        },
        data() {
            return {
                friendInformation: {},
                projectCount:{},
                orgOptions:{},
                status:{},
                user:{},
                doc_storage:0,
                chat_storage:0,
                chat_file_storage:0,
                invite:{},
                friend:{},
                project:{},
                delStorage:{},
            }
        },
        methods: {
            fetch: function () {
                //const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.system;
                axios.post('/api/contractContain',{
                    enterpriseId : this.enterprise_id,
                }).then((res) => {
                    this.user = res.data.res;
                    this.invite = res.data.invite;
                    this.friend = res.data.friend;
                    this.project = res.data.project;
                    this.delStorage = res.data.delStorage;
                    try {
                        for (let i = 0; i < this.user.length; i++) {
                            if(this.user[i].enterprise_admin==='1'){
                                this.user[i].enterprise_admin_name='管理アカウント';
                            }else{
                                this.user[i].enterprise_admin_name='一般アカウント';
                            }
                            this.doc_storage=res.data.doc_storage;
                            this.chat_storage=res.data.chat_storage;
                            this.chat_file_storage=res.data.chat_file_storage;
                        }
                    } catch (e) {
                        this.$alert(errMessage, {showClose: false});
                        // loading.close();
                    }

                    this.status=this.projectCount.progress_status;
                    this.drawLine(this.status);
                    // loading.close();

                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    // loading.close();
                });
                //loading.close();
            },
            toSize(size){
                if(size){
                    return  (size/1024/1024).toFixed(2);
                }else{
                    return 0;
                }
            },
            relation(relation){
                if(!relation){
                    return '#F1F1F1';
                }else{
                    return '#FFFFFF';
                }
            },
            drawLine:function(){
                let storage=this.doc_storage+this.chat_storage+this.chat_file_storage;
                let doc_storage=(this.doc_storage/storage*100).toFixed(2);
                let chat_storage=(this.chat_storage/storage*100).toFixed(2);
                let chat_file_storage=(this.chat_file_storage/storage*100).toFixed(2);
                this.orgOptions = {

                    pieColor: ["#B22222", "#AAC632", "#4169E1"],
                    legend: {
                        itemWidth: 25,
                        itemHeight: 25,
                        orient: 'vertical',

                        x: '450',
                        y: '250',
                        data: ['ドキュメント容量', 'チャット送信容量', 'チャット添付容量'],
                        textStyle: {
                            color: "black",
                            fontSize: 13,
                        },
                    },
                    series: [
                        {
                            color: ["#B22222", "#AAC632", "#4169E1"],
                            type: "pie",
                            hoverAnimation: false,
                            radius: ["0%", "80%"],
                            data: [
                                {name:"ドキュメント容量",value:doc_storage},
                                {name:"チャット送信容量",value:chat_storage},
                                {name:"チャット添付容量",value:chat_file_storage},
                            ],
                            itemStyle: {
                                normal: {
                                    label: {
                                        show: true,
                                        position: 'inner',
                                        formatter: '{c}%',
                                    },
                                    labelLine: {
                                        show: true
                                    }
                                }
                            },
                        }
                    ]
                }


            },
          /**
           * 小数点以下3桁ぐらいで四捨五入し
           * @param number
           * @param n       小数点以下3桁
           * @returns {*}
           */
          getFloat : function (number, n=3) {
            n = n ? parseInt(n) : 0;
            if (n <= 0) return Math.round(number);
            number = Math.round(number * Math.pow(10, n)) / Math.pow(10, n);
            return number;
          },
        },
        created() {
        },
        watch: {

        },
        mounted() {
            this.fetch();
        }
    }
</script>



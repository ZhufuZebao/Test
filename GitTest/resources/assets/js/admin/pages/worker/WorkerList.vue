<template>
  <el-container class="worklist">
    <el-header style="height: 122px;" class="header">
      <el-row type="flex" class="row-bg" justify="space-between">

        <el-col :span="6" class="headLeft">
          <router-link to="/worker">
            <li><img src="images/admin-img/craftsman_on@2x.png"></li>
            <li class="title">
              <p>職人</p>
              <p>Craftsman</p>
            </li>
          </router-link>
        </el-col>

        <el-col :span="8" class="headRight">
          <UserProfile/>
        </el-col>
      </el-row>
    </el-header>
    <el-main class="container">
      <div class="part1">
        <li>
          <h2>職人一覧</h2>
          <el-button @click="workerCsv">CSV出力</el-button>
        </li>
        <li>
          <el-input placeholder="キーワード検索" v-model="searchName"   prefix-icon="searchForm-submit"></el-input>
          <el-button @click="search">検索</el-button>
        </li>
      </div>
      <div class="delet-img">
        <a class="title-del"  v-if="delFriendItem.length !== 0" @click.prevent="delFriendItems">
          <img  class="img-delete" src="images/icon-dust2.png" alt="" >
        </a>
      </div>
      <div>
        <div>
          <ul class="tableHeader">
            <li >
              {{friendsName('email')}}
            </li>
            <li >
              {{friendsName('user_profiles')}}
            </li>
            <li >
              {{friendsName('last_name')}}
            </li>
            <li >
              {{friendsName('first_name')}}
            </li>
            <li >
              {{friendsName('ip')}}
            </li>
            <li>
              {{friendsName('last_date')}}
            </li>
            <li>
              {{friendsName('creat_time')}}
              <!--       <span @click="sort('date')"><i class="el-icon-d-caret"></i></span>-->
            </li>
            <li>
              {{friendsName('operate')}}
            </li>
          </ul>
        </div>
        <ul class="tablecontent">
          <el-checkbox-group v-model="delFriendItem">
            <li :id="'s'+worker.id" :key="worker.id" class="btns" date-tgt="wd1" v-for="worker in workers">
              <span >{{ worker.email}}</span>
              <span class="avatar"><img v-if="worker.file" :src="worker.file">
              <img v-else src="images/icon-chatperson.png"></span>
              <span>{{ worker.last_name}}</span>
              <span>{{ worker.first_name}}</span>
              <span>{{ worker.ip}}</span>
              <span>{{ worker.last_date}}</span>
              <span>{{ worker.created_at}}</span>
              <span>
                 <el-button @click="workerDetail(worker.id)">詳細</el-button>
                <el-button class="Cancelblock-button"  v-if="worker.block=== '1'" @click="workerBlock(worker.id)">ブロック  解除</el-button>
                <el-button @click="showWorkerBlock(worker)"  class="block-button" v-else>ブロック</el-button>
              </span>
            </li>
          </el-checkbox-group>
        </ul>
      </div>
      <div class="pagination-center" v-if="peopleCount !== ''">
        {{peopleCount}}名を招待いたしました
      </div>.
      <div class="pagination-center">
        <el-pagination
                prev-text="◀"
                next-text="▶"
                background
                layout="prev, pager, next"
                @current-change="changePage" :current-page="currentPage" :page-size="perPage"
                :total="total">
        </el-pagination>
      </div>
    </el-main>
    <WorkerBlock :WorkerBlockVisible="WorkerBlockVisible" :block="worker.block" :email="worker.email"
                 :file="worker.file" :id="worker.id" :last_name="worker.last_name"
                 @changeShow="changeShow" @reload="reload" ref="WorkerBlockRef"/>
  </el-container>
</template>

<script>
    import Pagination from "../../components/common/Pagination";
    import UserProfile from "../../components/common/UserProfile";
    import FriendLists from '../../mixins/WorkerList';
    import Calendar from '../../../mixins/Calendar';
    import Messages from "../../../mixins/Messages";
    import MailModal from '../../../components/friend/MailModal';
    import WorkerBlock from "./WorkerBlock";

    export default {
        components: {
            Pagination,
            UserProfile,
            MailModal,
            WorkerBlock
        },
        mixins: [Calendar, FriendLists, Messages],
        data() {
            return {
              WorkerBlockVisible: false,
                workers: {},
                delFriendItem: [],
                peopleCount: '',
                sortCol: "name",          //ソートカラム
                order: "asc",            //昇順、降順
                searchName: '',      // 詳細検索対象
                showMailModal: false,
                area:'',             //対応可能エリア
                perPage: 20,
                currentPage: 1,
                current: 1,
                total: 1,
                from: 0,
                to: 0,
                worker:[],
            }
        },
        methods: {
          workerCsv(){
              window.location.href = window.BASE_URL + '/api/workerCsv';
          },
          showWorkerBlock(worker){
            this.worker=worker;
            this.WorkerBlockVisible = true
          },
          changeShow(data){
            if(data === 'false'){
              this.WorkerBlockVisible = false
            }else{
              this.WorkerBlockVisible = true
            }
          },


          formatDate: function(date) {

              const dateTime = new Date(date);
              const YY = dateTime.getFullYear();
              const MM =
                      dateTime.getMonth() + 1 < 10
                              ? '0' + (dateTime.getMonth() + 1)
                              : dateTime.getMonth() + 1;
              const D =
                      dateTime.getDate() < 10 ? '0' + dateTime.getDate() : dateTime.getDate();
              const hh =
                      dateTime.getHours() < 10
                              ? '0' + dateTime.getHours()
                              : dateTime.getHours();
              const mm =
                      dateTime.getMinutes() < 10
                              ? '0' + dateTime.getMinutes()
                              : dateTime.getMinutes();
              const ss =
                      dateTime.getSeconds() < 10
                              ? '0' + dateTime.getSeconds()
                              : dateTime.getSeconds();
              return `${YY}/${MM}/${D}`;


          },
            getPeopleCount(data) {
                this.peopleCount = data;
                setTimeout(() => {
                    this.peopleCount = '';
                }, 3000);
            },

            //並べ替え
            sort(sortCol) {
                if (this.workers.length !== 0) {
                    if (this.sortCol === sortCol) {
                        this.changeOrder();
                    } else {
                        this.sortCol = sortCol;
                        this.order = 'asc'
                    }
                this.fetchFriends();
                }
            },
            changeOrder() {
                if (this.order === 'asc') {
                    this.order = 'desc';
                } else {
                    this.order = 'asc';
                }
            },
            // 職人 詳細
            workerDetail: function (userId) {
                this.$router.push({path: '/worker/detailInformation/' + userId});
            },

            // 空チェック
            isEmpty: function (obj) {
                return typeof obj == "undefined" || obj == null || obj === ""
            },
            //ページを切り替える
            changePage: function (page) {
                this.currentPage = page;
                if (this.searchName) {
                    this.search();
                } else {
                    this.fetchFriends();
                }
            },
            // 職人 検索
            search() {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.system;
                let data = {page: this.currentPage,sortCol: this.sortCol,pageSize:this.perPage, order: this.order,searchName:this.searchName};
                axios.post('/api/getWorkerList',data).then((res) => {
                    this.workers = res.data.workers;
                    this.currentPage = parseInt(res.data.current_page);
                    this.total = res.data.total;
                    this.from = res.data.from ? res.data.from : 0;
                    this.to = res.data.to ? res.data.to : 0;
                    for (let i = 0; i < res.data.workers.length; i++) {
                        try {
                            if(this.workers[i].file){
                                this.workers[i].file='file/users/'+ this.workers[i].file;
                            }else{
                                this.workers[i].file=null;
                            }
                        } catch (e) {
                            this.$alert(errMessage, {showClose: false});
                            loading.close();
                        }
                    }
                })
                    .catch(error => {
                        this.$alert(errMessage, {showClose: false});
                        loading.close();
                    });
                loading.close();
            },

            // 職人 一覧
            fetchFriends: function () {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.workerList;
                if(window.workerInput){
                    this.searchName=window.workerInput;
                    window.workerInput='';
                }
                let data = {page: this.currentPage,sortCol: this.sortCol,pageSize:this.perPage, order: this.order,searchName:this.searchName};
                axios.post('/api/getWorkerList',data).then((res) => {
                    this.workers = res.data.workers;
                    this.currentPage = parseInt(res.data.current_page);
                    this.total = res.data.total;
                    this.from = res.data.from ? res.data.from : 0;
                    this.to = res.data.to ? res.data.to : 0;
                    for (let i = 0; i < res.data.workers.length; i++) {
                        try {
                            if(this.workers[i].file){
                                this.workers[i].file='file/users/'+ this.workers[i].file;
                            }else{
                                this.workers[i].file=null;
                            }
                        } catch (e) {
                            this.$alert(errMessage, {showClose: false});
                            loading.close();
                        }
                    }
                })
                    .catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    loading.close();
                });
                loading.close();
            },
            reload(){
                this.WorkerBlockVisible = false;
                this.fetchFriends();
            },
            workerBlock(id){
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                axios.post('/api/workerBlock', {
                    id: id,
                }).then(res =>{
                    this.fetchFriends();
                   // this.$alert(res.data);
                    loading.close();
                }).catch(error => {
                    loading.close();
                    this.$alert(this.commonMessage.error.workerEdit, {showClose: false});
                });
            },


            // 職人を削除する
            delFriendItems() {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.delete;
                this.$confirm(this.commonMessage.confirm.delete.account).then(() => {
                    axios.post("/api/delFriend", {
                        id: this.delFriendItem,
                    }).then(res => {
                        this.delFriendItem = [];
                        this.search();
                    }).catch(error => {
                        this.$alert(errMessage, {showClose: false});
                        this.delFriendItem = [];
                        loading.close();
                    });
                }).catch(action => {
                    this.delFriendItem = [];
                    loading.close();
                });
            },
            // チャットリンク
            chatLink(id) {
                axios.post('/api/friendChatLink', {
                    id: id,
                }).then((res) => {
                    let groupId = res.data.group_id;
                    let userId = res.data.user_id;
                    this.$router.push({path: "/chat", query: {groupId: groupId, userId: userId}});
                })
            },
            openMailModal(){
                this.showMailModal = true;
            },
            closeMailModal: function () {
                this.showMailModal = false;
            },
        },
        created() {
            this.fetchFriends();
        },
        watch: {
            delFriendItem: function () {
                $(".is-checkedbox").removeClass('is-checkedbox');
                let num = this.delFriendItem.length;
                for(let i = 0; i<num; i++){
                    let str = '#s' + this.delFriendItem[i];
                    $(str).addClass('is-checkedbox');
                }
            }
        },
    }
</script>

<style scoped>
  .flo {
    float: left;
  }
  .friend-li {
    width: 16%;
  }
  .friend-li:first-child {
    width: 18%;
  }
  .friend-li:nth-child(2) {
    width: 6%;
  }
  .friend-li:nth-child(3) {
    width: 22%;
  }
  .friend-li:nth-child(5) {
    width: 22%;
  }

</style>

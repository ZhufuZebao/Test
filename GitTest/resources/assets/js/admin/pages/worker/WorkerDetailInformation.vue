<template>
    <el-container class="workerdetail">
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
            <div class="common-view project-detail">
                <div class="part1">
                    <li>
                        <h2>職人詳細</h2>
                    </li>
                    <!--<li>-->
                        <!--<el-input class="search-input" placeholder="キーワード検索" v-model="workerInput"   prefix-icon="searchForm-submit"></el-input>-->
                        <!--<el-button @click="search">検索</el-button>-->
                    <!--</li>-->
                </div>
            </div>
            <div class="content">
                <ul class="person-inf">
                    <li class="largavatar"><img v-bind:src="friendInformation.file"></li>
                    <h3>{{friendInformation.last_name}}</h3>
                    <h3>{{friendInformation.first_name}}</h3>
                    <h3>({{friendInformation.email}})</h3>
                </ul>
                <table class="list-table">
                    <tr>
                        <td>職種・対応可能な分野</td>
                        <td>
                            <div v-for="categorie in friendInformation.categories">
                                <span v-if="categorie">{{categorie.category_name1}}</span>
                                <span v-if="categorie">{{categorie.category_name2}}</span>
                                <span v-if="categorie">
                                        <span v-if="!categorie.years_of_experience || categorie.years_of_experience === ''">未入力</span>
                                        <span v-if="categorie.years_of_experience === 'under1'">一年未満</span>
                                        <span v-if="categorie.years_of_experience === 'over1'">1年以上</span>
                                        <span v-if="categorie.years_of_experience === 'over3'">3年以上</span>
                                        <span v-if="categorie.years_of_experience === 'over10'">10年以上</span>
                                    </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>得意な分野</td>
                        <td>
                            {{friendInformation.specialty}}
                        </td>
                    </tr>
                    <tr>
                        <td>住所</td>
                        <td>
                            {{friendInformation.pref_name}}{{friendInformation.city}}
                        </td>
                    </tr>
                    <tr>
                        <td>会社名・屋号</td>
                        <td>
                            <span v-if="friendInformation.company_div === '1'">法人</span>
                            <span v-else>個人</span>
                            <span v-if="friendInformation.corporate_name_position === '1'">
                                    <span v-if="friendInformation.corporate_type === '1'">株式会社</span>
                                    <span v-if="friendInformation.corporate_type === '2'">有限会社</span>
                                    <span v-if="friendInformation.corporate_type === '3'">合同会社</span>
                                </span>
                            <span style="padding-left: 20px">{{friendInformation.company_name}}</span>
                            <span v-if="friendInformation.corporate_name_position === '2'">
                                    <span v-if="friendInformation.corporate_type === '1'">株式会社</span>
                                    <span v-if="friendInformation.corporate_type === '2'">有限会社</span>
                                    <span v-if="friendInformation.corporate_type === '3'">合同会社</span>
                                </span>
                        </td>
                    </tr>

                    <tr>
                        <td>対応可能エリア</td>
                        <td>
                            {{friendInformation.workarea}}
                        </td>
                    </tr>
                    <tr>
                        <td>誕生日</td>
                        <td>
                            {{friendInformation.birth}}
                        </td>
                    </tr>
                    <tr>
                        <td>性別</td>
                        <td>
                            <span v-if="friendInformation.sex === '1'">男</span>
                            <span v-if="friendInformation.sex === '2'">女</span>
                        </td>
                    </tr>
                    <tr>
                        <td>免許資格</td>
                        <td>
                            {{friendInformation.license}}
                        </td>
                    </tr>
                    <tr>
                        <td>スキル</td>
                        <td>
                            {{friendInformation.skill}}
                        </td>
                    </tr>
                    <tr>
                        <td>電話番号</td>
                        <td>
                            {{friendInformation.telno1}}
                        </td>
                    </tr>
                    <tr>
                        <td>将来の夢</td>
                        <td>
                            {{friendInformation.dream}}
                        </td>
                    </tr>
                    <tr>
                        <td>座右の銘</td>
                        <td>
                            {{friendInformation.motto}}
                        </td>
                    </tr>
                    <tr>
                        <td>３～5年で実現したいこと</td>
                        <td>
                            {{friendInformation.things_to_realize}}
                        </td>
                    </tr>
                    <tr>
                        <td>ブロック理由</td>
                        <td>
                            {{friendInformation.block_message}}
                        </td>
                    </tr>
                </table>
                <div class="pro-button clearfix">
                    <router-link class="back" to="/worker">
                        <el-button>戻る</el-button>
                    </router-link>
                    <a v-if="friendInformation.block=== '1'" href="javascript:void(0)"
                       @click="workerBlock(friendInformation.id)">
                        <el-button>ブロック解除</el-button>
                    </a>
                    <a v-else href="javascript:void(0)" @click="showWorkerBlock">
                        <el-button>ブロックする</el-button>
                    </a>
                </div>
            </div>

        </el-main>
        <WorkerBlock :WorkerBlockVisible="WorkerBlockVisible"
                     :id="friendInformation.id" :block="friendInformation.block" :email="friendInformation.email"
                     :file="friendInformation.file" :last_name="friendInformation.last_name"
                     @reload="fetchFriendDetailInformation"
                     @changeShow="changeShow" ref="WorkerBlockRef"/>
    </el-container>
</template>

<script>
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../../mixins/Messages";
    import Calendar from "../../../mixins/Calendar";
    import WorkerBlock from "./WorkerBlock";
    export default {
        components: {
            UserProfile,
            WorkerBlock
        },
        mixins: [Messages,Calendar],
        data() {
            return {
              WorkerBlockVisible: false,
                friendInformation: {},
                workerInput: ''
            }
        },
        mounted() {
            this.$router.afterEach((to, from, next) => {
                window.scrollTo(0, 0)
            })
        },
        methods: {

            showWorkerBlock() {
                this.WorkerBlockVisible = true
            },
            changeShow(data) {
                if (data === 'false') {
                    this.WorkerBlockVisible = false
                } else {
                    this.WorkerBlockVisible = true
                }
            },
            search(){
                if(this.workerInput){
                    window.workerInput=this.workerInput;
                    this.$router.push({path: '/worker'});
                }
            },
            // 職人詳細画面
            fetchFriendDetailInformation: function () {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.workerDetail;
                axios.post('/api/workerDetail', {
                    userId: this.$route.params.id
                }).then((res) => {
                    this.friendInformation = res.data[0];
                    if (this.friendInformation.birth) {
                        this.friendInformation.birth = Calendar.dateFormat(this.friendInformation.birth);
                    }
                    if(res.data[0].file){
                        this.friendInformation.file='file/users/'+ res.data[0].file;
                    }else{
                        this.friendInformation.file='images/icon-chatperson.png';
                    }

                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    loading.close();
                });
                loading.close();
            },
            workerBlock(id){
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                axios.post('/api/workerBlock', {
                    id: id,
                }).then(res =>{
                    //      this.$options.methods.fetchFriends();
                    this.fetchFriendDetailInformation();
                    loading.close();
                }).catch(error => {
                    loading.close();
                    this.$alert(this.commonMessage.error.delete, {showClose: false});
                });
            },
        },
        created() {
            this.fetchFriendDetailInformation();
        },

    }
</script>
<style scoped>
  .el-icon-arrow-left{
    font-size: 70px;
    color: #a9a9ab;
    position: absolute;
    left: -80px;
    top: -29px;
  }
</style>

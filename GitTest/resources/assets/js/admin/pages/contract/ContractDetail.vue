<template>
    <el-container class="contractDatail">
        <el-header style="height: 122px;" class="header">
            <el-row type="flex" class="row-bg" justify="space-between">

                <el-col :span="6" class="headLeft">
                    <router-link to="/contract">
                        <li><img src="images/admin-img/contract_on@2x.png"></li>
                        <li class="title">
                            <p>契約者</p>
                            <p>Contractor</p>
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
                    <h2>契約者情報</h2>
                </li>
                <!--<li>-->
                <!--<el-input placeholder="キーワード検索" v-model="contractDatailinput"   prefix-icon="searchForm-submit" ></el-input>-->
                <!--<el-button>検索</el-button>-->
                <!--</li>-->
            </div>

            <div class="content">
                <ul class="person-inf">
                    <!--<li class="largavatar"  v-if="enterpriseUser.file"><img v-bind:src="'file/users/'+enterpriseUser.file"></li>-->
                    <!--<li class="largavatar"  v-else><img src="images/icon-chatperson.png" alt=""></li>-->
                    <h3>{{contractEnterprise.name}}</h3>
                    <h3>({{contractEnterprise.id}})</h3>
                </ul>
                <table class="list-table">
                    <tr>
                        <th rowspan="7">登録情報</th>
                        <td>会社名</td>
                        <td>{{contractEnterprise.name}}</td>
                    </tr>
                    <tr>
                        <td>郵便番号</td>
                        <td>〒 {{contractEnterprise.zip}}</td>
                    </tr>
                    <tr>
                        <td class="tdLeft">都道府県</td>
                        <td class="tdRight">{{contractEnterprise.pref}}</td>
                    </tr>
                    <tr>
                        <td>市区町村</td>
                        <td>{{contractEnterprise.town}}</td>
                    </tr>
                    <tr>
                        <td>番地</td>
                        <td>{{contractEnterprise.street}}</td>
                    </tr>
                    <tr>
                        <td>建物名</td>
                        <td>{{contractEnterprise.house}}</td>
                    </tr>
                    <tr>
                        <td>電話番号</td>
                        <td>{{contractEnterprise.tel}}</td>
                    </tr>
                </table>
                <table  class="list-table">
                    <tr>
                        <th rowspan="9">契約情報</th>
                        <td>会社名</td>
                        <td v-if="contractor">{{contractor.name}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>郵便番号</td>
                        <td v-if="contractor">〒 {{contractor.zip}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>都道府県</td>
                        <td v-if="contractor">{{contractor.pref}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>市区町村</td>
                        <td v-if="contractor">{{contractor.town}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>番地</td>
                        <td v-if="contractor">{{contractor.street}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>建物名</td>
                        <td v-if="contractor">{{contractor.house}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>電話番号</td>
                        <td v-if="contractor">{{contractor.tel}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>担当者</td>
                        <td v-if="contractor">{{contractor.people}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>備考</td>
                        <td v-if="contractor">{{contractor.remark}}</td>
                        <td v-else></td>
                    </tr>
                </table>
                <table  class="list-table">
                    <tr>
                        <th rowspan="3">契約プラン</th>
                        <td>プラン</td>
                        <td v-if="contractEnterprise">{{contractEnterprise.plan}}</td>
                        <td v-else></td>
                    </tr>
                    <tr>
                        <td>アカウント数</td>
                        <td v-if="contractEnterprise">{{contractEnterprise.amount}} 個</td>
                        <td v-else>0 個</td>
                    </tr>
                    <tr>
                        <td>データ容量(GB)</td>
                        <td v-if="contractEnterprise && contractEnterprise.storage">{{contractEnterprise.storage}}</td>
                        <td v-else>0</td>
                    </tr>
                </table>

                <div class="pro-button">
                    <a class="back" @click="back"><el-button>戻る</el-button></a>
                    <a class="edit" @click.prevent="editContract()"><el-button>変更</el-button></a>
                    <a class="delete" @click="deleteContract()"><el-button>削除</el-button></a>
                </div>
            </div>


            <!--</form>-->
        </el-main>
    </el-container>
    <!--/container-->

</template>

<script>
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../mixins/Messages";

    export default {
        components: {
            UserProfile,
        },
        mixins: [Messages],
        name: "ContractDetail",
        data() {
            return {
                contractEnterprise: {},  //契約者model
                contractor: {},  //契約者model
                enterpriseUser: {},
                contractDatailinput: ''
            }
        },
        methods: {
            fetch() {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.contractList;
                axios.get('/api/getContractDetail', {
                    params: {
                        enterpriseId: this.$route.query.enterprise_id,
                    }
                }).then((res) => {
                    this.contractEnterprise = res.data.enterprise;
                    this.contractor = res.data.contractor;
                    this.enterpriseUser = res.data.enterpriseUser;
                    this.getPlanSelect(this.contractEnterprise.plan);
                    loading.close();
                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                    loading.close();
                });
            },

            getPlanSelect(val) {
                if (val === 1) {
                    this.contractEnterprise.plan = '有料プラン';
                } else if (val === 2) {
                    this.contractEnterprise.plan = '有料プラン（改定前）';
                } else if (val === 3) {
                    this.contractEnterprise.plan = '無料トライアル';
                } else if (val === 4) {
                    this.contractEnterprise.plan = '永年無料プラン';
                } else {
                    this.contractEnterprise.plan = '';
                }
            },

            back() {
              this.$router.push({path: '/contract'});
            },

            deleteContract() {
                const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
                let errMessage = this.commonMessage.error.contractList;
                let delMessage = this.commonMessage.success.delete;
                this.$confirm(this.commonMessage.confirm.delete.message).then(() => {
                    axios.post('/api/deleteContract', {
                        enterpriseId: this.$route.query.enterprise_id,
                    }).then((res) => {
                        this.$alert(delMessage, {showClose: false});
                        this.back();
                        loading.close();
                    }).catch(error => {
                        this.$alert(errMessage, {showClose: false});
                        loading.close();
                    });
                }).catch(action => {
                    loading.close();
                });
            },

            editContract() {
                this.$router.push({path: '/contract/update', query: {enterprise_id: this.$route.query.enterprise_id}});
            },

        },
        created() {
            this.fetch();
        }
    }
</script>

<style scoped>
    .customercreateform .report-deteil-wrap .el-input__inner {
        padding: 0 15px !important;
    }
</style>

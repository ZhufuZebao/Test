<template>

  <el-container class="contractList">
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
          <h2>契約者一覧</h2>
          <el-button @click="create">新規登録</el-button>
          <el-button @click="contractCsv">CSV出力</el-button>
        </li>
        <li>
          <el-input placeholder="キーワード検索" @change="search" v-model="searchName" prefix-icon="searchForm-submit" ></el-input>
          <el-button @click="search">検索</el-button>
        </li>
      </div>
      <div>
          <ul class="tableHeader">
            <li>
              ID
            </li>
            <!--<li>-->
              <!--写真-->
            <!--</li>-->
            <li>
              契約者名
            </li>
            <li>
              契約情報
            </li>
            <!--<li class="customer-li">-->
              <!--契約期間-->
            <!--</li>-->
            <!--<li class="customer-li">-->
              <!--契約更新-->
            <!--</li>-->
            <li>
              操作
            </li>
          </ul>
        <ul class="tablecontent">
          <li class="clearfix btns" v-for="item in contracts">
            <span v-if="item.user">{{item.id}}</span>
            <!--<span class="avatar" v-if="item.user">-->
                  <!--<img :src="'file/users/'+item.user.file" alt="" v-if="item.user && item.user.file"-->
                       <!--class="avatar">-->
                <!--<img src="images/icon-chatperson.png" alt="" v-else class="invite-photo">-->
            <!--</span>-->
            <span v-if="item.user">{{item.name}}</span>
            <span v-if="item.plan === 1">有料プラン</span>
            <span v-else-if="item.plan === 2">有料プラン（改定前）</span>
            <span v-else-if="item.plan === 3">無料トライアル</span>
            <span v-else-if="item.plan === 4">永年無料プラン</span>
            <span v-else></span>

            <span v-if="item.user">
              <router-link :to="{ path: '/contract/detail', query: {enterprise_id: item.id}}">
               <el-button>詳細</el-button>
              </router-link>
                <router-link :to="{ path: '/contract/office', query: {enterprise_id: item.id,user_id:item.user.id}}">
               <el-button>利用状況</el-button>
              </router-link>
              <a @click="showAccount(item.id)"><el-button>アカウント</el-button></a>

              <!--<div>-->
                <el-popover popper-class="model-contract" placement="bottom" width="300" trigger="click" @hide="clearCheckBox">
                  <div slot="reference" class="contract-model-joiner"><a @click="showFriend(item.id)"><el-button>職人登録</el-button></a></div>
                  <div class="popover-div">
                    <ul class="popover-checkbox">
                      <li class="popover-li">職人登録するユーザを選択</li>
                      <el-checkbox-group v-model="checkboxArr">
                        <li v-for="item in operators" :key="'operator-'+item.user.id">
                          <el-checkbox :label="item.user.id">
                            <p class="avatar" v-if="item.user && item.user.file"><img :src="'file/users/'+item.user.file"><span>{{item.user.name}}</span></p>
                            <p class="avatar" v-else><img src="images/icon-chatperson.png"><span>{{item.user.name}}</span></p>
                          </el-checkbox>
                        </li>
                      </el-checkbox-group>
                      <li class="pr">
                        <el-button  @click="submitOperators(item.id)">職人登録</el-button>
                      </li>
                    </ul>
                  </div>
                </el-popover>
              <!--</div>-->
            </span>
          </li>
        </ul>
        <accountList :enterpriseId="enterpriseId" @closeAccount="closeAccount" v-if="showAccountModal" class="contractList-accountList"/>
      </div>

    </el-main>

  </el-container>


</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";
  import AccountList from "../../../components/account/AccountList";

  export default {
    components: {
      UserProfile,
      AccountList
    },
    mixins: [Messages],
    data() {
      return {
        contracts: {},
        searchName: '',      // 詳細検索対象
        showAccountModal: false,
        enterpriseId: '',
        checkboxArr:[],
        operators:{},        // 運営
      }
    },
    name: "ContractList",
    methods:{
      clearCheckBox(){
        this.checkboxArr = [];
      },
      submitOperators(enterpriseId){
        if (this.checkboxArr.length < 1){
          return;
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contractList;
        let successMessage = this.commonMessage.success.insert;
        axios.post('/api/addContractFriend', {
              friendUserIds: this.checkboxArr,
              enterpriseId:enterpriseId
            }
        ).then((res) => {
          this.$alert(successMessage, {showClose: false});
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },

      //Account一覧は閉じています
      closeAccount() {
        this.showAccountModal = false;
        $('.calendar-day-sckedule-wrap').css({"z-index": 1});
        $('.schedule-week-index').css({"z-index": 1});
      },

      //account
      showAccount(enterpriseId){
        this.enterpriseId = enterpriseId;
        this.showAccountModal = true;
      },

      //職人登録
      showFriend(enterpriseId){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contractList;
        axios.post('/api/getOperatorUsers',{
              enterpriseId:enterpriseId
            }
        ).then((res) => {
          this.operators = res.data;
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },

      //検索
      search() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contractList;
        axios.post('/api/searchContract', {
              keyword: this.searchName
            }
        ).then((res) => {
          this.contracts = res.data;
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },

      //新規
      create(){
        // window.open(window.location.origin+process.env.MIX_APP_DIR+'#/enterprise/register');
        this.$router.push({path: '/contract/enterpriseCreate'});
      },
      contractCsv(){
        window.location.href = window.BASE_URL + '/api/contractCsv';
      },
      fetch(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contractList;
        axios.get('/api/getContractList'
        ).then((res) => {
          this.contracts = res.data;
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
    },
    created() {
      this.fetch();
    },
  }
</script>

<template>
  <!--container-->
  <el-container class="contract-office">
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

    <!--customer-wrapper-->
    <el-main class="container">
      <div class="part1">
        <li>
          <h2>利用状況</h2>
        </li>
      </div>
      <UserLab :friendInformation="friendInformation"></UserLab>
      <div class="content">
          <li class="contract-office-usercount"><span>登録アカウント数 </span><span>{{userCount+InviteCount}}/{{amountCount}}(内協力会社{{InviteCount}})</span></li>
          <el-tabs class="contractlab" v-model="activeContract">
            <el-tab-pane label="アカウント数消費" name="showConsume">
              <ContractConsume :enterprise_id="this.$route.query.enterprise_id" @sendAmountCount="getAmountCount"/>
            </el-tab-pane>
            <el-tab-pane label="案件の注目頻度" name="showBrowse">
              <ContractBrowse :enterprise_id="this.$route.query.enterprise_id"/>
            </el-tab-pane>
            <el-tab-pane label="データ容量状況" name="showContain">
              <ContractContain :enterprise_id="this.$route.query.enterprise_id"/>
            </el-tab-pane>
            <el-tab-pane label="ログイン履歴" name="showHistory">
              <ContractHistory :enterprise_id="this.$route.query.enterprise_id"/>
            </el-tab-pane>
            <el-tab-pane label="各機能使用頻度" name="showRate">
              <ContractRate :enterprise_id="this.$route.query.enterprise_id"/>
            </el-tab-pane>
          </el-tabs>
        <div class="pro-button">
          <a class="back" @click="back"><el-button>戻る</el-button></a>
        </div>
      </div>
    </el-main>
  </el-container>
  <!--/container-->

</template>

<script>
    import UserProfile from "../../components/common/UserProfile";
    import Messages from "../../../mixins/Messages";
    import Calendar from "../../../mixins/Calendar";
    import ContractContain from '../../components/contract/ContractContain';
    import ContractConsume from '../../components/contract/ContractConsume';
    import ContractHistory from '../../components/contract/ContractHistory';
    import ContractRate from '../../components/contract/ContractRate';
    import ContractBrowse from '../../components/contract/ContractBrowse';
    import UserLab from '../../components/contract/UserLab';
    export default {
        components: {
            UserProfile,
            ContractContain,
            ContractConsume,
            ContractHistory,
            ContractRate,
            ContractBrowse,
            UserLab,
        },
        mixins: [Messages,Calendar],
        data() {
            return {
                friendInformation: {},
                user:{},
                activeContract: 'showConsume',
              contractoffice:'',
              userCount:0,
              amountCount:0,
              InviteCount:0
            }
        },
        methods: {
          back() {
            this.$router.push({path: '/contract'});
          },
            search(){},
            userDatial(){
                let errMessage = this.commonMessage.error.system;
                axios.post('/api/contractAccount',{
                    enterpriseId : this.$route.query.enterprise_id,
                }).then((res) => {
                    this.friendInformation=res.data;
                }).catch(error => {
                    this.$alert(errMessage, {showClose: false});
                });
            },
          getAmountCount(userCount,amountCount,InviteCount){
            this.userCount = userCount;
            this.amountCount = amountCount;
            this.InviteCount = InviteCount;
          },
        },
        created() {
        },
        watch: {
        },
        mounted() {
            this.userDatial();
        }
    }
</script>

<template>
  <!--container-->
  <el-container class="c">
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
          <h2>契約者情報</h2>
        </li>
        <!--<li>-->
          <!--<el-input placeholder="キーワード検索" prefix-icon="searchForm-submit"></el-input>-->
          <!--<el-button>検索</el-button>-->
        <!--</li>-->
      </div>
      <!--<form>-->
      <div class="content">
        <ul class="person-inf">
          <li class="largavatar"  v-if="enterpriseUser.file"><img v-bind:src="'file/users/'+enterpriseUser.file"></li>
          <li class="largavatar"  v-else><img src="images/icon-chatperson.png" alt=""></li>
          <h3>{{contractEnterprise.name}}</h3>
          <h3>({{enterpriseUser.email}})</h3>
        </ul>
        <el-form :model="contractEnterprise" label-width="200px" :rules="rules" ref="form">

          <div class="form-group">
            <el-form-item label="会社名" prop="name">
              <el-input v-model="contractEnterprise.name" maxlength="50"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="郵便番号" prop="zip">
              <el-input v-model="contractEnterprise.zip" maxlength="7"></el-input>
              <p class="p-tip">※(半角数字)ハイフン(-)なしで入力してください。</p>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="都道府県" prop="pref">
              <el-input v-model="contractEnterprise.pref" maxlength="20"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="市区町村" prop="town">
              <el-input v-model="contractEnterprise.town" maxlength="30"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="番地" prop="street">
              <el-input v-model="contractEnterprise.street" maxlength="20"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="建物名" prop="house">
              <el-input v-model="contractEnterprise.house" maxlength="70"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="電話番号" prop="tel">
              <el-input v-model="contractEnterprise.tel" maxlength="30"></el-input>
            </el-form-item>
          </div>
        </el-form>
        <h3>契約情報</h3>
        <el-form :model="contractor" label-width="200px" :rules="contractorRules" ref="contractorForm">
          <div class="form-group">
            <el-form-item label="会社名" prop="name">
              <el-input v-model="contractor.name" maxlength="50"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="郵便番号" prop="zip">
              <el-input v-model="contractor.zip" maxlength="7"></el-input>
              <p class="p-tip">※(半角数字)ハイフン(-)なしで入力してください。</p>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="都道府県" prop="pref">
              <el-input v-model="contractor.pref" maxlength="20"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="市区町村" prop="town">
              <el-input v-model="contractor.town" maxlength="30"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="番地" prop="street">
              <el-input v-model="contractor.street" maxlength="20"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="建物名" prop="house">
              <el-input v-model="contractor.house" maxlength="70"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="電話番号" prop="tel">
              <el-input v-model="contractor.tel" maxlength="15"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="担当者" prop="people">
              <el-input v-model="contractor.people" maxlength="30"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="備考">
              <el-input type="textarea" :rows="4" placeholder="メッセージ" v-model="contractor.remark">
              </el-input>
            </el-form-item>
          </div>
        </el-form>
        <h3>契約プラン</h3>
        <el-form :model="contractEnterprise"  :rules="contractEnterpriseRules" label-width="200px" ref="contractEnterpriseForm">
          <div class="form-group">
            <el-form-item label="プラン">
              <el-select v-model="contractEnterprise.plan" placeholder='' icon="el-icon-caret-bottom" popper-class="select-common"
                         split-button="true" type="el-icon-caret-bottom">
                <el-option v-for="plan in planArr" :key="plan.id" :label="plan.name"
                           :value="plan.id"></el-option>
              </el-select>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="アカウント数" prop="amount">
              <el-input v-model="contractEnterprise.amount" maxlength="11"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="データ容量(GB)" prop="storage">
              <el-input v-model="contractEnterprise.storage" maxlength="10"></el-input>
            </el-form-item>
          </div>
        </el-form>
        <div class="pro-button">
          <a class="back" @click="back">
            <el-button>戻る</el-button>
          </a>
          <a class="edit" @click.prevent="save()">
            <el-button>変更</el-button>
          </a>
        </div>
      </div>
    </el-main>
  </el-container>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";
  import validation from '../../validations/contract.js'

  export default {
    components: {
      UserProfile,
    },
    mixins: [Messages,validation],
    name: "ContractUpdate",
    data() {
      return {
        contractEnterprise: {
          amount:'',
          plan:'',
          storage:''
        },  //契約者model
        enterpriseUser: {},
        contractor: {
          name:'',
          zip:'',
          pref:'',
          town:'',
          street:'',
          house:'',
          tel:'',
          people:'',
          remark:'',
        },  //契約者model
        planArr: [
          {"name": '有料プラン', "id": '1'},
          {"name": '有料プラン（改定前）', "id": '2'},
          {"name": '無料トライアル', "id": '3'},
          {"name": '永年無料プラン', "id": '4'},
        ]
      }
    },
    methods:{
      fetchDetail(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contractList;
        axios.get('/api/getContractDetail', {
          params: {
            enterpriseId : this.$route.query.enterprise_id,
          }
        }).then((res) => {
          this.contractEnterprise = res.data.enterprise;
          this.enterpriseUser = res.data.enterpriseUser;
          if(res.data.contractor){
            this.contractor = res.data.contractor;
          }
          this.contractEnterprise.amount = this.contractEnterprise.amount+'';
          this.contractEnterprise.storage = this.contractEnterprise.storage+'';
          this.getPlanSelect(this.contractEnterprise.plan);
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },

      getPlanSelect(val){
        if (val === 1) {
          this.contractEnterprise.plan = '有料プラン';
        } else if (val === 2) {
          this.contractEnterprise.plan = '有料プラン（改定前）';
        } else if (val === 3) {
          this.contractEnterprise.plan = '無料トライアル';
        } else if (val === 4) {
          this.contractEnterprise.plan = '永年無料プラン';
        } else {
          this.contractEnterprise.plan = '有料プラン';
        }
      },

      back(){
        this.$router.push({path: '/contract/detail', query: {enterprise_id: this.$route.query.enterprise_id}});
      },

      save(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contractList;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            this.$refs['contractorForm'].validate((valid) => {
              if (valid) {
                this.$refs['contractEnterpriseForm'].validate((valid) => {
                  if (valid) {
                    axios.post('/api/updateContractDetail', {
                      enterpriseId: this.$route.query.enterprise_id,
                      contractEnterprise: this.contractEnterprise,
                      contractor: this.contractor,
                    }).then((res) => {
                      this.back();
                      loading.close();
                    }).catch(error => {
                      this.$alert(errMessage, {showClose: false});
                      loading.close();
                    });
                  } else {
                    // エラーフォーカス
                    document.body.scrollTop = 0;
                    document.documentElement.scrollTop = 0;
                    for (let i in this.$refs['contractorForm'].fields) {
                      let field = this.$refs['contractorForm'].fields[i];
                      if (field.validateState === 'error') {
                        field.$el.querySelector('input').focus();
                        break;
                      }
                    }
                  }
                })
              } else {
                // エラーフォーカス
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
                for (let i in this.$refs['contractorForm'].fields) {
                  let field = this.$refs['contractorForm'].fields[i];
                  if (field.validateState === 'error') {
                    field.$el.querySelector('input').focus();
                    break;
                  }
                }
              }
            });
          } else {
            // エラーフォーカス
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
            for (let i in this.$refs['form'].fields) {
              let field = this.$refs['form'].fields[i];
              if (field.validateState === 'error') {
                field.$el.querySelector('input').focus();
                break;
              }
            }
          }
        })
      },
    },
    created() {
      this.fetchDetail();
    }
  }
</script>

<style scoped>

</style>

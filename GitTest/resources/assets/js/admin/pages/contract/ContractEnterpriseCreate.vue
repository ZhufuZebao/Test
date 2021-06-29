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
          <h2>事業者情報登録</h2>
        </li>
      </div>
      <!--<form>-->
      <div class="content">
        <el-form :model="enterprise" label-width="200px" :rules="enterpriseRules" ref="form">
          <div class="form-group">
            <el-form-item label="会社名" prop="name">
              <el-input v-model="enterprise.name" maxlength="50"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="郵便番号" prop="zip">
              <el-input v-model="enterprise.zip" maxlength="7"></el-input>
              <p class="p-tip">※(半角数字)ハイフン(-)なしで入力してください。</p>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="都道府県" prop="pref">
              <el-input v-model="enterprise.pref" maxlength="20"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="市区町村" prop="town">
              <el-input v-model="enterprise.town" maxlength="30"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="番地" prop="street">
              <el-input v-model="enterprise.street" maxlength="20"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="建物名" prop="house">
              <el-input v-model="enterprise.house" maxlength="70"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
          <el-form-item label="電話番号" prop="tel">
            <el-input v-model="enterprise.tel" maxlength="15"></el-input>
          </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="管理者名" prop="userLastName">
              <el-input v-model="enterprise.userLastName" placeholder="姓" maxlength="59"></el-input>
            </el-form-item>
            <el-form-item prop="userFirstName">
              <el-input v-model="enterprise.userFirstName" placeholder="名" maxlength="59"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="管理者メールアドレス" prop="userEmail">
              <el-input v-model="enterprise.userEmail" maxlength="191"></el-input>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="管理者パスワード" prop="userPassword">
              <el-input type="password" v-model="enterprise.userPassword" maxlength="191"></el-input>
            </el-form-item>
          </div>
          <h3 style="font-size: 18px;font-weight: bold;margin-bottom: 20px">契約プラン</h3>
          <div class="form-group">
            <el-form-item label="プラン">
              <el-select v-model="enterprise.plan" placeholder='' icon="el-icon-caret-bottom" popper-class="select-common"
                         split-button="true" type="el-icon-caret-bottom">
                <el-option v-for="plan in planArr" :key="plan.id" :label="plan.name"
                           :value="plan.id"></el-option>
              </el-select>
            </el-form-item>
          </div>
          <div class="form-group">
            <el-form-item label="アカウント数" prop="amount">
              <el-input v-model="enterprise.amount" maxlength="11"></el-input>
            </el-form-item>
          </div>
        </el-form>
        <div class="pro-button">
          <a class="back" @click="back">
            <el-button>戻る</el-button>
          </a>
          <a class="edit" @click.prevent="save()">
            <el-button>登録</el-button>
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
    mixins: [Messages,validation],
    components: {
      UserProfile,
    },
    name: "ContractEnterpriseCreate",
    data() {
      return {
        enterprise: {
          name:'',
          zip:'',
          pref:'',
          town:'',
          street:'',
          house:'',
          tel:'',
          userLastName:'',
          userFirstName:'',
          userEmail:'',
          userPassword:'',
          plan:'1',
          amount:'999',
        },  //model
        planArr: [
          {"name": '有料プラン', "id": '1'},
          {"name": '有料プラン（改定前）', "id": '2'},
          {"name": '無料トライアル', "id": '3'},
          {"name": '永年無料プラン', "id": '4'},
        ]
      }
    },
    methods:{
      back(){
        this.$router.push({path: '/contract'});
      },

      save(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contractList;
        let successMessage = this.commonMessage.success.insert;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            axios.post('/api/addContractEnterprise', {
              enterprise: this.enterprise,
            }).then((res) => {
              this.$alert(successMessage, {showClose: false});
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
            for (let i in this.$refs['form'].fields) {
              let field = this.$refs['form'].fields[i];
              if (field.validateState === 'error') {
                field.$el.querySelector('input').focus();
                break;
              }
            }
            loading.close();
          }
        })

      },
    }
  }
</script>

<style scoped>

</style>

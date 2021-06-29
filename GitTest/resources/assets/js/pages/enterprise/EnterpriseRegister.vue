<template>
  <div class="enterprise-common enterprise-register">
    <div class="image">
      <div class="logo"></div>
    </div>
    <el-form :model="enterprise" :rules="rules" ref="form">
      <table class="registerTable">
        <tr>
          <td colspan="2">事業者情報登録</td>
        </tr>
        <tr>
          <td>※会社名</td>
          <td>
            <el-form-item prop="name">
              <el-input v-model="enterprise.name" class="EnterpriseInput" maxlength="50"></el-input>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※郵便番号</td>
          <td>
            <el-form-item prop="zip">
              <el-input v-model="enterprise.zip" class="EnterpriseInput" maxlength="7"></el-input>
              <div class="message">（＊半角数字）ハイフン（‐）なしで入力してください。</div>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※都道府県</td>
          <td>
            <el-form-item prop="pref">
              <el-input v-model="enterprise.pref" class="EnterpriseInput" maxlength="20"></el-input>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※市区町村</td>
          <td>
            <el-form-item prop="town">
              <el-input v-model="enterprise.town" class="EnterpriseInput" maxlength="30"></el-input>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※番地</td>
          <td>
            <el-form-item prop="street">
              <el-input v-model="enterprise.street" class="EnterpriseInput" maxlength="20"></el-input>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>建物名</td>
          <td>
            <el-form-item prop="house">
              <el-input v-model="enterprise.house" class="EnterpriseInput" maxlength="70"></el-input>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※電話番号</td>
          <td>
            <el-form-item prop="tel">
              <el-input v-model="enterprise.tel" class="EnterpriseInput" maxlength="15"></el-input>
              <div class="message">（＊半角数字）ハイフン（‐）なしで入力してください。</div>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※管理者名</td>
          <td>
            <el-form-item prop="userLastName" class="enterprise-userName">
              <el-input v-model="enterprise.userLastName" class="EnterpriseInput enterpriseInput-name" placeholder="姓" maxlength="59"></el-input>
            </el-form-item>
            <el-form-item prop="userFirstName" class="enterprise-userName">
              <el-input v-model="enterprise.userFirstName" class="EnterpriseInput enterpriseInput-name" placeholder="名" maxlength="59"></el-input>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※管理者メールアドレス</td>
          <td>
            <el-form-item prop="userEmail">
              <el-input v-model="enterprise.userEmail" class="EnterpriseInput" maxlength="191"></el-input>
              <div class="message">※入力したメールアドレスに認証キーを送信する</div>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※管理者パスワード</td>
          <td>
            <el-form-item prop="userPassword">
              <el-input type="password" v-model="enterprise.userPassword" class="EnterpriseInput" maxlength="20"></el-input>
            </el-form-item>
          </td>
        </tr>
        <tr>
          <td>※パスワード再入力</td>
          <td>
            <el-form-item prop="password_confirmation">
              <el-input type="password" v-model="enterprise.password_confirmation" class="EnterpriseInput" maxlength="20"></el-input>
            </el-form-item>
          </td>
        </tr>
      </table>
      <el-form-item>
        <div class="but">
          <router-link to="/enterprise/Rule">
            <el-button class="back">戻る</el-button>
          </router-link>

          <el-button class="sure" @click="validation()">確認</el-button>
        </div>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
  import validation from '../../validations/enterprise'

  export default {
    name: "EnterpriseRegister",
    mixins: [validation],
    data() {
      return {
        zipFlag:0,
        enterprise: {pref: "", town: "", street: "", userLastName: "", userFirstName: ""}
      }
    }, methods: {
      //情報登録メッセージを検証
      validation: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            sessionStorage.setItem('data', JSON.stringify(this.$data.enterprise));
            this.$router.push({path: '/enterprise/registerSure'})
          }
        })
      },
      //事業者情報確認画面帰る,事業者情報登録画面のメッセージ保留
      fetchMsg: function () {
        let data = JSON.parse(sessionStorage.getItem('data'));
        if (data) {
          this.enterprise = data;
        }
      }
    },
    created() {
      this.fetchMsg()
    }
  }
</script>
<style>
  .registerTable .el-form-item__error {
    left: 20%;
  }
</style>

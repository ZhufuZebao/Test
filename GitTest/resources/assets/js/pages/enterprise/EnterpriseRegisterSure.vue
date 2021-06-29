<template>
  <div class="enterprise-sure">
    <div class="image">
      <div class="logo"></div>
    </div>
    <table class="registerTable">
      <tr>
        <td colspan="2">事業者情報確認</td>
      </tr>
      <tr>
        <td>※会社名</td>
        <td><input type="text" v-model="enterprise.name" disabled></td>
      </tr>
      <tr>
        <td>※郵便番号</td>
        <td>
          <input type="text" v-model="enterprise.zip" disabled>
        </td>
      </tr>
      <tr>
        <td>※都道府県</td>
        <td><input type="text" v-model="enterprise.pref" disabled></td>
      </tr>
      <tr>
        <td>※市区町村</td>
        <td><input type="text" v-model="enterprise.town" disabled></td>
      </tr>
      <tr>
        <td>※番地</td>
        <td><input type="text" v-model="enterprise.street" disabled></td>
      </tr>
      <tr>
        <td>建物名</td>
        <td><input type="text" v-model="enterprise.house" disabled></td>
      </tr>
      <tr>
        <td>※電話番号</td>
        <td>
          <input type="text" v-model="enterprise.tel" disabled>
        </td>
      </tr>
      <tr>
        <td>※管理者名</td>
        <td><input type="text" v-model="user.name" disabled></td>
      </tr>
      <tr>
        <td>※管理者メールアドレス</td>
        <td><input type="text" v-model="user.email" disabled></td>
      </tr>
      <tr>
        <td>※管理者パスワード</td>
        <td>
          <input type="password" v-model="user.password" disabled>
        </td>
      </tr>
    </table>
    <div class="but">
      <router-link :to="{path:'/enterprise/register'}">
        <button class="back">戻る</button>
      </router-link>
      <router-link :to="{path:'/enterprise/mailValidate'}">
        <button class="sure" @click="enterpriseLogin()">仮登録</button>
      </router-link>
    </div>
  </div>
</template>

<script>
  export default {
    name: "EnterpriseRegisterSure",
    data() {
      return {
        enterprise: {},
        user: {
          name: '',
          email: '',
          password: '',
          emailSendFlag:'',
          firstName: '',
          lastName: '',
        }
      }
    }, methods: {
      enterpriseLogin() {
        sessionStorage.setItem('emailSendFlag','send')
      }
    },
    created() {
      //sessionにデータを取得
      let data = JSON.parse(sessionStorage.getItem('data'));
      if (data != null) {
        this.enterprise = data;
        // this.user.name = data.userName;
        this.user.name = data.userLastName + data.userFirstName;
        this.user.firstName = data.userFirstName;
        this.user.lastName = data.userLastName;
        this.user.email = data.userEmail;
        this.user.password = data.userPassword;
      }
    }
  }
</script>

<template>
  <div class="container clearfix setup">
    <header>
      <h1><a href="javascript:void(0)">設定</a></h1>
      <ul class="header-nav friend">

      </ul>
      <div class="title-wrap">
      </div>
      <UserProfile/>
      <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    </header>
    <section class="report-wrapper">

      <h3 class="font-green">パスワード変更</h3>

      <div class="form-group">
        <label class="control-label visible-ie4 " for="oldPassword">元のパスワード</label>
        <input class="form-control placeholder-no-fix" type="password" autocomplete="off"
               v-model="saveForm.oldPassword" id="oldPassword">
        <span class="danger">{{oldPasswordContext}}</span>
      </div>
      <div class="form-group">
        <label class="control-label visible-ie4 " for="password">新のパスワード</label>
        <input class="form-control placeholder-no-fix" type="password" autocomplete="off"
               id="password"
               v-model="saveForm.password">
        <span class="danger">{{passwordContext}}</span>
      </div>
      <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9" for="passwordConfirmation">パスワード確認</label>
        <input class="form-control placeholder-no-fix" type="password" autocomplete="off"
               v-model="saveForm.passwordConfirmation" id="passwordConfirmation"></div>
      <span class="danger">{{passwordConfirmationContext}}</span>
      <div class="form-actions">
        <button type="button" id="register-submit-btn" class="btn btn-success uppercase pull-right"
                @click="edit">設定
        </button>
        <router-link to="/setting">キャンセル</router-link>
      </div>
    </section>
  </div>
</template>

<script>
  import UserProfile from '../components/common/UserProfile';
  import axios from 'axios';
  export default {
    components: {UserProfile},
    name: 'ResetPwd',
    data() {
      return {
        oldPasswordContext: '',
        passwordContext: '',
        passwordConfirmationContext: '',
        saveForm: {
          oldPassword: '',
          password: '',
          passwordConfirmation: '',
        }

      }
    },
    methods: {
      edit: function () {
        if (this.saveForm.oldPassword === '') {
          this.oldPasswordContext = 'パスワードが空です';
          return false;
        } else {
          this.oldPasswordContext = ''

        }
        if (this.saveForm.password === '') {
          this.passwordContext = 'パスワードが空です';
          return false;
        } else {
          this.passwordContext = '';
          this.passwordConfirmationContext = '';
        }
        if (this.saveForm.passwordConfirmation !== this.saveForm.password) {
          this.passwordContext = 'パスワードが違います';
          this.passwordConfirmationContext = 'パスワードが違います';
          return false;
        }
        axios.post('/api/setting/pwd', {
          saveForm: this.saveForm
        }).then(res => {
          if (res.data === 0) {
            console.log(res.data);
            this.oldPasswordContext = 'パスワードエラー';
            this.$router.push({path: '/setting/pwd'});
          } else if (res.data === 1) {
            alert('パスワードを変更しました。');
            this.$router.push(location.reload());
          } else {
            this.passwordContext = res.data[0]
          }
        });
      }
    }

  }
</script>

<style scoped>
  .danger {
    color: #ff0000;
  }
</style>
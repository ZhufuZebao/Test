<template>
  <!--container-->
  <div class="container">
    <header>
      <h1><a href=""></a></h1>
      <div class="title-wrap ">
        <h2 style="margin-top: 7px">アカウント追加</h2>
      </div>
      <UserProfile/>
    </header>
    <section class="project-wrapper">
      <div class="report-deteil-wrap edit clearfix">
        <el-form :model="account" :rules="rules" ref="form" label-width="200px">
          <el-form-item label="アカウント名" prop="name">
            <el-input prefix-icon="el-icon-user-solid" v-model="account.name" maxlength="30"></el-input>
          </el-form-item>

          <el-form-item label="メールアドレス" prop="email">
            <el-input prefix-icon="el-icon-message" v-model="account.email" maxlength="30"></el-input>
          </el-form-item>

          <el-form-item label="パスワード" prop="password">
            <el-input prefix-icon="el-icon-lock" type="password" v-model="account.password"
                      maxlength="20">
            </el-input>
          </el-form-item>

          <el-form-item label="パスワード再入力" prop="passwordConfirm">
            <el-input prefix-icon="el-icon-lock" type="password"
                      v-model="account.passwordConfirm" maxlength="20">
            </el-input>
          </el-form-item>

          <el-form-item label="アカウント区分">
            <el-radio v-model="account.type" label="3">管理アカウント</el-radio>
            <el-radio v-model="account.type" label="2">一般アカウント</el-radio>
          </el-form-item>
        </el-form>
        <div class="button-wrap clearfix">
          <div class="button-lower">
            <router-link to="/account">戻る</router-link>
          </div>
          <div class="button-lower remark"><a href="javascript:void(0)" @click="create">追加</a></div>
        </div>
        <MessageDialog :showMessage="showMessage" @messageShowOver="messageShowOver" v-if="messageShowFlg"/>
      </div>
    </section>
  </div>
  <!--/container-->
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import MessageDialog from '../../components/common/MsgDialog.vue'
  import validation from '../../validations/account'
  import Messages from "../../mixins/Messages";

  export default {
    name: "AccountCreate",
    components: {
      MessageDialog,
      UserProfile,
    },
    mixins: [validation, Messages],
    data: function () {
      return {
        account: {type: '2'},
        saved: false,
        passwordConfirm: '',
        showMessage: '',
        messageShowFlg: false,
      }
    },
    methods: {
      messageShowOver: function () {
        this.messageShowFlg = false;
      },
      create: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post("/api/setAccount", {
              account: this.account,
              email: this.account.email,
            }).then(res => {
              loading.close();
              this.$alert(this.commonMessage.success.insert, {showClose: false});
              if (res.data.errors !== undefined) {
                let errorMessage = '';
                for (let i = 0; i < res.data.errors.length; i++) {
                  errorMessage += res.data.errors[i];
                }
                this.showMessage = errorMessage;
                this.messageShowFlg = true;
              } else {
                this.saved = true;
                this.$router.push({path: "/account"});
              }
            }).catch(error => {
              loading.close();
              this.$alert(this.commonMessage.error.insert, {showClose: false});
            });
          }
        })
      },
    },
  }
</script>
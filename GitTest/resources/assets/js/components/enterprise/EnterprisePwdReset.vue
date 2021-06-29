<template>
  <transition name="fade">
        <section class="enterprise-reset">
          <h3 class="modal-content-head">パスワード変更</h3>
          <div class=" edit clearfix">
            <el-form :rules="pwd" ref="form" :model="user">
              <el-form-item prop="oldPassword">
                <el-input id="oldPassword" type="password" placeholder="現在のパスワード" v-model="user.oldPassword">
                </el-input>
                <span>{{oldPasswordContext}}</span>
              </el-form-item>
              <el-form-item prop="password">
                <el-input id="password" type="password" placeholder="新しいパスワード" v-model="user.password" maxlength="100">
                </el-input>
              </el-form-item>
              <el-form-item prop="password_confirmation">
                <el-input id="passwordConfirmation" type="password" placeholder="新しいパスワード（確認）"
                          v-model="user.password_confirmation">
                </el-input>
              </el-form-item>
            </el-form>
          </div>
          <div class="pro-button">
              <a class="modoru" href="javascript:void(0)" @click="$emit('closePwdResetModel')">戻る</a>
              <a class="nextPage" href="javascript:void(0)" @click="edit">変更する</a>
          </div>
        </section>
  </transition>
</template>

<script>
  import validation from '../../validations/user';
  import Messages from "../../mixins/Messages";

  export default {
    components: {
    },
    mixins: [
      validation,
      Messages
    ],
    name: "EnterprisePwdReset",
    data: function () {
      return {
        successMsg:'',
        show: true,
        createOk: false,
        isMounted: false,
        oldPasswordContext: '',
        passwordContext: '',
        passwordConfirmationContext: '',
        user: {
          oldPassword: '',
          password: '',
          passwordConfirmation: '',
        }
      }
    },
    mounted() {
      this.isMounted = true;
    },
    methods: {
      //パスワード修正フォーム提出
      edit: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            let errMsg = this.commonMessage.error.passwordReset;
            let successMsg=this.commonMessage.success.password;
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post('/api/setEnterprisesList/pwd', {
              user: this.user
            }).then(res => {
              if (res.data.result === 0) {
                this.$alert(successMsg, {showClose: false}).then(() => {
                  this.$emit('closePwdResetModel');
                  loading.close();
                });
              } else {
                this.$alert(res.data.errors, {showClose: false});
                loading.close();
              }
            }).catch(e => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });
          }
        })
      }
    }
  }
</script>


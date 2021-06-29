<template>
  <transition name="fade">
    <section class="enterprise-reset account-create">
      <h3 class="modal-content-head" v-if="!accountId">アカウントを追加する</h3>
      <h3 class="modal-content-head" v-else>アカウントを変更する</h3>
      <div class=" edit clearfix">
        <el-form :model="account" :rules="rules" ref="form">
          <div class="form-group form-name">
            <el-form-item prop="last_name"   class="form-last-name">
              <el-input v-model="account.last_name" id="firstInput" maxlength="59" :readonly="account.disable" placeholder="姓"
                        @blur="checkForm"></el-input>
            </el-form-item>
            <el-form-item prop="first_name" class="form-first-name">
              <el-input v-model="account.first_name" maxlength="59" :readonly="account.disable" placeholder="名"
                        @blur="checkForm"></el-input>
            </el-form-item>
          </div>
          <el-form-item prop="email" v-if="!accountId">
            <el-input placeholder="メールアドレス" type="email" autocomplete="email" v-model="account.email"
                      maxlength="191" @blur="checkForm"></el-input>
          </el-form-item>
          <el-form-item prop="editEmail" v-else>
            <el-input placeholder="メールアドレス" type="email" autocomplete="email" v-model="account.editEmail"
                      maxlength="191" @blur="checkForm"></el-input>
          </el-form-item>

          <el-form-item prop="editPassword" v-if="accountId">
            <el-input placeholder="パスワード" type="password" autocomplete="new-password" :readonly="account.disable" v-model="account.editPassword"
                      maxlength="100" @blur="checkForm">
            </el-input>
            <p class="p-tip" v-if="account.disable">※すでに職人ユーザとして登録済みのため、パスワード設定は不要です</p>
          </el-form-item>

          <el-form-item prop="password" v-else>
            <el-input placeholder="パスワード" type="password" autocomplete="new-password" :readonly="account.disable" v-model="account.password"
                      maxlength="100" @blur="checkForm">
            </el-input>
            <p class="p-tip" v-if="account.disable">※すでに職人ユーザとして登録済みのため、パスワード設定は不要です</p>
          </el-form-item>

          <el-form-item prop="editPasswordConfirm" v-if="accountId">
            <el-input placeholder="パスワード再入力" type="password" :readonly="account.disable"
                      v-model="account.editPasswordConfirm" maxlength="100" @blur="checkForm">
            </el-input>
          </el-form-item>

          <el-form-item prop="passwordConfirm" v-else>
            <el-input placeholder="パスワード再入力" type="password" :readonly="account.disable"
                      v-model="account.passwordConfirm" maxlength="100" @blur="checkForm">
            </el-input>
          </el-form-item>

          <el-form-item>
            <el-radio v-model="account.enterprise_admin" label="1">管理アカウント</el-radio>
            <el-radio v-model="account.enterprise_admin" label="0">一般アカウント</el-radio>
          </el-form-item>
        </el-form>
      </div>
      <div class="pro-button">
        <el-button class="modoru" @click="$emit('closeCreate',2)">戻る</el-button>
        <el-button :disabled="needCheck" @click="create" v-if="!accountId">追加する</el-button>
        <el-button :disabled="needCheck" @click="create" v-else>変更する</el-button>
      </div>
    </section>
  </transition>
</template>

<script>
  import MessageDialog from '../../components/common/MsgDialog.vue';
  import validation from '../../validations/account';
  import Messages from "../../mixins/Messages";

  export default {
    name: "AccountCreate",
    components: {
      MessageDialog,
    },
    mixins: [validation, Messages],
    props:{
      accountId:0,
      enterpriseId:0,
    },
    data: function () {
      return {
        account: {name: '',last_name:'',first_name:'', email: '', enterprise_admin: '1',disable: false},
        saved: false,
        passwordConfirm: '',
        showMessage: '',
        messageShowFlg: false,
        needCheck: true
      }
    },
    methods: {
      //情報様式を提示する
      messageShowOver: function () {
        this.messageShowFlg = false;
      },
      //帳票の提出
      create: function () {
        let email = null;
        if (this.accountId){
          email = this.account.editEmail
        } else{
          email = this.account.email
        }
        this.$refs['form'].validate((valid) => {
          if (valid) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            this.account.name = this.account.last_name + this.account.first_name;
            axios.post("/api/setAccount", {
              account: this.account,
              email: email,
              accountId: this.accountId,
              enterpriseId: this.enterpriseId,
            }).then(res => {
              loading.close();
              if (res.data.result) {
                this.$alert(this.commonMessage.error.contract, {showClose: false});
                this.messageShowFlg = true;
              } else {
                if (!this.accountId){
                  this.$alert(this.commonMessage.success.insert, {showClose: false});
                } else{
                  this.$alert(this.commonMessage.success.update, {showClose: false});
                }
                this.saved = true;
                this.$emit('closeCreate', 1);
              }
            }).catch(error => {
              loading.close();
              this.$alert(this.commonMessage.error.accountCreate, {showClose: false});
            });
          } else {
            this.needCheck = true;
          }
        })
      },
      getAccountMsg(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post("/api/getAccountMsg", {
          id: this.accountId,
        }).then(res => {
          if (!res.data.result){
            loading.close();
            this.account = res.data.params[0];
            this.account.disable = false;
            this.needCheck = false;
            this.$set(this.account,'editEmail',res.data.params[0].email);
            this.account.password = '------'
            this.account.passwordConfirm = '------'
          }
        }).catch(error => {
          loading.close();
          this.$alert(this.commonMessage.error.insert, {showClose: false});
        });
      },
      //フォーム検証
      checkForm: function () {
        if (this.account.email && this.account.last_name && this.account.first_name && this.account.passwordConfirm
        && this.account.password){
          this.$refs['form'].validate((valid) => {
            if (valid) {
              this.needCheck = false;
            }else{
              this.needCheck = true;
            }
          });
        }else{
          this.needCheck = true;
        }
      }
    },
    created(){
      if (this.accountId) {
        this.getAccountMsg();
      }
    },
    watch: {
      "account.disable":function (){
        if (this.account.disable === false){
          this.account.name = '';
          this.account.password = '';
          this.account.passwordConfirm = '';
        }
      }
    }
  }
</script>

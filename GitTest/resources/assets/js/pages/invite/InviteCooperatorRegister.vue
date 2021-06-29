<template>
  <!--container-->
  <div class="container clearfix commonAll datapoper invite-cooperator">
    <header>
      <div class="title-wrap">
        <h2>協力会社情報登録</h2>
      </div>
    </header>
    <div class="content clearfix commonAll">
      <section class="common-wrapper Nesting-page">
        <div class="common-view">
          <div class="content">
            <el-form :model="cooperators" :rules="rules" ref="form" label-width="200px">
              <div class="form-group form-name">
                <el-form-item :label="invitesCooperatorRegisterName('last_name')" class="form-last-name"
                              prop="last_name">
                  <el-input v-model="cooperators.last_name" maxlength="59":readonly="readonlyInputLastName" @focus="cancelReadOnly('readonlyInputLastName')" @blur="setReadOnly('readonlyInputLastName')"></el-input>
                </el-form-item>
                <el-form-item :label="invitesCooperatorRegisterName('first_name')" class="form-first-name"
                              prop="first_name">
                  <el-input v-model="cooperators.first_name" maxlength="59" :readonly="readonlyInputFirstName" @focus="cancelReadOnly('readonlyInputFirstName')" @blur="setReadOnly('readonlyInputFirstName')"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('email')" prop="email">
                  <el-input v-model="cooperators.email" maxlength="191" :disabled="true"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('password')" prop="userPassword">
                  <el-input v-model="cooperators.userPassword" maxlength="100" :readonly="readonlyInputPassword" @focus="cancelReadOnly('readonlyInputPassword')" @blur="setReadOnly('readonlyInputPassword')" show-password></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('password_again')" prop="password_confirmation">
                  <el-input v-model="cooperators.password_confirmation" maxlength="100" :readonly="readonlyInputPasswordAgain" @focus="cancelReadOnly('readonlyInputPasswordAgain')" @blur="setReadOnly('readonlyInputPasswordAgain')" show-password></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('cooperator_name')" prop="cooperator_name">
                  <el-input v-model="cooperators.cooperator_name" maxlength="50"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('zip')" prop="zip">
                  <el-input v-model="cooperators.zip" maxlength="7"></el-input>
                  <p class="p-tip">※(半角数字)ハイフン(-)なしで入力してください。</p>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('pref')" prop="pref">
                  <el-input v-model="cooperators.pref" maxlength="20"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('town')" prop="town">
                  <el-input v-model="cooperators.town" maxlength="30"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('street')" prop="street">
                  <el-input v-model="cooperators.street" maxlength="20"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('house')" prop="house">
                  <el-input v-model="cooperators.house" maxlength="70"></el-input>
                </el-form-item>
              </div>
              <div class="form-group">
                <el-form-item :label="invitesCooperatorRegisterName('tel')" prop="tel">
                  <el-input v-model="cooperators.tel" maxlength="15"></el-input>
                  <p class="p-tip">※(半角数字)ハイフン(-)なしで入力してください。</p>
                </el-form-item>
              </div>
            </el-form>
            <div class="pro-button">
              <a class="nextPage" @click="create()">登録</a>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
<script>
  import Messages from "../../mixins/Messages";
  import InviteLists from '../../mixins/InviteLists';
  import validation from '../../validations/invite'

  export default {
    name: "InviteCooperatorRegister",
    mixins: [
      Messages,
      validation,
      InviteLists,

    ],
    components: {},
    data: function () {
      return {
        zipFlag: 0,
        cooperators: {},
        invitationFromUserId: '',
        invitationToken: '',

        readonlyInputFirstName: true,
        readonlyInputPassword: true,
        readonlyInputPasswordAgain: true,
        readonlyInputLastName: true,
      }
    },
    methods: {
      fetch: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/cooperatorFetch', {
          invitationToken: this.invitationToken
        }).then((res) => {
          loading.close();
          if (res.data[0].email) {
            this.invitationFromUserId = res.data[0].created_by;
            this.$set(this.cooperators,'email',res.data[0].email)
          }else{
            window.location.href = window.BASE_URL + 'pub/#/invite/not'
          }
        }).catch(error => {
          loading.close();
          window.location.href = window.BASE_URL + 'pub/#/invite/not'
        });
      },
      create: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        this.$refs['form'].validate((valid) => {
          if (valid) {
            axios.post('/api/cooperatorRegister', {
              invitationFromUserId: this.invitationFromUserId,
              invitationToken: this.invitationToken,
              cooperators: this.cooperators
            }).then((res) => {
              if (res.data === 'inviteOk') {
                window.location.href = window.BASE_URL + '#/invite/ok'
              } else {
                if (res.data === 'inviteNot') {
                  window.location.href = window.BASE_URL + 'pub/#/invite/not'
                }
              }
            }).catch(error => {
              loading.close();
              this.$alert(this.commonMessage.error.system, {showClose: false});
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
          loading.close();
        });
      },
      cancelReadOnly(model) {
        if (model == 'readonlyInputFirstName') {
          this.readonlyInputFirstName = false;
        }
        if (model == 'readonlyInputLastName') {
          this.readonlyInputLastName = false;
        }
        if (model == 'readonlyInputPassword') {
          this.readonlyInputPassword = false;
        }
        if (model == 'readonlyInputPasswordAgain') {
          this.readonlyInputPasswordAgain = false;
        }
      },
      setReadOnly(model) {
        if (model == 'readonlyInputFirstName') {
          this.readonlyInputFirstName = true;
        }
        if (model == 'readonlyInputLastName') {
          this.readonlyInputLastName = true;
        }
        if (model == 'readonlyInputPassword') {
          this.readonlyInputPassword = true;
        }
        if (model == 'readonlyInputPasswordAgain') {
          this.readonlyInputPasswordAgain = true;
        }
      },
    },
    created() {
      this.invitationToken = this.$route.params.token;
      this.fetch();
    },
    mounted() {
      this.zipFlag = this.cooperators.zip;
    },
  }
</script>

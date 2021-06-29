<template>
  <div class="container clearfix project">
    <header>
      <div class="title-wrap">
      </div>
      <UserProfile/>
    </header>
    <section class="project-wrapper">
      <div class="report-deteil-wrap edit clearfix" style="margin:10px 30px 10px 100px;">
        <el-form :rules="pwd" ref="form" :model="user" label-width="200px">
          <el-form-item label="現在のパスワード" prop="oldPassword" maxlength="100">
            <el-input id="oldPassword" type="password" placeholder="現在のパスワード" v-model="user.oldPassword">
            </el-input>
            <span>{{oldPasswordContext}}</span>
          </el-form-item>
          <el-form-item label="新しいパスワード" prop="password">
            <el-input id="password" type="password" placeholder="新しいパスワード" v-model="user.password" maxlength="100">
            </el-input>
          </el-form-item>
          <el-form-item label="新しいパスワード再入力" prop="password_confirmation">
            <el-input id="passwordConfirmation" type="password" placeholder="新しいパスワード"
                      v-model="user.password_confirmation">
            </el-input>
          </el-form-item>
        </el-form>
      </div>
      <div class="clearfix">
        <div class="button-lower" style="margin:10px 30px 10px 250px;">
          <router-link to="/enterprise">戻る</router-link>
        </div>
        <div class="button-lower remark">
          <a href="javascript:void(0)" @click="edit">変更</a>
        </div>
      </div>
    </section>
  </div>
  <!--/container-->
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import validation from '../../validations/user'
  import Messages from "../../mixins/Messages";

  export default {
    components: {
      UserProfile,
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
      edit: function () {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            let errMsg = this.commonMessage.error.update;
            let successMsg=this.commonMessage.success.password;
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post('/api/setEnterprisesList/pwd', {
              user: this.user
            }).then(res => {
              loading.close();
              if (res.data.result === 0) {
                this.deferGo(successMsg);
              } else {
                this.$alert(res.data.errors, {showClose: false});
              }
            }).catch(e => {
              loading.close();
              this.$alert(errMsg, {showClose: false});
            });

          }
        })
      },
      deferGo(successMsg) {
        const TIME_COUNT = 3;
        if (!this.timer) {
          this.count = TIME_COUNT;
          this.$alert(successMsg, {showConfirmButton: false}).catch(() => {
          });
          this.timer = setInterval(() => {
            if (this.count > 0 && this.count <= TIME_COUNT) {
              this.count--;
            } else {
              this.$msgbox.close();
              clearInterval(this.timer);
              this.timer = null;
              this.$router.push({path: "/enterprise"});
            }
          }, 1000)
        }
      },
    },
  }
</script>
<style scoped>

</style>


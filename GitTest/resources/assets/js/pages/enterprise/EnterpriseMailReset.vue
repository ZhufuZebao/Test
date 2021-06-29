<template>
  <div class="container clearfix project">
    <header>
      <div class="title-wrap">
      </div>
      <UserProfile/>
    </header>
    <section class="project-wrapper" v-if="user.enterprise_admin==='1'">
      <div class="report-deteil-wrap edit clearfix">
        <el-form :rules="mail" ref="form" :model="user" label-width="200px">
          <el-form-item label="現在のメール" prop="">
            <el-input type="text" v-model="user.email" disabled></el-input>
          </el-form-item>
          <el-form-item label="新しいメール" prop="newMail">
            <el-input type="text" placeholder="新しいメール" v-model="user.newMail" maxlength="191">
            </el-input>
          </el-form-item>
          <el-form-item label="新しいメール再入力" prop="newMail_confirmation">
            <el-input type="text" placeholder="新しいメール再入力" v-model="user.newMail_confirmation" maxlength="191">
            </el-input>
          </el-form-item>
          <el-form-item label="現在のパスワード" prop="oldPassword">
            <el-input type="password" placeholder="現在のパスワード" v-model="user.oldPassword" maxlength="100">
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-row style="margin-left: 35px">
              <s-identify :identifyCode="identifyCode"></s-identify>

              <el-button @click="refreshCode"><a
                      href="javascript:void(0)">他の画像に切り替える</a>
              </el-button>
            </el-row>
          </el-form-item>
          <el-form-item label="" prop="identifyCode_confirmation">
            <el-input style="width: 200px;" placeholder="文字列を入力" v-model="user.identifyCode_confirmation">
            </el-input>
          </el-form-item>
        </el-form>
      </div>
      <div class="clearfix">
        <div class="button-lower" style="margin:10px 30px 10px 250px;">
          <router-link to="/enterprise">戻る</router-link>
        </div>
        <div class="button-lower remark" @click="verification">
          <a type="text" href="javascript:void(0)" class="send">メール送信</a>
        </div>
      </div>
    </section>
  </div>
  <!--/container-->
</template>

<script>
  import SIdentify from '../../components/common/SIdentify'
  import UserProfile from "../../components/common/UserProfile";
  import validation from '../../validations/user'
  import Messages from "../../mixins/Messages";

  export default {
    name: "EnterpriseMailReset",
    components: {
      UserProfile,
      SIdentify
    },
    mixins: [
      validation,
      Messages,
    ],
    data: function () {
      return {
        btnStyle: {},
        newMail: '',
        verifyNewMail: '',
        newMailPwd: '',
        MailCode: '',
        isMounted: false,
        identifyCodes: '23456789ABCDEFGHJKMNPQRSTUVWXYZ', // キャプチャ文字集
        identifyCode: '',
        user: {},
      }
    },
    mounted() {
      this.fetch();
      this.identifyCode = '';
      this.makeCode(this.identifyCodes, 6);
      this.isMounted = true;
    },
    methods: {
      fetch: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.update;
        axios.get('/api/getEnterprisesList').then((res) => {
          this.user = res.data.user[0];
          loading.close();
        }).catch(e => {
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
      verification: function () {
        this.user.identifyCode = this.identifyCode;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            let errMsg = this.commonMessage.error.update;
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post('/api/setEnterprisesList/mail', {
              user: this.user
            }).then(res => {
              loading.close();
              if (res.data.result === 0) {
                this.$router.push({path: '/enterprise/afterMail'});
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
      // 確認コードを更新
      refreshCode() {
        this.identifyCode = ''
        this.makeCode(this.identifyCodes, 6)
      },
      makeCode(o, l) {
        for (let i = 0; i < l; i++) {
          this.identifyCode += this.identifyCodes[this.randomNum(0, this.identifyCodes.length)]
        }
      }, randomNum(min, max) {
        return Math.floor(Math.random() * (max - min) + min)
      },
    },
  }
</script>
<style scoped>
  .send {
    padding: 0 20px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border: 1px solid #9dc815;
    border-radius: 2px;
    display: block;
    background-color: #9dc815;
    font-size: 14px;
    color: #ffffff;
  }

</style>


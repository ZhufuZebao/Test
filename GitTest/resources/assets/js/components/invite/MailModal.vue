<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show schedule-modal">
      <div class="modalBody  mail-modal" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="close">×</div>
        <div class="modalBodycontent">
          <h3>協力会社を追加する</h3>
          <div class="schedule-modalBodycontent clearfix">
            <el-form :model="invites" :rules="rules" ref="form">
              <el-form-item class="email-form">
                <el-tag v-for="(email,index) in emails" :key="index" closable @close="delCheckItem(index)"
                        :type="messageType">
                  <span>{{email}}</span>
                </el-tag>
              </el-form-item>
              <el-form-item prop="email" class="email-input">
                <el-input placeholder="メールアドレス" @blur="add" v-model="invites.email" maxlength="191"
                          onkeypress="if (event.keyCode === 13) return false;"></el-input>
              </el-form-item>
            </el-form>
            <dl class="clearfix mail-textarea">
              <el-input type="textarea" :rows="4" placeholder="メッセージ" v-model="invites.message">
              </el-input>
            </dl>
            <div class="clearfix"></div>
            <div class="button-wrap clearfix">
              <div class="button-lower remark">
                <el-button @click="sendEmail" :disabled="emails.length === 0">招待する</el-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
    <!--/modal-->
  </transition>
</template>

<script>
  import Messages from "../../mixins/Messages";
  import validation from '../../validations/invite'

  export default {
    name: "MailModal",
    mixins: [Messages,validation],
    data: function () {
      return {
        isMounted: false,
        invites: {
          email:'',
          message:'',
        },         //協力会社データ
        emails:[],
        messageType : 'info',
      }
    },
    methods:{
      close() {
        this.$emit('closeMailModal');
      },
      //メールsend
      sendEmail() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.inviteCreate;
        let AmountErrMessage = this.commonMessage.error.contract;
        axios.post('/api/sendEmail', {
          toEmail: this.emails,
          message: this.invites.message,
        }).then((res) => {
          if (!res.data.result){
            this.close();
            this.$emit("sendPeopleCount",this.emails.length);
            this.$emit('fetchInvites');
            loading.close();
          }else{
            loading.close();
            this.$alert(res.data.errors, {showClose: false});
          }
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },
      //メール追加
      add() {
        this.$refs['form'].validate((valid) => {
          if (valid && this.invites.email !== '') {
            let errMessage = this.commonMessage.error.system;
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)',target:document.querySelector('.modalBodycontent')});
            axios.post('/api/checkEmail', {
              checkEmail: this.invites.email,
            }).then((res) => {
              if (res.data === 'ok') {
                if (this.emails.indexOf(this.invites.email) === -1){
                  this.emails.push(this.invites.email);
                  this.invites.email = ''
                } else {
                  this.invites.email = ''
                }
              }
              if (res.data === 'sameEnterprise') {
                this.$alert('このメールアドレスは社内ユーザとして登録されています', {showClose: false});
                this.invites.email = '';
              }
              if (res.data === 'already') {
                this.$alert('このメールアドレスは登録済み', {showClose: false});
                this.invites.email = '';
              }
              loading.close();
            }).catch(error => {
              this.$alert(errMessage, {showClose: false});
              loading.close();
            });
          }
        })
      },
      //tagを削除する
      delCheckItem(index) {
        this.emails.splice(index, 1);
      },
    },
    computed: {
      modalLeft: function () {
        if (!this.isMounted)
          return;
        return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
      },
      modalTop: function () {
        return '0px';
      }
    },
    mounted() {
      this.isMounted = true;
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>

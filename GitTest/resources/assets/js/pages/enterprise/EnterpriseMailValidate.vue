<template>
  <div class="enterprise-all">
    <div class="image">
      <div class="logo"></div>
    </div>
    <div class="msg">
      <p>下記のメールアドレスに 送信しました。</p><br>
      <p>メールに記載されている認証キーを入力か、コピーして貼り付けてください</p><br>
      <p>※メールが届かない場合は、迷惑メールフォルダにご確認してください</p>
    </div>
    <table class="registerTable">
      <tr>
        <td colspan="2">認証キー入力</td>
      </tr>
      <tr>
        <td>メールアドレス</td>
        <td><input type="text" v-model="user.email" disabled></td>
      </tr>
      <tr>
        <td>※認証キー</td>
        <td>
          <input type="text" v-model="user.emailKey">
        </td>
      </tr>
    </table>
    <div class="but">
      <button class="reSend" :class="{disabled: !this.canClick}" @click="countDown">{{content}}
      </button>
      <button class="sure" @click="enterpriseLogin()">次へ</button>
    </div>
  </div>
</template>

<script>
  export default {
    name: "EnterpriseMailValidate",
    data() {
      return {
        content: '再送信（60秒）',
        totalTime: 60,
        canClick: true,
        canSend: true,
        enterprise: {},
        user: {email: ''},
        userEmailKey: '',
        rSendTime: '',
        timeBack: true,
        invitationFromUserId:'',
        invitationToken:'',
      }
    }, methods: {
      //送信ボタンをクリック
      countDown() {
        if (!this.canClick) return;
        this.canClick = false;
        this.content = this.totalTime + '秒後再送信';
        if (this.canSend) {
          this.getAuthKey();
        }
        if (this.timeBack) {
          let clock = window.setInterval(() => {
            this.totalTime--;
            this.content = this.totalTime + '秒後再送信';
            sessionStorage.setItem('rSendTime', JSON.stringify(this.$data.totalTime));
            if (this.totalTime < 0) {
              window.clearInterval(clock);
              this.content = '再送信（60秒）';
              this.totalTime = 60;
              this.canClick = true;
            }
          }, 1000)
        }
      },
      //メールの認証キーを取得
      getAuthKey: function () {
        axios.post('/api/enterpriseGetAuthKey', {
          'email': this.user.email
        }).then((res) => {
        })
      },
      //メールの認証キーと入力の認証キー比較
      enterpriseLogin: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/enterpriseLogin', {
          invitationFromUserId: this.invitationFromUserId,
          invitationToken: this.invitationToken,
          'enterprise': this.enterprise,
          'emailKey': this.user.emailKey,
          'user': this.user
        }).then((res) => {
          if (res.data === 'inviteOk') {
            this.$router.push({path: '/invite/ok'});
          } else {
            if (res.data === 'inviteNo') {
              this.$router.push({path: '/invite/no'});
            } else {
              if (res.data.result === 1) {
                for (let i = 0; i < res.data.errors.length; i++) {
                  this.$alert(res.data.errors[i], {showClose: false});
                }
              } else {
                this.$router.push({
                  path: '/enterprise/registerOk'
                });
              }
            }
          }
        }).catch(error => {
          loading.close();
        });
        loading.close();
      }
    },
    created() {
      this.totalTime= 60;
      //sessionにデータを取得
      let data = JSON.parse(sessionStorage.getItem('data'));
      if (data != null) {
        this.enterprise = data;
        this.user.name = data.userLastName + data.userFirstName;
        this.user.first_name = data.userFirstName;
        this.user.last_name = data.userLastName;
        this.user.password = data.userPassword;
        this.user.email = data.userEmail;
      }
      //sessionに「事業者情報確認」画面の仮登録バタンをクリックのflagを取得
      let emailSendFlag = sessionStorage.getItem('emailSendFlag');
      this.rSendTime = JSON.parse(sessionStorage.getItem('rSendTime'));
      //「事業者情報確認」画面の仮登録バタンをクリック
      if (emailSendFlag) {
        sessionStorage.removeItem('emailSendFlag');
        //再送信バタン60s中
        if (this.rSendTime > 0) {
          this.timeBack = true;
          this.canSend = false;
          this.totalTime = this.rSendTime;
          this.countDown();
        } else {
          //再送信バタン60s完了
          this.countDown();
        }
      } else {
        //この画面刷新 再送信バタン60s中
        if (this.rSendTime > 0) {
          this.timeBack = true;
          this.canSend = false;
          this.totalTime = this.rSendTime;
          this.countDown();
        }
        //この画面刷新 再送信バタン60s完了
        else {
          this.timeBack = true;
          this.canSend = true;
        }
      }
      try {
        if (sessionStorage.from_user_id && sessionStorage.token) {
          this.invitationFromUserId = sessionStorage.from_user_id;
          this.invitationToken = sessionStorage.token;
        }
      } catch (e) {
      }
    }
  }
</script>

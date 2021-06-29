<template>
  <transition name="fade">
    <div class="modal wd1 modal-show commonAll project-customer project-enterprise">
      <div class="modalBodyCustomer" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="$emit('closeModal')">×</div>
        <div class="modalBodycontent commonMol">
          <div class="common-view">
            <div v-if="showEnterpriseModel" class="content">
              <div class="modal_header">
                <img v-if="user.file" :src="user.file"/>
                <img v-else src="images/icon-chatperson.png"/>
                <span v-if="user.enterprise_admin === '1'">{{ user.name }}</span>
                <span v-else>{{ user.last_name }} {{ user.first_name }}</span>
              </div>
              <table class="detail-table">
                <a @click.prevent="DetailEdit" class="edit"><img src="images/edit@2x.png"/></a>
                <tr v-if="user.enterprise_admin === '0'">
                  <td class="tdLeft">アカウント名</td>
                  <td class="tdRight">{{ user.name }}</td>
                </tr>
                <tr>
                  <td class="tdLeft">メールアドレス</td>
                  <td class="tdRight">{{ user.email }}</td>
                </tr>
                <tr>
                  <td class="tdLeft">パスワード</td>
                  <td class="tdRight">********
                    <a href="javascript:void(0)" @click.prevent="pwdEdit">パスワードを変更する</a>
                  </td>
                </tr>
                <tr v-if="user.enterprise_admin === '1'">
                  <td class="tdLeft">会社名</td>
                  <td class="tdRight">{{ user.enterprise.name }}</td>
                </tr>
                <tr v-if="user.enterprise_admin === '1'">
                  <td class="tdLeft">住所</td>
                  <td class="tdRight">〒{{ user.enterprise.zip }} {{ user.enterprise.pref }}{{ user.enterprise.town }}{{
                    user.enterprise.street }}{{ user.enterprise.house }}
                  </td>
                </tr>
                <tr v-if="user.enterprise_admin === '1'">
                  <td class="tdLeft">電話番号</td>
                  <td class="tdRight">{{ user.enterprise.tel }}</td>
                </tr>
              </table>
            </div>
            <EnterprisePwdReset v-if="showEnterprisePwdResetModel" @closePwdResetModel="closePwdResetModel"/>
            <EnterpriseDetail v-if="showEnterpriseDetailModel" @closeEdit="closeEdit" :user="user"/>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>


<script>
  import Messages from "../../mixins/Messages";
  import EnterprisePwdReset from "./EnterprisePwdReset";
  import EnterpriseDetail from "./EnterpriseDetail";

  export default {
    name: "EnterpriseList",
    components: {
      EnterprisePwdReset,
      EnterpriseDetail,
    },
    mixins: [
      Messages
    ],
    data: function () {
      return {
        user: {
          enterprise: {}
        },
        isMounted: false,
        show: false,
        position: {},
        showEnterpriseModel: true,
        showEnterprisePwdResetModel: false,
        showEnterpriseDetailModel: false,
        sessionUser: {file: '', enterprise_admin: '', name: ''}
      }
    },
    methods: {
      //ユーザー情報表示インタフェースをオフにする
      closeDetailModel() {
        this.showEnterpriseModel = true;
        this.showEnterpriseDetailModel = false;
      },
      //ユーザーのパスワードをオフにしてインタフェースを修正する
      closePwdResetModel() {
        this.showEnterpriseModel = true;
        this.showEnterprisePwdResetModel = false;
      },
      //ユーザーパスワード変更インターフェースを表示します
      pwdEdit() {
        this.showEnterpriseModel = false;
        this.showEnterprisePwdResetModel = true;
      },
      //ユーザ情報編集インターフェースを表示する
      DetailEdit() {
        this.showEnterpriseModel = false;
        this.showEnterpriseDetailModel = true;
      },
      //ユーザ情報編集インターフェースを表示する
      closeEdit: function (needFresh) {
        if (needFresh > 0) {
          this.fetch();//現在の表示値を更新する
          this.showEnterpriseDetailModel = false;
          this.showEnterpriseModel = true;
        } else {
          this.showEnterpriseDetailModel = false;
          this.showEnterpriseModel = true;
        }
      },
      //ユーザー情報をアップロードする(Enterpriseおよび担当者を含む)
      fetch: function () {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.profileDetail;
        axios.get('/api/getEnterprisesList').then((res) => {
          this.user = res.data.user[0];
          if (!this.user.enterprise) {
            this.user.enterprise = this.user.enterprise_coop;
          }
          this.sessionUser.noticeCount = res.data.noticeCount;
          this.sessionUser.id = this.user.id;
          this.sessionUser.name = this.user.name;
          this.sessionUser.enterprise_admin = this.user.enterprise_admin;
          this.sessionUser.enterprise_id = this.user.enterprise_id;
          if (this.user.file) {
            this.user.file  ='file/users/'+ this.user.file + "?" +Date.now();
            this.user.localFile  = this.user.file;
          }
          this.sessionUser.file = this.user.file;
          this.sessionUser.localFile = this.user.localFile;
          this.sessionUser.enterpriseName = '';
          if (this.user.enterprise_id){
            this.sessionUser.enterpriseName = this.user.enterprise.name
          } else{
            this.sessionUser.enterpriseName = this.user.enterprise_coop.name
          }
          sessionStorage.setItem('_user_info', JSON.stringify(this.sessionUser));
          this.$emit('fetch');
          loading.close();
        }).catch(e => {
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
    },
    created() {
      this.fetch();
    },
    mounted() {
      this.isMounted = true;
    },
    //窓際の位置を計算する
    computed: {
      modalLeft: function () {
        if (this.isMounted) {
          return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
        } else {
          return;
        }
      },
      modalTop: function () {
        return '0px';
      },
    },
  }
</script>


<template>
  <transition name="fade">
    <div class="modal wd1 modal-show">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="$emit.self('closeModal')">×</div>
        <div class="modalBodycontent commonMol">
          <section class="project-wrapper">
            <div class="report-deteil-wrap clearfix">
              <dl class="clearfix">
                <dd>
                  <div v-if="user.file">
                    <img v-bind:src="user.file">
                  </div>
                  <div v-else>
                    <img src="images/no-image.png">
                  </div>
                  <span>{{user.name}}</span>
                </dd>
                <dd>メールアドレス: {{user.email}}
                  <!--&lt;!&ndash;<router-link to="/enterprise/mail">編集</router-link>&ndash;&gt;-->
                </dd>
                <dd>パスワード: &nbsp;**********
                  <a href="javascript:void(0)" @click.prevent="pwdEdit" style="float: right">パスワードを変更する</a>
                  <!--<router-link to="/enterprise/pwd">管理者パスワード</router-link>-->
                </dd>
                <dd>会社名: {{user.enterprise.name}}</dd>
                <dd>住所:〒
                  {{user.enterprise.pref}}{{user.enterprise.town}}{{user.enterprise.street}}{{user.enterprise.house}}
                </dd>
                <dd>電話番号:{{user.enterprise.tel}}</dd>
                <dd>担当者:{{user.enterprise.user.name}}</dd>
              </dl>
              <dl class="clearfix" v-if="user.enterprise_admin==='0'">
                <dd>
                  <div v-if="user.file">
                    <img v-bind:src="user.file">
                  </div>
                  <div v-else>
                    <img src="images/no-image.png">
                  </div>
                  <span>{{user.name}}</span>
                </dd>
                <dd>メールアドレス: {{user.email}}
                  <!--&lt;!&ndash;<router-link to="/enterprise/mail">編集</router-link>&ndash;&gt;-->
                </dd>
                <dd>パスワード: &nbsp;**********
                  <a href="javascript:void(0)" @click.prevent="pwdEdit" style="float: right">パスワードを変更する</a>
                  <!--<router-link to="/enterprise/pwd">管理者パスワード</router-link>-->
                </dd>
              </dl>
            </div>
            <div class="button-lower remark mig">
              <router-link to="/enterprise/detail">プロフィール 編集</router-link>
            </div>
          </section>
        </div>
      </div>
      <div class="modalBK"></div>
    </div>
  </transition>
</template>


<script>
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";

  export default {
    name: "EnterpriseList",
    components: {
      UserProfile,
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
      }
    },
    mounted() {
      this.isMounted = true;
    },
    methods: {
      pwdEdit(){

      },
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
    },
    created() {
      this.fetch();
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
    }
  }
</script>
<style scoped>


  .rlo {
    float: right;
  }

  .mig {
    margin: 0 250px;
    width: 300px;
  }

  img {
    height: 12.5px;
  }

  .modal-show {
    display: block;
  }
</style>


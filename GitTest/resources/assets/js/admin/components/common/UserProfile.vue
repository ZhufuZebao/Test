<template>
    <span>
    <div class="user-profile">
      <div class="profile-photo">
      <div v-if="user.file"><img v-bind:src="user.file"></div>
        <div v-else><img src="images/icon-chatperson.png" alt=""></div>
        </div>
      <div class="profile-name">{{user.name}}</div>
      <el-button class="logout" @click="removeUserInfo">ログアウト</el-button>
    </div>
    <transition name="box">
    <ul class="user-profile-setting show" v-if="showSetting" id="div">
      <li v-if="user.enterprise_admin==='0' || user.enterprise_admin==='1'" @click="showEnterprise">
        <a href="javascript:void(0)">プロフィール編集</a>
      </li>
      <!--<li v-if="user.enterprise_admin==='1' && user.enterprise_id" @click="showAccount">-->
        <!--<a href="javascript:void(0)">アカウント管理</a>-->
      <!--</li>-->
      <li><a href="javascript:void(0)" @click="removeUserInfo">ログアウト</a></li>
    </ul>
    </transition>
    <enterpriseList @closeModal="closeModal" @fetch="fetch" v-if="showEnterpriseModal"/>

  </span>
</template>

<script>
  import EnterpriseList from "../enterprise/Enterprise";

  export default {
    name: 'UserProfile',
    components: {
      EnterpriseList,
    },
    props:{
      isInvite : Boolean,
      isSchedule:Boolean,
    },
    data() {
      return {
        showEnterpriseModal: false,
        showAccountModal: false,
        showSetting: false,
        user: {file: '', enterprise_admin: '', name: ''}
      }
    },
    methods: {
      //ユーザー情報インタフェースを展開する
      showEnterprise() {
        this.showEnterpriseModal = true;
        $('.calendar-day-sckedule-wrap').css({"z-index": 0});
        $('.schedule-week-index').css({"z-index": 0});
      },
      //ユーザー情報インタフェースがクローズされている
      closeModal() {
        this.showEnterpriseModal = false;
        $('.calendar-day-sckedule-wrap').css({"z-index": 1});
        $('.schedule-week-index').css({"z-index": 1});
      },
      //Account一覧展开
      showAccount() {
        this.showAccountModal = true;
        $('.calendar-day-sckedule-wrap').css({"z-index": 0});
        $('.schedule-week-index').css({"z-index": 0});
      },
      //Account一覧は閉じています
      closeAccount(status) {
        this.showAccountModal = false;
        $('.calendar-day-sckedule-wrap').css({"z-index": 1});
        $('.schedule-week-index').css({"z-index": 1});
        if(status){
          if (this.isInvite) {
            this.$emit("fetchInvites");
          }else if (this.isSchedule){
            this.$emit("fetch");
          }
        }
      },
      //切り替えプレゼン
      toggleClass: function (e) {
        this.showSetting = !this.showSetting;
        if (this.showSetting) {
          document.onclick = function () {
            if (document.getElementById("div")) {
              document.getElementById("div").style.display = "none";
            }
          };
          e.stopPropagation();
        }
      },
      //ユーザー登録を抹消する
      removeUserInfo: function () {
        sessionStorage.removeItem('_user_info');
        window.location.href = 'logout';
      },
      //ユーザ情報ローディング
      fetch: function () {
        try {
          let _user_info = sessionStorage.getItem('_user_info');
          if (_user_info) {
            this.user = JSON.parse(_user_info);
          } else {
            this.user = {};
          }
        } catch (e) {
        }
        if (!this.user || !this.user.name) {
          axios.get('/api/getEnterprisesList').then((res) => {
            let data = res.data.user[0];
            this.user = {};
            this.user.name = data.name;
            if (data.file) {
              this.user.file  ='file/users/'+ data.file;
            }
            this.user.id = data.id;
            this.user.last_name = data.last_name;
            this.user.first_name = data.first_name;
            this.user.email = data.email;
            this.user.enterprise_admin = data.enterprise_admin;
            this.user.enterprise_id = data.enterprise_id;
            sessionStorage.setItem('_user_info', JSON.stringify(this.user));
          })
        }
      }
    },
    created() {
      this.fetch();
    }
  }
</script>
<style>
  .show {
    display: block;
  }

  .user-profile-setting {
    overflow: hidden;
  }

  .user-profile-setting li {
    height: 30px;
  }

  .box-enter-active, .box-leave-active {
    transition: all 0.5s;
  }

  .box-enter, .box-leave-to {
    height: 0;
  }
</style>

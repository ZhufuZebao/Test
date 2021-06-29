<template>
  <span>
    <div class="user-profile" @click="toggleClass($event)">
      <div class="profile-photo">
      <div @click.stop="getNotice()" class="profile-photo-notice">
        <img src="images/icon-new-notice.png" alt="">
        <span v-if="user.noticeCount">{{user.noticeCount}}</span>
      </div>
      <div v-if="user.file"><img v-bind:src="user.file"></div>
        <div v-else><img src="images/icon-chatperson.png" alt=""></div>
        </div>
      <div class="profile-name">{{user.name}}</div>
    </div>
    <transition name="box">
    <ul class="user-profile-setting show" v-if="showSetting" id="div">
      <li v-if="user.enterprise_admin==='0' || user.enterprise_admin==='1'" @click="showEnterprise">
        <a href="javascript:void(0)" @click="browse('profile')">プロフィール編集</a>
      </li>
      <li v-if="user.enterprise_admin==='1' && user.enterprise_id" @click="showAccount">
        <a href="javascript:void(0)" @click="browse('account')">アカウント管理</a>
      </li>
      <li v-if="user.enterprise_admin==='1' && user.enterprise_id">
        <a href="javascript:void(0)" @click="showContain()">データ容量状況</a>
      </li>
      <li><a href="javascript:void(0)" @click="removeUserInfo">ログアウト</a></li>
    </ul>
    </transition>
    <enterpriseList @closeModal="closeModal" @fetch="fetch" v-if="showEnterpriseModal"/>
    <accountList :isInvite="isInvite" @closeAccount="closeAccount" v-if="showAccountModal"/>
    <NoticeList v-if="showNoticeList" @closeNotice="closeNotice" @getNoticeCount="getNoticeCount"/>
     <ContainList v-if="showContainList" @closeContainliste="closecontainlist"></ContainList>
  </span>
</template>

<script>

  import EnterpriseList from "../enterprise/Enterprise";
  import NoticeList from "../notice/NoticeList";
  import Common from "../../mixins/Common.js";
  import AccountList from "../account/AccountList";
  import Messages from "../../mixins/Messages";
  import ContainList from "../../components/account/ContainList"

  export default {
    name: 'UserProfile',
    components: {
      EnterpriseList,
      AccountList,
      NoticeList,
      ContainList,
    },
    mixins: [
      Common,Messages
    ],
    props:{
      isInvite : Boolean,
      isContact : Boolean,
      isSchedule:Boolean,
    },
    data() {
      return {
        showEnterpriseModal: false,
        showAccountModal: false,
        showSetting: false,
        user: {file: '', enterprise_admin: '', name: ''},
        showNoticeList:false,
        showContainList:false,
      }
    },
    methods: {
      getNoticeCount(){
        let errMessage = this.commonMessage.error.system;
        axios.get('/api/getNoticeCount').then((res) => {
          this.user.noticeCount = res.data;
          let user = {};
          let _user_info = sessionStorage.getItem('_user_info');
          if (_user_info) {
            user = JSON.parse(_user_info);
          }
          user.noticeCount =  res.data;
          sessionStorage.setItem('_user_info', JSON.stringify(user));
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
        });
      },
      closeNotice(){
        this.showNoticeList = false;
        $('.calendar-day-sckedule-wrap').css({"z-index": 1});
        $('.schedule-week-index').css({"z-index": 1});
      },
      getNotice() {
        this.showNoticeList = true;
        $('.calendar-day-sckedule-wrap').css({"z-index": 0});
        $('.schedule-week-index').css({"z-index": 0});
      },
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
          }else if (this.isContact){
            this.$emit("fetchContacts");
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
          axios.post('/api/deleteFirebaseID').then((res) => {
              sessionStorage.removeItem('_user_info');
              window.location.href = 'logout';
          });
      },
      //ユーザ情報ローディング
      fetch: function () {
        try {
          let _user_info = sessionStorage.getItem('_user_info');
          if (_user_info) {
            this.user = JSON.parse(_user_info);
            this.getNoticeCount();
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
            this.user.noticeCount = res.data.noticeCount;
            if (data.file) {
              this.user.file  ='file/users/'+ data.file;
              this.user.localFile = data.file;
            }
            this.user.id = data.id;
            this.user.enterprise_admin = data.enterprise_admin;
            this.user.enterprise_id = data.enterprise_id;
            this.user.enterpriseName = '';
            if (data.enterprise_id){
              this.user.enterpriseName = data.enterprise.name
            } else{
              this.user.enterpriseName = data.enterprise_coop.name
            }
            sessionStorage.setItem('_user_info', JSON.stringify(this.user));
          })
        }
      },
        browse:function(name){
            let id=name;
            axios.post("/api/setBrowse", {id:id
            }).then(res => {
            });
        },
      showContain(){
        this.showContainList = true;
      },
      closecontainlist(){
        this.showContainList = false;
      }
    },
    created() {
      this.fetch();
      Common.initSocket();
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

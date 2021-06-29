<template>
  <div class="container clearfix friend customerlist commonAll">
    <header>
      <h1>
        <router-link to="/contact">
          <div class="commonLogo">
            <ul>
              <li class="bold">CONTACT</li>
              <li>連絡先</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap">
        <h2>協力会社一覧</h2>
        <a class="title-add" v-if="!isParticipantFlag">
          <img src="images/add@2x.png" @click.prevent="openMailModal"/>
        </a>


        <ul class="header-nav schedule" style="border: 0">
          <li class="header-nav-schedule-li" style="width: auto !important;">
            <span class="non-current-li"><router-link to="/contact">社内</router-link></span>
            <span class="current-li"><router-link to="/contact/invite">協力会社</router-link></span>
            <span class="non-current-li"><router-link to="/contact/friend">職人</router-link></span>
          </li>
        </ul>
        <div class="flo">
          <el-input placeholder="名前/組織名で検索"
                    @change="search" v-model="searchName"  suffix-icon="searchForm-submit" >
          </el-input>
        </div>
      </div>
      <UserProfile :isInvite="isInvite" @fetchInvites="fetchInvites"/>
    </header>
    <section class="customer-wrapper invite-form delet-img">
      <div class="invite-inf">
        <button @click="changePower('powerEnterprise')" :class="{'power' : powerEnterpriseShow}" v-if="powerEnterprise" >招待した</button>
        <button @click="changePower('powerInvite')" :class="{'power' : powerInviteShow}" v-if="powerInvite">招待された</button>
      </div>
      <div>
        <a class="title-del"  v-if="delInviteItem.length !== 0" @click.prevent="delInviteItems">
          <img  class="img-delete" src="images/icon-dust2.png" alt="" >
        </a>
      </div>
      <div class="customertable listaddimg">
        <div class="list-del-common">
          <ul class="list-del-header report-serch clearfix">
            <li class="customer-li">

              {{invitesName('user_name')}}
              <span @click="sort('name')"><i class="el-icon-d-caret"></i></span>
            </li>
            <li class="customer-li">
              &nbsp;
            </li>
            <li class="customer-li">
              {{invitesName('enterprise_name')}}
              <span @click="sort('enterprise')"><i class="el-icon-d-caret"></i></span>
            </li>
            <li class="customer-li">
              {{invitesName('email')}}
            </li>
            <li class="customer-li">
              {{invitesName('created_at')}}
              <span @click="sort('date')"><i class="el-icon-d-caret"></i></span>
            </li>
          </ul>
        </div>

        <ul class="table-scroll tablelisthover">
          <el-checkbox-group v-model="delInviteItem">
            <li :id="'s'+invite.id" :key="invite.id" class="clearfix btns"  date-tgt="wd1" v-for="invite in invites" v-if=" !invite.deleted_at">
              <span class="customer-li" v-if="invite.account && !invite.isParticipant">
                <el-checkbox :label="invite.id"><div v-if="invite.account">
                   <img :src="'file/users/'+invite.account.file" alt="" v-if="invite.account && invite.account.file" class="pro-photo">
                    <img src="images/icon-chatperson.png" alt="" v-else class="pro-photo">
                 <span class="btn-span">{{ invite.account.name }}</span> </div></el-checkbox>
              </span>
              <span class="customer-li" v-if="!invite.account &&!invite.isParticipant"><el-checkbox :label="invite.id">&nbsp;</el-checkbox>  </span>
              <span class="customer-li" v-if="invite.isParticipant && invite.account">
                <div  v-if="invite.account" style="padding-left: 41px"><img :src="'file/users/'+invite.account.file" alt="" v-if="invite.account && invite.account.file" class="invite-photo">
                <img src="images/icon-chatperson.png" alt="" v-else class="invite-photo">
                <sapn class="btn-span">{{ invite.account.name }}</sapn></div></span>
              <span class="customer-li" v-if="invite.agree === 1"><a href="javascript:void(0)" @click="chatLink(invite,invite.user_id)">
                <img class="icon-blue-chat" src="images/icon-blue-chat.png" alt=""></a></span>
              <span class="customer-li" v-else><a href="javascript:void(0)"></a></span>
              <span class="customer-li" v-if="invite.account && invite.account.enterprise">{{ invite.account.enterprise.name }}</span>
              <span class="customer-li" v-else-if="invite.account && invite.account.coop_enterprise">{{ invite.account.coop_enterprise.name }}</span>
              <span class="customer-li" v-else></span>
              <span class="customer-li"><a :href="'mailto:'+ invite.email">{{ invite.email }}</a></span>
              <span class="customer-li" v-if="invite.agree === 1">{{ createdDateFormat(invite.created_at) }}</span>
              <span class="customer-li" v-else-if="invite.agree === 2"><span class="not-confirmed">拒否しました</span></span>
              <span class="customer-li"  v-else><span class="to-confirmed">承認待ち</span></span>
            </li>
            <li :id="'s'+invite.id" :key="invite.id" class="clearfix btns"  date-tgt="wd1" v-else>
              <span class="customer-li"><el-checkbox :label="invite.id"><span class="btn-span" style="display: inherit;">このユーザは削除されました</span></el-checkbox></span>
              <span class="customer-li"></span>
              <span class="customer-li" v-if="invite.account && invite.account.enterprise">{{ invite.account.enterprise.name }}</span>
              <span class="customer-li" v-else-if="invite.account && invite.account.coop_enterprise">{{ invite.account.coop_enterprise.name }}</span>
              <span class="customer-li" v-else></span>
              <span class="customer-li"></span>
              <span class="customer-li"></span>

            </li>
          </el-checkbox-group>
        </ul>
      </div>
      <div class="pagination-center" v-if="peopleCount !== ''">
        {{peopleCount}}名を招待いたしました
      </div>
    </section>
    <MailModal @closeMailModal="closeMailModal" @fetchInvites="fetchInvites" @sendPeopleCount="getPeopleCount" v-if="showMailModal"></MailModal>
  </div>
</template>

<script>
  import Pagination from "../../components/common/Pagination";
  import UserProfile from "../../components/common/UserProfile";
  import InviteLists from '../../mixins/InviteLists';
  import Calendar from '../../mixins/Calendar';
  import Messages from "../../mixins/Messages";
  import MailModal from '../../components/invite/MailModal';

  export default {
    components: {
      Pagination,
      UserProfile,
      MailModal
    },
    mixins: [Calendar,InviteLists,Messages],
    data() {
      return {
        invites: {},         //協力会社データ
        searchName:'',      //詳細検索対象
        sortCol: "",          //ソートカラム
        order: "asc",            //昇順、降順
        showMailModal: false,
        peopleCount:'',
        delInviteItem: [],
        isParticipantFlag: false,
        isInvite: true,
        powerEnterprise:false,
        powerInvite:false,
        powerEnterpriseShow:false,
        powerInviteShow:false,
      }
    },
    methods: {
      fetchInvites: function () {
        this.isParticipantFlag=false;
        let errMessage = this.commonMessage.error.inviteList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getInviteList', {
          enterprise: this.powerEnterpriseShow,
          invite: this.powerInviteShow,
        }).then((res) => {
          this.invites = res.data.data;
          this.powerEnterprise=res.data.power.enterprise;
          this.powerInvite=res.data.power.invite;
          this.powerEnterpriseShow=res.data.power.enterpriseShow;
          this.powerInviteShow=res.data.power.inviteShow;
          for (let i = 0; i < res.data.data.length; i++) {
            try {
              if (res.data.data[i].isParticipant) {
                this.isParticipantFlag = res.data.data[i].isParticipant;
              }
            } catch (e) {
              this.$alert(errMessage, {showClose: false});
              loading.close();
            }
          }
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      changePower: function(obj){
        if(obj == 'powerEnterprise'){
          this.powerEnterpriseShow=true;
          this.powerInviteShow=false;
        }else{
          this.powerEnterpriseShow=false;
          this.powerInviteShow=true;
        }
        this.fetchInvites();
      },
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj === ""
      },
      //inviteを検索する
      search() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.inviteList;
        axios.post('/api/getInviteSearch', {
          searchName: this.searchName,
        }).then((res) => {
          this.invites = res.data;
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      //チャットリンク
      chatLink(invite){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.system;
        axios.post('/api/inviteChatLink', {
          invite : invite,
        }).then((res) => {
          let groupId = res.data.group_id;
          let userId = res.data.user_id;
          this.$router.push({path: "/chat", query: {groupId: groupId,userId:userId}});
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
        loading.close();
      },
      //並べ替え
      sort(sortCol) {
        if (this.invites.length !== 0) {
          if (this.sortCol === sortCol) {
            this.changeOrder();
          } else {
            this.sortCol = sortCol;
            this.order = 'asc'
          }
          let errMessage = this.commonMessage.error.inviteList;
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.post('/api/getInviteSearchSort', {
            searchName: this.invites,
            sort: this.sortCol,
            order: this.order,
          }).then((res) => {
            this.invites = res.data;
            loading.close();
          }).catch(error => {
            this.$alert(errMessage, {showClose: false});
            loading.close();
          });
        }
      },

      //追加日 format
      createdDateFormat(contactData){
        if (contactData){
          return Calendar.dateFormat(contactData);
        } else {
          return '';
        }
      },

      changeOrder() {
        if (this.order === 'asc') {
          this.order = 'desc';
        } else {
          this.order = 'asc';
        }
      },
      openMailModal(){
        this.showMailModal = true;
      },
      closeMailModal(){
        this.showMailModal = false;
      },
      //招待人数
      getPeopleCount(data){
        this.peopleCount = data;
        setTimeout(() => {
          this.peopleCount = '';
        }, 3000);
      },
      //inviteを削除する
      delInviteItems(){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.inviteDelete;
        this.$confirm(this.commonMessage.confirm.delete.account).then(() => {
          axios.post("/api/delInvite", {
            id: this.delInviteItem,
          }).then(res => {
            this.delInviteItem = [];
            this.search();
          }).catch(error => {
            this.$alert(errMessage, {showClose: false});
            this.delInviteItem = [];
            loading.close();
          });
        }).catch(action => {
          this.delInviteItem = [];
          loading.close();
        });
      }
    },
    created() {
      this.fetchInvites();
    },
    watch: {
      delInviteItem: function () {
        $(".is-checkedbox").removeClass('is-checkedbox');
        let num = this.delInviteItem.length;
        for(let i = 0; i<num; i++){
          let str = '#s' + this.delInviteItem[i];
          $(str).addClass('is-checkedbox');
        }
      }
    }
  }
</script>


<style scoped>

</style>


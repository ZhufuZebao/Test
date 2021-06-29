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
        <h2>社内一覧</h2>
        <ul class="header-nav schedule" style="border: 0">
          <li class="header-nav-schedule-li" style="width: auto !important;">
            <span class="current-li"><router-link to="/contact">社内</router-link></span>
            <span class="button-wrap non-current-li"><router-link to="/contact/invite">協力会社</router-link></span>
            <span class="button-wrap non-current-li"><router-link to="/contact/friend">職人</router-link></span>
          </li>
        </ul>
        <div class="flo">
          <el-input placeholder="名前/組織名で検索"
                    @change="search" v-model="searchName"  suffix-icon="searchForm-submit" >
          </el-input>
        </div>
      </div>
      <UserProfile :isContact="isContact" @fetchContacts="fetch"/>
    </header>
    <section class="customer-wrapper invite-form delet-img">
      <div class="customertable listaddimg">
        <div class="list-del-common">
          <ul class="list-del-header report-serch clearfix">
            <li class="customer-li" style="padding-left: 40px">
              名前
              <span @click="sort('name')"><i class="el-icon-d-caret"></i></span>
            </li>
            <li class="customer-li">
              &nbsp;
            </li>
            <li class="customer-li">
              組織名
              <span @click="sort('enterprise')"><i class="el-icon-d-caret"></i></span>
            </li>
            <li class="customer-li">
              メールアドレス
            </li>
            <li class="customer-li">
              追加日
              <span @click="sort('date')"><i class="el-icon-d-caret"></i></span>
            </li>
          </ul>
        </div>

        <ul class="table-scroll tablelisthover">
          <li :id="'s'+contact.id" :key="contact.id" class="clearfix btns" date-tgt="wd1" v-for="contact in contacts">
            <span class="customer-li">
              <div>
              <img :src="'file/users/'+contact.file" alt="" v-if="contact.file" class="pro-photo">
                    <img src="images/icon-chatperson.png" alt="" v-else class="pro-photo">
              <span class="btn-span">{{contact.name}}</span>
                </div>
            </span>
            <span class="customer-li"><a href="javascript:void(0)" @click="chatLink(contact.id)">
                <img class="icon-blue-chat" src="images/icon-blue-chat.png" alt=""></a></span>
            <span class="customer-li" v-if="contact.enterprise">{{contact.enterprise.name}}</span>
            <span class="customer-li" v-else></span>
            <span class="customer-li"><a :href="'mailto:'+ contact.email">{{contact.email}}</a></span>
            <span class="customer-li">{{contact.created_at}}</span>
          </li>
        </ul>
      </div>
    </section>
  </div>
</template>

<script>
  import Pagination from "../../components/common/Pagination";
  import UserProfile from "../../components/common/UserProfile";
  import InviteLists from '../../mixins/InviteLists';
  import Calendar from '../../mixins/Calendar';
  import Messages from "../../mixins/Messages";

  export default {
    components: {
      Pagination,
      UserProfile,
    },
    mixins: [Calendar,InviteLists,Messages],
    data() {
      return {
        contacts: {},         //データ
        searchName:'',      //詳細検索対象
        sortCol: "",          //ソートカラム
        order: "asc",            //昇順、降順
        isContact: true,
      }
    },
    methods: {
      fetch: function () {
        let errMessage = this.commonMessage.error.contactList;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get('/api/getContactList').then((res) => {
          this.contacts = res.data;
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
        loading.close();
      },
      //inviteを検索する
      search() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.contactList;
        axios.post('/api/contactSearch', {
          searchName: this.searchName,
        }).then((res) => {
          this.contacts = res.data;
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
        loading.close();
      },
      //チャットリンク
      chatLink(contactId){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.system;
        axios.post('/api/contactChatLink', {
          contactUserId : contactId,
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
        if (this.contacts.length !== 0) {
          if (this.sortCol === sortCol) {
            this.changeOrder();
          } else {
            this.sortCol = sortCol;
            this.order = 'asc'
          }
          let errMessage = this.commonMessage.error.contactList;
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.post('/api/contactSort', {
            searchName: this.contacts,
            sort: this.sortCol,
            order: this.order,
          }).then((res) => {
            this.contacts = res.data;
            loading.close();
          }).catch(error => {
            this.$alert(errMessage, {showClose: false});
            loading.close();
          });
        }
      },

      changeOrder() {
        if (this.order === 'asc') {
          this.order = 'desc';
        } else {
          this.order = 'asc';
        }
      },
    },
    created() {
      this.fetch();
    },
  }
</script>

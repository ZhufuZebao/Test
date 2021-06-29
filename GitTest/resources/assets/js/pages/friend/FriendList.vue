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
        <h2>職人一覧</h2>
        <a class="title-add">
          <img src="images/add@2x.png" @click.prevent="openMailModal"/>
        </a>
        <ul class="header-nav schedule" style="border: 0">
          <li class="header-nav-schedule-li" style="width: auto !important;">
            <span class="non-current-li"><router-link to="/contact">社内</router-link></span>
            <span class="non-current-li"><router-link to="/contact/invite">協力会社</router-link></span>
            <span class="current-li"><router-link to="/contact/friend">職人</router-link></span>
          </li>
        </ul>
        <div class="flo">
          <el-input placeholder="職人名、メールアドレス検索"
                    @change="search" v-model="searchName" suffix-icon="searchForm-submit">
          </el-input>
        </div>
      </div>
      <UserProfile/>
    </header>
    <section class="customer-wrapper invite-form delet-img">
      <div>
        <a class="title-del"  v-if="delFriendItem.length !== 0" @click.prevent="delFriendItems">
          <img  class="img-delete" src="images/icon-dust2.png" alt="" >
        </a>
      </div>
      <div class="customertable listaddimg">
        <div class="list-del-common">
          <ul class="list-del-header report-serch clearfix">
            <li class="friend-li">

              {{friendsName('user_name')}}
              <span @click="sort('name')"><i class="el-icon-d-caret"></i></span>
            </li>
            <li class="friend-li">
              &nbsp;
            </li>
            <li class="friend-li">
              {{friendsName('company_name')}}
              <span @click="sort('company_name')"><i class="el-icon-d-caret"></i></span>
            </li>
            <li class="friend-li">
              {{friendsName('area1')}}
            </li>
            <li class="friend-li">
              {{friendsName('email')}}
            </li>
            <li class="friend-li">
              {{friendsName('created_at')}}
              <span @click="sort('date')"><i class="el-icon-d-caret"></i></span>
            </li>
          </ul>
        </div>
        <ul class="table-scroll tablelisthover">
          <el-checkbox-group v-model="delFriendItem">
            <li :id="'s'+friend.id" :key="friend.id" class="clearfix btns" date-tgt="wd1" v-for="friend in friends">
              <span class="friend-li" v-if="friend.accounts">
                <el-checkbox :label="friend.id" v-if="!friend.accounts.deleted_at">
                  <div @click="friendDetail(friend.from_user_id,friend.contact_agree)" v-if="friend.accounts_invite">
                    <img :src="'file/users/'+friend.accounts.file" alt="" v-if="friend.accounts && friend.accounts.file" class="pro-photo">
                    <img src="images/icon-chatperson.png" alt="" v-else class="pro-photo">
                   <span class="btn-span">{{ friend.accounts.name }}</span>
                  </div>
                  <div @click="friendDetail(friend.to_user_id,friend.contact_agree)" v-else>
                    <img :src="'file/users/'+friend.accounts.file" alt="" v-if="friend.accounts && friend.accounts.file" class="pro-photo">
                    <img src="images/icon-chatperson.png" alt="" v-else class="pro-photo">
                    <span class="btn-span">{{ friend.accounts.name }}</span>
                  </div>
                </el-checkbox>
                <el-checkbox :label="friend.id" v-else-if="(friend.contact_agree === '1' || friend.contact_agree === '2') && friend.accounts.deleted_at">
                 <span class="btn-span">このユーザは削除されました&nbsp;</span>
                </el-checkbox>
              </span>
              <span class="friend-li" v-else>
                <el-checkbox :label="friend.id">&nbsp;</el-checkbox>
              </span>
              <span class="friend-li" v-if="friend.contact_agree === '1' && !friend.accounts.deleted_at">
                <a href="javascript:void(0)" @click="chatLink(friend.accounts.id)">
                  <img class="icon-blue-chat" src="images/icon-blue-chat.png" alt=""></a>
              </span>
              <span class="friend-li" v-else>
                &nbsp;
              </span>
              <span class="friend-li" v-else><a href="javascript:void(0)"> </a></span>
              <span class="friend-li" v-if="friend.accounts && friend.accounts.company_name">{{ friend.accounts.company_name }}</span>
              <span class="friend-li" v-else></span>
              <el-tooltip :content=friend.area placement="top" effect="light"
                          v-if="friend.accounts && !friend.accounts.deleted_at && friend.accounts.publicity === '1'">
                <span class="friend-li">{{ friend.area }}</span>
              </el-tooltip>
              <el-tooltip :content=noAgree placement="top" effect="light"
                          v-else-if="friend.accounts && !friend.accounts.deleted_at && friend.accounts.publicity !== '1' && friend.contact_agree !== '1'">
                <span class="friend-li">未承認のため非表示</span>
              </el-tooltip>
              <span class="friend-li" v-else></span>
              <span class="friend-li" v-if="friend.accounts && !friend.accounts.deleted_at &&(friend.contact_agree === '1' || friend.append_status === '1' || !friend.append_status)">
                <a :href="'mailto:'+ friend.email">{{ friend.email }}</a></span>
              <span class="friend-li" v-else-if="friend.accounts && !friend.accounts.deleted_at && friend.contact_agree !== '1' && friend.append_status === '0'">
                  名前検索から招待の未承認状態
              </span>
              <span class="friend-li" v-else-if="friend.accounts && friend.accounts.deleted_at && friend.contact_agree === '1'">
              </span>
              <span class="friend-li" v-else>
                <a :href="'mailto:'+ friend.email">{{ friend.email }}</a>
              </span>
              <span class="friend-li" v-if="friend.accounts && !friend.accounts.deleted_at && friend.contact_agree === '1'">{{ contactDateFormat(friend.contact_date) }}</span>
              <span class="friend-li" v-else-if="friend.accounts && friend.accounts.deleted_at && friend.contact_agree === '1'"></span>
              <span class="friend-li" v-else-if="friend.contact_agree === '2'"><span class="not-confirmed">拒否しました</span></span>
              <span class="friend-li" v-else-if="friend.recipient"><span class="to-confirmed">未承認</span></span>
              <span class="friend-li" v-else><span class="to-confirmed">承認待ち</span></span>

            </li>
          </el-checkbox-group>
        </ul>
      </div>
      <div class="pagination-center" v-if="peopleCount !== ''">
        {{peopleCount}}名を招待いたしました
      </div>
    </section>
    <MailModal @closeMailModal="closeMailModal" @fetchFriends="fetchFriends" @sendPeopleCount="getPeopleCount" v-if="showMailModal"></MailModal>
  </div>
</template>

<script>
  import Pagination from "../../components/common/Pagination";
  import UserProfile from "../../components/common/UserProfile";
  import FriendLists from '../../mixins/FriendLists';
  import Calendar from '../../mixins/Calendar';
  import Messages from "../../mixins/Messages";
  import MailModal from '../../components/friend/MailModal';

  export default {
    components: {
      Pagination,
      UserProfile,
      MailModal
    },
    mixins: [Calendar, FriendLists, Messages],
    data() {
      return {
        noAgree: '',
        friends: {},
        delFriendItem: [],
        peopleCount: '',
        sortCol: "",          //ソートカラム
        order: "asc",            //昇順、降順
        searchName: '',      // 詳細検索対象
        showMailModal: false,
        area:'',             //対応可能エリア
      }
    },
    methods: {
      getPeopleCount(data) {
        this.peopleCount = data;
        setTimeout(() => {
          this.peopleCount = '';
        }, 3000);
      },
      //並べ替え
      sort(sortCol) {
        if (this.friends.length !== 0) {
          if (this.sortCol === sortCol) {
            this.changeOrder();
          } else {
            this.sortCol = sortCol;
            this.order = 'asc'
          }
          let errMessage = this.commonMessage.error.friendList;
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          axios.post('/api/getFriendSearchSort', {
            searchName: this.friends,
            sort: this.sortCol,
            order: this.order,
          }).then((res) => {
            this.friends = res.data;
            loading.close();
          }).catch(error => {
            this.$alert(errMessage, {showClose: false});
            loading.close();
          });
        }
      },

      //追加日 format
      contactDateFormat(contactData){
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
      // 職人 詳細
      friendDetail: function (userId,agree) {
        if (agree === '1'){
          this.$router.push({path: '/friend/detailInformation/' + userId});
        }
      },

      // 空チェック
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj === ""
      },

      // 職人 検索
      search() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.friendList;
        axios.post('/api/getFriendSearch', {
          searchName: this.searchName,
        }).then((res) => {
          this.friends = res.data;
          for (let i = 0; i < res.data.length; i++) {
            try {
              //対応可能エリア処理
              let userWorkareas = res.data[i]['userworkareas'];
              if(userWorkareas) {
                for (let j = 0; j < userWorkareas.length; j++) {
                  if (userWorkareas[j] && userWorkareas[j]['workareas']) {
                    this.area = this.area + '・' + userWorkareas[j]['workareas'].name;
                  }
                }
                for (let k = 0; k < userWorkareas.length; k++) {
                  if (userWorkareas[k] && userWorkareas[k]['workplaces']) {
                    this.area = this.area + '・' + userWorkareas[k]['workplaces'].name;
                  }
                }
              }
              if (this.area[0] === '・') {
                this.area = this.area.substr(1);
              }
              if (!this.area){
                this.area = '';
              }
              this.$set(this.friends[i], 'area', this.area);
              this.area = '';
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

      // 職人 一覧
      fetchFriends: function (type=null) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.friendList;
        axios.post('/api/getFriendList',{
          type:type
        }).then((res) => {
          this.friends = res.data;
          for (let i = 0; i < res.data.length; i++) {
            try {
              //対応可能エリア処理
              let userWorkareas = res.data[i]['userworkareas'];
              if(userWorkareas){
                for (let j = 0; j < userWorkareas.length; j++) {
                  if (userWorkareas[j] && userWorkareas[j]['workareas']) {
                    this.area = this.area + '・' + userWorkareas[j]['workareas'].name;
                  }
                }
                for (let k = 0; k < userWorkareas.length; k++) {
                  if (userWorkareas[k] && userWorkareas[k]['workplaces']) {
                    this.area = this.area + '・' + userWorkareas[k]['workplaces'].name;
                  }
                }
              }
              if (this.area[0] === '・') {
                this.area = this.area.substr(1);
              }
              this.$set(this.friends[i], 'area', this.area);
              this.area = '';
            } catch (e) {
              this.$alert(errMessage, {showClose: false});
              loading.close();
            }
          }
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
        loading.close();
      },

      // 職人を削除する
      delFriendItems() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.friendDelete;
        this.$confirm(this.commonMessage.confirm.delete.account).then(() => {
          axios.post("/api/delFriend", {
            id: this.delFriendItem,
          }).then(res => {
            this.delFriendItem = [];
            this.search();
          }).catch(error => {
            this.$alert(errMessage, {showClose: false});
            this.delFriendItem = [];
            loading.close();
          });
        }).catch(action => {
          this.delFriendItem = [];
          loading.close();
        });
      },
      // チャットリンク
      chatLink(id) {
        axios.post('/api/friendChatLink', {
          id: id,
        }).then((res) => {
          let groupId = res.data.group_id;
          let userId = res.data.user_id;
          this.$router.push({path: "/chat", query: {groupId: groupId, userId: userId}});
        })
      },
      openMailModal(){
        this.showMailModal = true;
      },
      closeMailModal: function () {
        this.showMailModal = false;
      },
    },
    created() {
    },
    watch: {
      delFriendItem: function () {
        $(".is-checkedbox").removeClass('is-checkedbox');
        let num = this.delFriendItem.length;
        for(let i = 0; i<num; i++){
          let str = '#s' + this.delFriendItem[i];
          $(str).addClass('is-checkedbox');
        }
      }
    },
    mounted() {
      this.noAgree = this.commonMessage.warning.noAgree;
      if (this.$route.query.type === '2') {
        this.fetchFriends(this.$route.query.type);
      }else{
        this.fetchFriends();
      }
    }
  }
</script>

<style scoped>
  .flo {
    float: left;
  }
  .friend-li {
    width: 15%;
  }
  .friend-li:first-child {
    width: 21%;
  }
  .friend-li:nth-child(2) {
    width: 6%;
  }
  .friend-li:nth-child(3) {
    width: 22%;
  }
  .friend-li:nth-child(5) {
    width: 20%;
  }

</style>

<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show schedule-modal">
      <div class="modalBody  mail-modal" ref="modalBody" v-bind:style="{'margin-left': modalLeft,'margin-top': modalTop}">
        <div class="modal-close" @click="close">×</div>
        <div class="modalBodycontent">
          <h3>職人を追加する</h3>
          <div class="schedule-modalBodycontent friend-add clearfix" v-if="indexListFlag">
            <p>
              <img src="images/add@2x.png">
              <a @click="mailAppend()">メールアドレスで追加する</a>
            </p>
            <p>
              <img src="images/add@2x.png">
              <a @click="searchAppend()">名前検索で追加する</a>
            </p>
          </div>
          <div class="schedule-modalBodycontent friend-add clearfix" v-else>
            <el-form :model="friends" :rules="rules" ref="form" @submit.native.prevent>
              <el-form-item class="email-form">
                <el-tag v-for="(email,index) in emails" :key="index" closable @close="delCheckItem(index)"
                        :type="messageType">
                  <span>{{email}}</span>
                </el-tag>
              </el-form-item>
              <el-form-item prop="" class="email-input" v-if="searchListFlag">
                <el-input placeholder="名前、会社名" @change="searchEmail(friends.searchWord)" v-model="friends.searchWord"
                          maxlength="191" suffix-icon="searchForm-submit"></el-input>
              </el-form-item>
              <el-form-item prop="email" class="email-input" v-else>
                <el-input placeholder="メールアドレス" @blur="add" v-model="friends.email" maxlength="191"
                          onkeypress="if (event.keyCode === 13) return false;"></el-input>
              </el-form-item>
            </el-form>
            <section class="customer-wrapper invite-form" v-if="searchListFlag">
              <div class="customertable">
                <ul class="list-del-header report-serch clearfix">
                  <li class="customer-li">
                    {{friendsName('user_name')}}
                    <span @click="sort('name')"><i class="el-icon-d-caret"></i></span>
                  </li>
                  <li class="customer-li">
                    {{friendsName('company_name')}}
                    <span @click="sort('company_name')"><i class="el-icon-d-caret"></i></span>
                  </li>
                </ul>
                <ul class="table-scroll">
                  <el-checkbox-group v-model="selectFriendItem">
                    <li :id="'mail'+friend.id" :key="friend.id" class="clearfix btns" date-tgt="wd1"
                        v-for="friend in searchFriends" v-if="friend">
                      <el-checkbox :label="friend.email" @change="selectItem" style="overflow: hidden">
                        <div v-if="friend.name" v-model='checkVal'>
                          {{ friend.name }}
                        </div>
                      </el-checkbox>
                      <span class="customer-li" style="overflow: hidden" v-if="friend.company_name">{{ friend.company_name }}</span>
                    </li>
                  </el-checkbox-group>
                </ul>
              </div>
            </section>

            <dl class="clearfix mail-textarea">
              <el-input type="textarea" :rows="4" placeholder="メッセージ" v-model="friends.message">
              </el-input>
            </dl>
            <div class="clearfix"></div>
            <div class="button-wrap clearfix">
              <div class="button-lower remark">
                <el-button @click="sendEmail" :disabled="!sendEmailFlag">招待する</el-button>
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
  import validation from '../../validations/friend'
  import FriendLists from '../../mixins/FriendLists';

  export default {
    name: "MailModal",
    props: ['chatEmail'],
    mixins: [Messages, FriendLists, validation],
    data: function () {
      return {
        checkVal:'',
        nameOrder:true,               //昇順 true、降順 false
        companyOrder: true,            //昇順 true、降順 false
        sendEmailFlag:false,
        searchFriends: [],
        selectFriendItem: [],
        searchListFlag: false,
        indexListFlag: true,
        isMounted: false,
        friends: {
          email:'',
          message:'',
        },         //職人データ
        emails:[],
        messageType : 'info',
      }
    },
    methods:{
      // 空チェック
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj === ""
      },
      //並べ替え
      sort(sortCol) {
        if (this.searchFriends.length) {
          if ('name' === sortCol) {
            this.changeOrder(sortCol,this.nameOrder)
            this.nameOrder = !this.nameOrder;
          } else if ('company_name' === sortCol) {
            this.changeOrder(sortCol,this.companyOrder)
            this.companyOrder = !this.companyOrder;
          }
        }
      },
      changeOrder(sortCol,order) {
        if (order){
          this.searchFriends.sort(function(newItem, oldItem){
            if (!newItem[sortCol]){
              newItem[sortCol] = ''
            }else if (!oldItem[sortCol]){
              oldItem[sortCol] = ''
            }
            return newItem[sortCol].localeCompare(oldItem[sortCol]);
          });
        } else{
          this.searchFriends.sort(function(newItem, oldItem){
            if (!newItem[sortCol]){
              newItem[sortCol] = ''
            }else if (!oldItem[sortCol]){
              oldItem[sortCol] = ''
            }
            return oldItem[sortCol].localeCompare(newItem[sortCol]);
          });
        }
      },
      selectItem(){
        if(this.selectFriendItem.length){
          this.sendEmailFlag=true;
        }else{
          this.sendEmailFlag=false;
        }
      },
      fetchFriends(word) {
        if (!word){
          return false;
        }
        let errMessage = this.commonMessage.error.insert;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)',target:document.querySelector('.modalBodycontent')});
        axios.post("/api/searchFriend", {
          word: word,
        }).then(res => {
          this.searchFriends=res.data;
          loading.close();
        }).catch(error => {
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      mailAppend() {
        this.indexListFlag = false;
      },
      searchAppend() {
        this.searchListFlag = true;
        this.indexListFlag = false;
        this.fetchFriends();
      },
      close() {
        this.$emit('closeMailModal');
      },
      //メールsend
      sendEmail() {
        let emails=[];
        if (this.selectFriendItem.length){
          emails = this.selectFriendItem;
        }else if (this.emails.length){
          emails = this.emails;
        }
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.friendCreate;
        axios.post('/api/sendFriendEmail', {
          toEmail: emails,
          message: this.friends.message,
          nameSearch: this.searchListFlag
        }).then((res) => {
          if (res.data) {
            if (res.data.result === 0) {
              let length = 0;
              if (res.data.params){
                length = res.data.params
              }
              this.close();
              this.$emit("sendPeopleCount", length);
              this.$emit('fetchFriends');
            } else {
              this.$alert(res.data.errors[0], {showClose: false});
            }
          }
          loading.close();
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },
      searchEmail(word) {
        this.searchFriends = [];
        this.fetchFriends(word);
      },
      //メール追加
      add() {
        this.$refs['form'].validate((valid) => {
          if (valid && this.friends.email !== '') {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)',target:document.querySelector('.modalBodycontent')});
            let errMessage = this.commonMessage.error.system;
            axios.post('/api/checkFriendEmail', {
              checkEmail: this.friends.email,
            }).then((res) => {
              if (res.data === 'shokunin') {
                if (this.emails.indexOf(this.friends.email) === -1){
                  this.emails.push(this.friends.email);
                  this.friends.email = ''
                } else {
                  this.friends.email = ''
                }
              }
              if (this.emails.length){
                this.sendEmailFlag=true;
              }else{
                this.sendEmailFlag=false;
              }
              if (res.data === 'enterprise') {
                this.$alert('このメールアドレスは事業者として登録されています', {showClose: false});
                this.friends.email = '';
              }
              if (res.data === 'already') {
                this.$alert(this.commonMessage.error.already, {showClose: false});
                this.friends.email = '';
              }
            }).catch(error => {
              this.$alert(errMessage, {showClose: false});
              loading.close();
            });
            loading.close();
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
      if(this.chatEmail){
        this.friends.email = this.chatEmail;
      }
    }
  }
</script>

<style scoped>
  .modal-show {
    display: block;
  }
</style>

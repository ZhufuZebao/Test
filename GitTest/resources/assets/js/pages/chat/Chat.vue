<template>
  <div class="container clearfix chat commonAll" @click="closeEnter">
    <header>
      <h1>
        <router-link to="/chat">
          <div class="commonLogo">
            <ul>
              <li class="bold">CHAT</li>
              <li>チャット</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->

      <UserProfile/>
    </header>
    <div class="title-wrap chat-wrapper">
      <el-tabs type="card" v-model="active">
        <el-tab-pane name="activeFirst">
            <span slot="label" @click.prevent="fetchChatPersonList()"><i><img src="images/icon-chat2.png"
                                                                              alt=""> </i><p>チャット</p> </span>
          <div class="chatbuttom">
            <el-input placeholder="メッセージ、タスクを検索" v-model="chatMessageSearchWord"
                      suffix-icon="searchForm-submit" class="popoverto-input" @change="searchChatList">
            </el-input>
          </div>
          <chatList ref="chatListModel" :chatMessageSearchWord="chatMessageSearchWord" @searchBack="searchChatList"
                    :searchChatListFlag="searchChatListFlag" @conversion="conversion"
                    @setGroupFn="setGroup" :leaveToChatFlag="leaveToChatFlag" :chatJumpObj="chatJumpObj"
                    @deleteGroup="deleteGroup" @delInGroup="delInGroup" :activeFlag="active" @clearAllSearchWord="clearAllSearchWord"/>
        </el-tab-pane>

        <el-tab-pane name="activeThird">
          <span slot="label" @click.prevent="fetchPersonList()"><i><img src="images/icon-chatlist.png" alt=""></i><p>案件</p> </span>
          <div class="chatbuttom">
            <el-input placeholder="メッセージ、タスクを検索" v-model="chatMessageSearchWord"
                      suffix-icon="searchForm-submit" class="popoverto-input" @change="searchChatList"></el-input>
          </div>
          <chatContact ref="chatContactModel"
                       :chatMessageSearchWord="chatMessageSearchWord" @searchBack="searchChatList"
                       :searchChatListFlag="searchChatListFlag" @conversion="conversion"
                       @setGroupFn="setGroup" :leaveToChatProFlag="leaveToChatProFlag" :chatJumpObj="chatJumpObj" @clearAllSearchWord="clearAllSearchWord"
                       @setChatList="setChatList" @deleteGroup="deleteGroup" @delInGroup="delInGroup" :activeFlag="active" @resetJumpFlag="resetJumpFlag"/>
        </el-tab-pane>

        <el-tab-pane name="activeSecond">
            <span slot="label" @click.prevent="fetchChatTaskList()"><i>
              <img src="images/icon-chatcheck.png" alt=""></i><p>タスク</p></span>
          <div class="title-add" @click.prevent="createTask()">
            <img src="images/add@2x.png"/>
          </div>
          <div class="chatbuttom">
            <el-input placeholder="メッセージ、タスクを検索" v-model="searchTaskWord" maxlength="30" @conversion="conversion"
                      suffix-icon="searchForm-submit" class="popoverto-input" @change="searchChatTask">
            </el-input>
          </div>
          <ChatTask :searchTaskWord="searchTaskWord" @leaveToChat="leaveToChat" ref="chatTaskModel"/>
        </el-tab-pane>


        <el-tab-pane name="activeFour">
            <span slot="label" @click.prevent="fetchChatFileList()"><i><img src="images/icon-chat-file.png"
                                                                            alt=""></i><p>File</p></span>
          <div class="chatbuttom">
            <el-input placeholder="ファイル名検索" v-model="searchFileWord" maxlength="30"
                      suffix-icon="searchForm-submit" class="popoverto-input" @change="searchChatFile">
            </el-input>
          </div>
          <ChatFile :searchFileWord="searchFileWord" ref="chatFileModel" @conversion="conversion"
                    @setChatList="setChatList" @leaveToChat="leaveToChat"/>
        </el-tab-pane>
      </el-tabs>
    </div>
    <div>
    </div>
  </div>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import Messages from "../../mixins/Messages";
  import reportValidation from '../../validations/report'

  import chatContact from '../../components/chat/ChatContact';
  import chatList from '../../components/chat/ChatList';
  import ChatTask from '../../components/chat/ChatTask';
  import ChatFile from '../../components/chat/ChatFile';

  export default {
    name: "Chat",
    components: {
      UserProfile,
      chatContact,
      chatList,
      ChatTask,
      ChatFile
    },
    mixins: [
      reportValidation,
      Messages
    ],
    data: function () {
      return {
        select: '',
        person: [],
        group: {},
        DialogVisible: false,
        active: 'activeFirst',
        linkGroup: 'linkGroup-0',
        current: '',
        imageUrl: '',
        validateMessage: '',
        // imgFile: null,
        // select: '',
        searchGroupPerson: '',
        inputpopover: '',
        tabPosition: 'left',
        chatMessageSearchWord: '',
        inputtask: '',
        searchTaskWord: '',    // メッセージ、タスクを検索
        searchFileWord: '',    // メッセージ、タスクを検索
        activeName: 'first',
        radioNo: 3,
        radioYes: 6,
        searchChatListFlag: false,
        linkGroupCurrent: '',
        leaveToChatFlag: false,
        leaveToChatProFlag: false,
        chatJumpObj: null,
        dataJumpDoc: {}
      }
    },
    methods: {
      clearAllSearchWord(){
        this.chatMessageSearchWord = "";
        this.searchTaskWord = "";
        this.searchFileWord = "";
      },
      conversion() {
        if (window.videoChat){
          if (!window.videoChat.group_type && this.active !== 'activeFirst' && !window.proj){
            this.active = 'activeFirst';
            let $sel = 'linkGroup-' + window.videoChat.group_id;
            this.$refs.chatListModel.fetchChatPersonList(window.videoChat.group_id);
          } else if(window.videoChat.group_type && this.active !== 'activeThird'){
            this.active = 'activeThird';
            this.setProjectChatList(window.videoChat.group_id, 0);
          }
        }
      },
      leaveToChat(data) {
        if (data && data.pro){
          this.active = 'activeThird';
          this.leaveToChatProFlag = !this.leaveToChatProFlag;
          this.chatJumpObj = data;
        } else{
          this.active = 'activeFirst';
          this.leaveToChatFlag = !this.leaveToChatFlag;
          this.chatJumpObj = data;
        }
      },
      resetJumpFlag() {
        this.leaveToChatProFlag = false;
        this.chatJumpObj = null;
      },
      searchChatList() {
        this.searchChatListFlag = !this.searchChatListFlag;
      },
      setChatList($groupId, $userId, $selector) {
        this.active = 'activeFirst';
        this.$refs.chatListModel.linkGroupCurrent = $selector;
        this.$refs.chatListModel.showChatFlag = true;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        let data = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        let url = '/api/setChatList';
        data.append('userId', $userId);
        data.append('groupId', $groupId);
        axios.post(url, data, headers).then(res => {
          if (this.$route.query.groupId && !$groupId) {
            $groupId = this.$route.query.groupId;
            this.$route.query = {};
          }
          this.$refs.chatListModel.fetchChatPersonList($groupId);
          loading.close();
        }).catch(err => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        })
      },
      setProjectChatList($groupId, $userId, $selector) {
        this.active = 'activeThird';
        this.$refs.chatContactModel.fetchChatPersonList($groupId);
      },
        setTask($taskId, $userId, $selector) {
            this.active = 'activeSecond';
            this.$refs.chatTaskModel.fetchChatTaskList($taskId);
        },
      closeEnter(e) {
        this.$refs.chatListModel.visibled = false;
      },
      //グループを作成
      setGroup(id, g, name, imgFile) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.chatGroupCreate;
        let data = new FormData();
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        if (g) {
          let checkGroupPerson= this.$refs.chatContactModel.checked.checkGroupPerson;
          let checkParticipant= this.$refs.chatContactModel.checked.checkParticipant;
          let checkGroup= this.$refs.chatContactModel.checked.checkGroup;
          let groupIdArray=checkGroupPerson.concat(checkParticipant);
          groupIdArray=groupIdArray.concat(checkGroup);
          data.append('person', JSON.stringify(groupIdArray));
          groupIdArray=[];
        } else {
          let checkGroupPerson= this.$refs.chatListModel.checked.checkGroupPerson;
          let checkParticipant= this.$refs.chatListModel.checked.checkParticipant;
          let checkGroup= this.$refs.chatListModel.checked.checkGroup;
          let groupIdArray=checkGroupPerson.concat(checkParticipant);
          groupIdArray=groupIdArray.concat(checkGroup);
          data.append('person', JSON.stringify(groupIdArray));
          groupIdArray=[];
        }
        data.append('groupName', name);
        if (g) {
          data.append('parentId', g);
        }
        if (imgFile) {
          data.append("file", imgFile);
          imgFile = null;
        }
        let url = '/api/setGroup';
        axios.post(url, data, headers).then(res => {
          let gId = res.data.params.gId;
          let $selector = 'linkGroup-' + gId;
          if (g){
            this.active = 'activeThird';
            this.$refs.chatContactModel.addPerson(res.data.params.group,$selector);
            loading.close();
          } else{
            this.setChatList(gId, id, $selector);
            this.$refs.chatListModel.linkGroupCurrent = $selector;
          }
        }).catch(err => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        })
      },
      fetchChatFileList() {
        this.$refs.chatTaskModel.taskFlag = 1;
        this.clearAllSearchWord();
        this.$refs.chatFileModel.fetchChatFileList(this.searchFileWord);
      },
      fetchPersonList() {
        this.clearAllSearchWord();
        this.$refs.chatTaskModel.taskFlag = 1;
        this.select = '';
        this.$refs.chatContactModel.fetchChatPersonList();
      },
      fetchChatPersonList() {
        this.$refs.chatTaskModel.taskFlag = 1;
        this.clearAllSearchWord();
        this.$refs.chatListModel.fetchChatPersonList();
      },
      delInGroup(uId, gId) {
        this.$refs.chatContactModel.delInGroup(uId, gId);
      },
      deleteGroup(msg) {
        this.$refs.chatContactModel.deleteGroup(msg)
      },
      fetchSearchPerson() {
        this.$refs.chatListModel.fetchSearchPerson();
      },
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj == ""
      },

      handleClick(tab, event) {
      },
      taskDetail: function () {
        axios.get("/api/getEditCustomer", {
          params: {
            id: this.$route.query.id,
            officeId: this.$route.query.officeId,
          }
        }).then(res => {
          this.customer.id = res.data.customer.id;
          this.customer.name = res.data.customer.name;
          this.customer.phonetic = res.data.customer.phonetic;
          this.customer.office = res.data.office[0];
          this.customer.office.people = res.data.office[0].people;
          this.customer.office.billings = res.data.office[0].billings;
          this.arr = this.customer.office.tel.split('-');
          if (this.arr.length >= 2) {
            this.$set(this.customer.office, 'telOut', this.arr[0]);
            this.$set(this.customer.office, 'telIn', this.arr[1]);
          } else if (this.arr.length === 1) {
            this.$set(this.customer.office, 'telOut', this.arr[0]);
            this.$set(this.customer.office, 'telIn', "");
          }
        });
      },
      createTask() {
        this.$refs.chatTaskModel.createTask();
      },
      fetchChatTaskList() {
        this.$refs.chatTaskModel.taskFlag = 1;
        this.$refs.chatTaskModel.fetchChatTaskList();
        this.clearAllSearchWord();
      },
      searchChatTask(searchTaskWord) {
        this.$refs.chatTaskModel.searchChatTask(searchTaskWord);
      },
      searchChatFile(searchFileWord) {
        this.$refs.chatFileModel.fetchChatFileList(searchFileWord);
      },
      searchChatPeopleAndGroup(select) {

        this.$refs.chatContactModel.searchChatPeopleAndGroup(select);
        this.$router.push({path: "/chat", query: {groupId: this.groupId, userId: this.userId}});
      },
    },
    created() {
    },

    mounted() {
        axios.get("/api/getFilesNeedSent")
            .then(res => {
                let groupId =  res.data.groupId;
                let filesSent = res.data.filesSent;
                let project = res.data.project;
                if(groupId > 0) {
                  window.groupId = groupId; // mount this par to window
                  window.filesSent = filesSent; // mount this par to window
                  this.chatJumpObj = {messageId: 0, groupId: groupId};
                    let sel = 'linkGroup-' + groupId;
                    let uid = 0 ;
                    if(project){
                        this.setProjectChatList(groupId, uid);
                    }else{
                        this.setChatList(groupId, uid, sel);
                    }
                 // this.leaveToChat(this.chatJumpObj);
                 return false;
                }
                return true;
            })
            .then((opFlag) => {
                    if (opFlag && this.$route.query.proId) {
                        let $gId = this.$route.query.proId;
                        let $uId = 0;
                        this.setProjectChatList($gId, $uId);
                    } else if (opFlag && this.$route.query.groupId) {
                        let $gId = this.$route.query.groupId;
                        let $uId = 0 ;
                        if (this.$route.query.userId){
                            $uId = this.$route.query.userId;
                        }
                        let $sel = 'linkGroup-' + $gId;
                        this.setChatList($gId, $uId, $sel);
                    } else if(opFlag && this.$route.query.taskId) {
                        let $taskId = this.$route.query.taskId;
                        let $uId = 0;
                        this.setTask($taskId);
                    } else if(opFlag && this.$route.query.taskType){
                        this.$refs.chatTaskModel.activeName = 'second';
                        this.active = 'activeSecond';
                        this.$refs.chatTaskModel.radioNo = parseInt(this.$route.query.taskType);
                        this.$refs.chatTaskModel.fetchChatTaskList();
                    } else {
                      if(opFlag) {
                        this.fetchChatPersonList();
                      }
                    }
                }
            )
          .catch(function(error) {
              console.log(error);
            }
            //optional operation,if error occur,just let it be
          );
    },
    beforeRouteLeave (to, from, next) {
      let taskFlag = this.$refs.chatTaskModel.taskFlag;
      let msg = this.commonMessage.confirm.warning.jump;
      if ((to.path === '/chat'
          || to.path === '/dashboard'
          || to.path === '/schedule'
          || to.path === '/company'
          || to.path === '/contact'
          || to.path === '/document'
          || to.path === '/invite'
          || to.path === '/friend'
          || to.path === '/project'
          || to.path === '/customer') &&
          taskFlag === 2){
        let res = confirm(msg);
        if (res) {
          next();
        }else{
          return false;
        }
      }else{
        next();
      }
    },
    computed: {}
  }

</script>

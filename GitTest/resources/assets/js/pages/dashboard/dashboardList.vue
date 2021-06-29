<template>
  <div class="container dashboard commonAll">
    <header>
      <h1>
        <router-link to="/dashboard">
          <div class="commonLogo">
            <ul>
              <li class="bold">DASHBOARD</li>
              <li>ダッシュボード</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <UserProfile/>
    </header>
    <div class="dashboard-table">
      <div class="dashboard-list dashboard-first">
        <p class="dashboard-title-position"><span class="dashboard-title">新着情報</span>
          <a class="float-right-msg" @click="clearNewMsg">すべて確認済みにする</a>
        </p>
        <div @scroll="checkLazyLoad" ref="home">
          <ul class="dashboard-title-but">
              <el-button @click="selectItem('chat','0')"><span class="select-item" ref="chat">チャット</span></el-button>
              <el-button @click="selectItem('sch','3')"><span class="select-item" ref="sch">スケジュール</span></el-button>
              <el-button @click="selectItem('doc','5')"><span class="select-item" ref="doc">ドキュメント</span></el-button>
              <el-button @click="selectItem('invite','6')"><span class="select-item" ref="invite">協力会社</span></el-button>
              <el-button @click="selectItem('friend','7')"><span class="select-item" ref="friend">職人</span></el-button>
              <el-button @click="selectItem('project','4')"><span class="select-item" ref="project">案件</span></el-button>
              <el-button @click="selectItem('customer','8')" v-if="user.enterprise_id"><span class="select-item" ref="customer">施主</span></el-button>
          </ul>
          <DashboardUrl v-if="showTaskFlag" @DashboardUrlBack="DashboardUrlBack" :id="groupId"></DashboardUrl>
          <ul v-for="msg in newMsg" v-if='msg' style="margin-top: 20px">
            <li @click="openUrl(parseInt(msg.type),msg.related_id,0,msg.st_datetime,msg.content)" class="dashboard-msg-title gimg" >
              <p>
              <span v-if="msg.type==='0' || msg.type==='1' ||msg.type==='2'" class="dashboard-text-title chat-color">
                チャット</span>
              <span v-else-if="msg.type==='3'" class="dashboard-text-title schedule-color">
                スケジュール</span>
              <span v-else-if="msg.type==='5'" class="dashboard-text-title docment-color">
                ドキュメント</span>
              <span v-else-if="msg.type==='6'" class="dashboard-text-title invite-color">
                協力会社</span>
              <span v-else-if="msg.type==='7'" class="dashboard-text-title friend-color">
                職人</span>
              <span v-else-if="msg.type==='4'" class="dashboard-text-title project-color">
                案件</span>
              <span v-else-if="msg.type==='8' && user.enterprise_id" class="dashboard-text-title customer-color">
                施主</span>
            </p>
            <p :style="{'fontWeight':(!msg.read || msg.read === '0')?'bold':'normal'}">
              <span  v-if="msg.type !== '5'" class="msg-content">{{emoji(msg.content)}}</span>
              <span v-html="showMessage(msg)"></span>
              <span v-if="(msg.type==='6' || msg.type==='7' || msg.type==='8') && msg.updated_at">({{msg.updated_at.split('-')[1]}}/{{msg.updated_at.split('-')[2].split(' ')[0]}}
              &nbsp;{{msg.updated_at.split(' ')[1].split(':')[0]}}:{{msg.updated_at.split(' ')[1].split(':')[1]}})</span>
            </p>
            </li>
          </ul>
        </div>
      </div>
      <div class="dashboard-list dashboard-middle">
        <p class="dashboard-title-position"><span class="dashboard-title">案件</span></p>
        <div class="dashboard-project">
          <div class="dashboard-project-img" v-for="project in projects">
            <div @click.right.prevent="fixedLabel(project.id)" @click.stop="openUrl(4,project.id,1)">
              <div  class="dashboard-project-image" >
                <img   v-if="project.subject_image&&!project.subject_image.match('images')" v-bind:src="'file/projects/'+project.subject_image">
                <img  v-else src="images/no-image.png" >
              </div>
              <p class="clearfix" v-if="project.place_name">{{project.place_name}}</p>
              <p class="clearfix" v-else></p>
              <p class="fixed-needle" v-if="project.fixedLable">
                <span></span>
                <img src="images/fixed-needle.png">
              </p>
              <p class="fixed-info" v-if="project.num">{{project.num}}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="dashboard-list dashboard-last-one">
        <p class="dashboard-title-position"><span class="dashboard-title">スケジュール</span>
          <a class="float-right-msg" @click="openUrl(3)">一覧を見る</a>
        </p>
       <ul>
         <p class="dashboard-title">本日の予定です</p>
         <div class="dashboard-title" v-for="sch in schedules">
           <p :class="'hovering'+sch.type">
             <ScheduleDetailModel :day="day" :schedule="sch" :editable="editable"
                                  @showHandle="showHandle" @hideHandle="hideHandle">
               <span class="dashboard-allday" v-if="sch.all_day == 1">終&nbsp;&nbsp;日</span>
               <span v-if="sch.all_day == 0"  class="dashboard-allday">{{sch.startTime}}~{{sch.finishTime}}</span>
               <span class="scheduletyle" :style="{backgroundColor:getColor(sch.type),color: '#ffffff'}"
                     v-if="sch.type">{{typeName(sch.type.toString())}}</span>
               <span v-else></span>
               <span>{{sch.subject}}</span>
             </ScheduleDetailModel>
           </p>
         </div>
       </ul>
      </div>
      <div class="dashboard-list dashboard-last-two">
        <p class="dashboard-title-position"><span class="dashboard-title">タスク</span>
          <a class="float-right-msg" @click="openUrl(2)">一覧を見る</a>
        </p>
        <ul>
          <p class="dashboard-title">本日までのタスクです<span class="float-right-msg-err" v-if="getTaskExpired()">期限超過のタスクがあります!</span></p>
          <div class="dashboard-title" v-for="task in tasks">
            <p @click="openUrl(2,task.id)">
              <span class="task-title-inf" v-html="showNote(task.note,task.chatmessages,task.group_id)"></span>
              <span v-if="task.expired"><img class="float-right-msg" src="images/ico_mask_1.png" alt=""></span>
              <span v-else></span>
            </p>
          </div>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
  import UserProfile from "../../components/common/UserProfile";
  import ScheduleLists from "../../mixins/ScheduleLists";
  import Messages from "../../mixins/Messages";
  import Calendar from '../../mixins/Calendar'
  import ScheduleDetailModel from "../../components/schedule/ScheduleDetailModel";
  import emojiData from '../../../data/emoji-data.js';
  import formatChatMsg from '../../mixins/FormatChatMsg.js';
  import DashboardUrl from "../../components/dashboard/DashboardUrl";

  export default {
    components: {
      UserProfile, ScheduleDetailModel,DashboardUrl
    },
    mixins: [Messages, Calendar, ScheduleLists,formatChatMsg],
    name: "dashboardList",
    data() {
      return {
        emojiData: emojiData,
        status: false, //Schedule pop-up window switch
        day: {
          date: '', //今日の日付
        },
        editable: false, // 権限を変更および削除する
        newMsg: [],  //新着
        newMsgArr:[],
        newMsgType: [],  //新着の種類
        dashboardList: [],  //システムお知らせ
        projects: [], //案件
        schedules: [], //スケジュール
        tasks: [],//タスク
        limit:1,
        oldLimit:-1,
        user:{},
        nextLoad:true,
        showTaskFlag:false,
        groupId:0,
      }
    },
    methods: {
      /**
       * name=>(
       *   chat:0,chatProject:1,chatTask:2
       *   スケジュール:3,案件:4,ドキュメント:5,
       *   協力会社:6,職人:7,施主:8
       * )
       * @param name
       * @param id
       */
      showMessage(chatItem) {
          if (chatItem.title) {
              chatItem.title = chatItem.title.replace(/\[icon:101\]/g, `<img src="./images/chat/101.png" width="16px" height="16px">`);
              chatItem.title = chatItem.title.replace(/\[icon:102\]/g, `<img src="./images/chat/102.png" width="16px" height="16px">`);
                  let msg = null;
                  if (chatItem.title.indexOf('[To:') !== -1) {
                      chatItem.title = formatChatMsg.formatToMsg(chatItem.title,chatItem.group_id);
                  }
                  if (chatItem.title.indexOf('[mid:') !== -1) {
                      msg = formatChatMsg.formatReMsg(chatItem.title,chatItem.group_id);
                      return this.emoji(msg);
                  } else {
                      return this.emoji(chatItem.title);
                  }
          }
      },
        showNote(message, fileName, group_id) {
          if (message === null){
            message = '';
          }
                    message = message.replace(/\[icon:101\]/g, `<img src="./images/chat/101.png" width="16px" height="16px">`);
                    message = message.replace(/\[icon:102\]/g, `<img src="./images/chat/102.png" width="16px" height="16px">`);
                     fileName = '';
                     message = formatChatMsg.quote(this.HTMLEncode(message), fileName, group_id, 1);
                    return this.emoji(message);
        },
      //HTMLタグを処理
      HTMLEncode(html) {
          let temp = document.createElement("div");
          (temp.textContent != null) ? (temp.textContent = html) : (temp.innerText = html);
          let output = temp.innerHTML;
          temp = null;
          return output;
      },
      async openUrl(name, id,flag=0,st_datetime,content) {
        if (!flag){
          await this.editDashBoardStatus(id,name);
        }
        switch (name) {
          case 2: //task
            if (id) {
                let errMessage = this.commonMessage.error.dashboardError;
                axios.post("/api/getTask", {id: id})
                    .then(res => {
                        if(res){
                            this.$router.push({
                                path: '/chat', query: {
                                    taskId: id,
                                }
                            });
                        }
                    }).catch(err => {
                    this.$alert(errMessage, {showClose: false});
                });
            } else {
              this.$router.push({
                path: '/chat', query: {
                  taskType: '1',
                }
              });
            }
            break;
          case 0: //chatList
              this.getGroupUser(id,0);
            break;
          case 1: //chatProject
              this.getGroupUser(id,1);
            break;
          case 3: //schedule
            if (id) {
              let errMessage = this.commonMessage.error.dashboardError;
              axios.post("/api/dashboardScheduleCheck", {id: id}).then(res => {
                if (res.data) {
                  this.$router.push({
                    path: '/schedule/week?createDate=', query: {
                      createDate: Calendar.dateFormat(st_datetime,'yyyy/MM/dd')
                    }
                  });
                } else {
                  this.$alert(errMessage, {showClose: false});
                }
              }).catch(err => {
                this.$alert(errMessage, {showClose: false});
              });
            } else {
              //一覧を見る
              this.$router.push({
                path: '/schedule/week'
              });
            }
            break;
          case 4: //project
            this.clearProjectNum(id);
            break;
          case 5: //ドキュメント
              let errMessage = this.commonMessage.error.dashboardError;
              axios.post("/api/setNodeId", {id: content})
                  .then(res => {
                      if(res.data){
                          let isIos=this.isIOS();
                          let isChrome = this.isChrome();
                          if(isIos && !isChrome){ //neither Chrome nor Firefox
                              this.showTaskFlag=true;
                              this.groupId = id;
                          }else{
                              if(id > 0) {
                                  window.open(process.env.MIX_DOC_URL + id);
                              } else{
                                  window.open(process.env.MIX_DOC_URL + 'enterprise/internal');
                              }
                          }
                      }else{
                          this.$alert(errMessage, {showClose: false});
                      }
                  }).catch(err => {
                  this.$alert(errMessage, {showClose: false});
              });
            break;
          case 6: //協力会社
            this.$router.push({
              path: '/contact/invite'
            });
            break;
          case 7: //職人
            this.$router.push({
              path: '/contact/friend'
            });
            break;
          case 8: //customer
              axios.get("/api/getCustomerDetail", {
                  params: {
                      id: id
                  }
              }).then((res) => {
                  this.$router.push({
                    path: '/customer/detail/'+id
                  });
              }).catch(err => {
                  let errMsg = this.commonMessage.error.dashboardError;
                  this.$alert(errMsg, {showClose: false});
              });
            break;
          default:

        }
      },
      isIOS(){
          const u = navigator.userAgent;
          const isiOS =u.match(/\(i[^;]+;( U;)? CPU.+Mac OS/) || u.match(/Mac+/i) || u.match(/(iPad).*OS\s([\d_]+)/i);
          if(isiOS) {
              return true;
          } else {
              return false;
          }
      },
      // check if Chrome or Firefox
      isChrome() {
        const u = navigator.userAgent;
        const isChrome = u.match(/Chrome+/i) || u.match(/CriOS+/i) || u.match(/Firefox+/i);
        if(isChrome) {
          return true;
        } else {
          return false;
        }
      },
      getGroupUser(id,type){
          let _user_info = sessionStorage.getItem('_user_info');
          let user_id = JSON.parse(_user_info).id;
          let headers = {headers: {"Content-Type": "multipart/form-data"}};
          axios.get('/api/getGroupUser', {
              params: {
                  id: id,
              }
          }, headers).then((res) => {
              if (res.data.result === 0) {
                  let user = res.data.params;
                  let jump = false;
                  for (let i = 0; i < user.length; i++) {
                      if (user[i].user_id === user_id) {
                          jump = true;
                          break;
                      }
                  }
                  if(jump){
                      switch (type) {
                          case 0:
                              this.$router.push({
                                  path: "/chat", query: {groupId: id, userId: 0}
                              });
                              break;
                          case 1:
                              this.$router.push({
                                  path: '/chat',query:{proId:id}
                              });
                              break;
                          default:
                              break;
                      }
                  }else{
                      this.$alert(this.commonMessage.error.dashboardError, {showClose: false});
                  }
              }
          }).catch(err => {
              console.warn(err);
              this.$alert(this.commonMessage.error.dashboardError, {showClose: false});
          });
      },
      DashboardUrlBack: function (){
          this.showTaskFlag=false;
          this.groupId = 0;
      },
      getColor(type){
        let color = "#e5e5e5";
        if(type === 1){
          color="#ca6ebc";
        }else if(type === 5){
          color="#8fc31f";
        }else if(type === 2){
          color="#7ecef4";
        }else if(type === 4){
          color="#f8b551";
        }else if(type === 3){
          color="#097c25";
        }else if(type === 6){
          color="#ff5e3c";
        }else if (type === 0) {
          color = "#e5e5e5";
        }
        return color;
      },
      async editDashBoardStatus(id,type){
        let errMessage = this.commonMessage.error.system;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        try {
          await axios.post('/api/editDashBoardStatus',{
            id:id,
            type:type
          });
          loading.close();
        } catch {
          loading.close();
          console.warn('Request error');
        }
      },
      clearProjectNum(id){
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.system;
        let _user_info = sessionStorage.getItem('_user_info');
        let user_id = JSON.parse(_user_info).id;
        axios.post('/api/clearProjectNum', {
          id: id
        }).then((result) => {
            axios.post('/api/getProjectParticipants',{projectId:id}).then((res)=>{
                let jump = false;
                if(res.data.contactArr){
                    for (let i = 0; i < res.data.contactArr.length; i++) {
                        if (res.data.contactArr[i].id === user_id) {
                            jump = true;
                            break;
                        }
                    }

                }
                if(res.data.enterpriseArr){
                    for (let i = 0; i < res.data.enterpriseArr.length; i++) {
                        if (res.data.enterpriseArr[i].id === user_id) {
                            jump = true;
                            break;
                        }
                    }
                }
                if(res.data.participantsArr){
                    for (let i = 0; i < res.data.participantsArr.length; i++) {
                        if (res.data.participantsArr[i].id === user_id) {
                            jump = true;
                            break;
                        }
                    }
                }
                if(res.data.otherPerson){
                    for (let i = 0; i < res.data.otherPerson.length; i++) {
                        if (res.data.otherPerson[i].id === user_id) {
                            jump = true;
                            break;
                        }
                    }
                }
                if(jump && !result.data.result){
                    this.$router.push({
                        path: '/project/show/'+id
                    });
                }else{
                    this.$alert(this.commonMessage.error.dashboardError, {showClose: false});
                }
            }).catch(error => {
                this.$alert(this.commonMessage.error.dashboardError, {showClose: false});
            });
          loading.close();
        }).catch(error => {
          console.warn(error)
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      fixedLabel(id) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.system;
        axios.post('/api/setProjectFixedLabel', {
          id: id
        }).then((res) => {
          if (!res.data.result){
            this.projects.forEach(function (item) {
              if (item.id === id){
                item.fixedLable = !item.fixedLable;
              }
              return item;
            });
          }
          loading.close();
        }).catch(error => {
          console.warn(error)
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      clearNewMsg(){
        this.newMsg=[];  //新着
        this.newMsgArr=[];
        const loading = this.$loading({lock: true, background: 'rgba(0,0,0,0.7)'});
        let errMessage = this.commonMessage.error.system;
        axios.post('/api/clearNewMsg',{
          type:this.newMsgType
        }).then((res) => {
          this.newMsg=res.data.params.data;  //新着
          this.newMsgArr=res.data.params.data;
          //#2654 ダッシュボード】案件の新着を表す数字が「すべて確認済み」をクリックしても消えない
          this.projects=res.data.params.project;
          loading.close();
        }).catch(error => {
          console.warn(error)
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      getTaskExpired() {
        let res = false;
        this.tasks.filter(item => {
          if (item.expired) {
            res = true;
          }
        });
        return res;
      },
      showHandle: function () {
        this.status = true;
      },
      hideHandle: function () {
        this.status = false;
      },
      openProject(type) { //4:順調,6:大幅な遅れ//null:総件数
        this.$router.push({
          path: '/project', query: {
            status: type
          }
        });
      },
      selectItem(name, type) { // newMsg click event
        if (type==='1'||type==='2'){
          name = 'chat';
          type = '0';
        }
        if (this.$refs[name].className === 'select-item') {
          this.$refs[name].className = 'select-item-change';
          this.newMsgType.push(type);
          if (type === '0'){
            this.newMsgType.push('1');
            this.newMsgType.push('2');
          }
        } else {
          this.$refs[name].className = 'select-item';
          let index = this.newMsgType.indexOf(type);
          this.newMsgType.splice(index, 1);
          if (type === '0'){
            let index1 = this.newMsgType.indexOf('1');
            this.newMsgType.splice(index1, 1);
            let index2 = this.newMsgType.indexOf('2');
            this.newMsgType.splice(index2, 1);
          }
        }
        this.limit=1;
        this.oldLimit=-1;
        this.nextLoad = true;
        let errMessage = this.commonMessage.error.system;
        axios.post('/api/getDashboardNewMsg',{
          page:this.limit,
          type:this.newMsgType
        }).then((res) => {
          this.newMsg = [];
          this.newMsgArr = [];
          this.limit++;
          for (let i in res.data.params.data) {
            this.newMsg.push(res.data.params.data[i]);
            this.newMsgArr.push(res.data.params.data[i]);  //バックアップ情報
          }
        }).catch(error => {
          console.warn(error)
          this.$alert(errMessage, {showClose: false});
        });
      },
      fetch() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.system;
        axios.get('/api/getDashboardList', {
          params: {
            page: this.limit,
          }
        }).then((res) => {
          this.user =  res.data.user;
          this.limit++;
          this.day.date = res.data.date;  //今日の日付
          for (let i in res.data.newMsg.data) {
            this.newMsg.push(res.data.newMsg.data[i]);
            this.newMsgArr.push(res.data.newMsg.data[i]);  //バックアップ情報
          }
          this.$nextTick(() => {
            this.checkLazyLoad();
          });
          this.projects = res.data.project;  //案件
          this.schedules = res.data.schedule;  //スケジュール
          this.tasks = res.data.task;  //タスク
          loading.close();
        }).catch(error => {
          console.warn(error);
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },
      checkLazyLoad() {
        let _this = this,
            classArr = document.getElementsByClassName('gimg'),
            home = _this.$refs.home,
            clientHeight = home.clientHeight,
            scrollTop = home.scrollTop;
        let getMsgHeight = 0;
        if (_this.newMsg.length && classArr[_this.newMsg.length-1]){
          getMsgHeight = classArr[_this.newMsg.length-1].offsetTop
          if (clientHeight+scrollTop>=getMsgHeight*0.7 && this.limit > this.oldLimit){
            this.oldLimit = this.limit;
            if (this.nextLoad){
              this.getNewMsg();
            }
          }
        }
      },
      getNewMsg(){
        if (this.newMsg.length >= 50){
          let errMessage = this.commonMessage.error.system;
          axios.post('/api/getDashboardNewMsg',{
            page:this.limit,
            type:this.newMsgType
          }).then((res) => {
            if (res.data.params.data.length === 0){
              this.nextLoad = false;
            }else {
              this.nextLoad = true;
              this.limit++;
            }
            for (let i in res.data.params.data) {
              this.newMsg.push(res.data.params.data[i]);
              this.newMsgArr.push(res.data.params.data[i]);  //バックアップ情報
            }
          }).catch(error => {
            console.warn(error)
            this.$alert(errMessage, {showClose: false});
          });
        }
      },
    },
    created() {
      this.fetch();
    }
  }
</script>

<style scoped>
  .dashboard-title:hover .hovering1 span,
  .hovering1:hover span{
    color:#ca6ebc
  }

  .dashboard-title:hover .hovering2 span,
  .hovering2:hover span{
    color:#7ecef4
  }
  .dashboard-title:hover .hovering3 span,
  .hovering3:hover span{
    color:#097c25
  }
  .dashboard-title:hover .hovering4 span,
  .hovering4:hover span{
    color:#f8b551
  }
  .dashboard-title:hover .hovering5 span,
  .hovering5:hover span{
    color:#8fc31f
  }
  .dashboard-title:hover .hovering6 span,
  .hovering6:hover span{
    color:#ff5e3c
  }
  .dashboard-title:hover .hovering0 span,
  .hovering0:hover span{
    color:#e5e5e5
  }


</style>

<template>
  <div>
    <el-tabs type="card" v-model="activeName" @tab-click="handleClick" class="taskcontent taskcontent-list">
      <!--タスク未完了の画面を表示する-->
      <el-tab-pane label="未完了" name="second">
        <el-radio-group v-model="radioNo" @change="taskType">
          <el-radio :label="2">自分のタスク</el-radio>
          <el-radio :label="1">依頼したタスク</el-radio>
          <el-radio :label="3">すべて</el-radio>
        </el-radio-group>

        <!--検索の場合-->
        <el-tabs :tab-position="tabPosition" class="chatTaskFlow" v-model="tabSearch" v-if="searchTaskWord">
          <el-tab-pane :name="'tabSearch'+chatTask.id" v-for="(chatTask,index) in selectTasksNo" v-bind:key="'chatTask*'+chatTask.id+index">
            <span slot="label">
              <p class="note msline" v-if="chatTask.note" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id,1)"></p>
              <p class="note" v-else style="height: 25px">&nbsp; </p>
              <p><i class="el-icon-time"></i>
                {{dayFormat(chatTask.limit_date)}}
                <span round v-if="!isEmpty(chatTask.users)" class="chatTask-users">
                  {{chatTask.users[0].name}}
                </span>
              </p>
            </span>
            <!--一覧画面の場合-->
            <section class="chatcontent" v-if="taskFlag === 1">
              <ul class="chattask-header">
                <li class="rowfrist">
                  <h3><i class="el-icon-time"></i>
                    {{dayFormat(chatTask.limit_date)}}&nbsp&nbsp&nbsp&nbsp
                  </h3>
                </li>
                <li class="chattask-bearer">
                  担当者：
                  <span v-for="(userName,index) in chatTask.chattaskcharges" :key="index">
                    <span round v-if="!isEmpty(userName.users)">
                      {{userName.users[0].name}}
                    </span>
                  </span>
                </li>
                <li class="chattask-dependent">
                  依頼者：
                  <span round v-if="!isEmpty(chatTask.users)">
                    {{chatTask.users[0].name}}
                  </span>
                </li>
              </ul>
              <ul class="rowcontent">
                <li><p class="noteDetail msline" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id)"></p></li>
              </ul>
              <ul v-if="chatTask.message_id" class="rowbuttom task-finish">
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="editChatTaskList(chatTask)">編集</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">完了にする</el-button>
                <el-button class="buttonright" @click.prevent="leaveToChat(chatTask)">追加時点へ移動</el-button>
              </ul>
              <ul class="rowbuttom" v-else>
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="editChatTaskList(chatTask)">編集</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">完了にする</el-button>
              </ul>
            </section>
          </el-tab-pane>
        </el-tabs>
        <!--検索しないの場合-->
        <el-tabs :tab-position="tabPosition" class="chatTaskFlow" v-model="tabList" v-else>
          <el-tab-pane :name="'tabList'+chatTask.id" v-for="(chatTask,index) in chatTasksNo" v-bind:key="'chatTask*'+chatTask.id+index">
            <span slot="label">
              <p class="note msline" v-if="chatTask.note" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id,1)"></p>
              <p class="note" v-else style="height: 25px">&nbsp; </p>
              <p><i class="el-icon-time"></i>
                {{dayFormat(chatTask.limit_date)}}
                <span round v-if="!isEmpty(chatTask.users)" class="chatTask-users">
                  {{chatTask.users[0].name}}
                </span>
              </p>
            </span>
            <!--一覧画面の場合-->
            <section class="chatcontent" v-if="taskFlag === 1">
              <ul class="chattask-header">
                <li class="rowfrist">
                  <h3><i class="el-icon-time"></i>
                    {{dayFormat(chatTask.limit_date)}}&nbsp&nbsp&nbsp&nbsp
                  </h3>
                </li>
                <li class="chattask-bearer">
                  担当者：
                  <span v-for="(userName,index) in chatTask.chattaskcharges" :key="index">
                    <span round v-if="!isEmpty(userName.users)">
                      {{userName.users[0].name}}
                    </span>
                  </span>
                </li>
                <li class="chattask-dependent">
                  依頼者：
                  <span round v-if="!isEmpty(chatTask.users)">
                    {{chatTask.users[0].name}}
                  </span>
                </li>
              </ul>
              <ul class="rowcontent">
                <li><p class="noteDetail msline" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id)"></p></li>
              </ul>
              <ul v-if="chatTask.message_id" class="rowbuttom task-finish">
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="editChatTaskList(chatTask)">編集</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">完了にする</el-button>
                <el-button class="buttonright" @click.prevent="leaveToChat(chatTask)">追加時点へ移動</el-button>
              </ul>
              <ul class="rowbuttom" v-else>
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="editChatTaskList(chatTask)">編集</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">完了にする</el-button>
              </ul>
            </section>
          </el-tab-pane>
        </el-tabs>
      </el-tab-pane>

      <!--タスク完了の画面を表示する-->
      <el-tab-pane label="完了" name="first">
        <el-radio-group v-model="radioYes" @change="taskType">
          <el-radio :label="5">自分のタスク</el-radio>
          <el-radio :label="4">依頼したタスク</el-radio>
          <el-radio :label="6">すべて</el-radio>
        </el-radio-group>
        <!--検索の場合-->
        <el-tabs :tab-position="tabPosition" class="chatTaskFlow" v-if="searchTaskWord">
          <el-tab-pane v-for="(chatTask,index) in selectTasksYes" v-bind:key="'selectTask*'+chatTask.id+index">
            <span slot="label">
              <p class="note msline" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id)"></p>
              <p><i class="el-icon-date"></i>
                {{dayFormat(chatTask.limit_date)}}
                <span round v-if="!isEmpty(chatTask.users)" class="chatTask-users">
                  {{chatTask.users[0].name}}
                </span>
              </p>
            </span>
            <!--一覧画面の場合-->
            <section class="chatcontent" v-if="taskFlag === 1">
              <ul class="chattask-header">
                <li class="rowfrist">
                  <h3><i class="el-icon-time"></i>
                    {{dayFormat(chatTask.limit_date)}}&nbsp&nbsp&nbsp&nbsp
                  </h3>
                </li>
                <li class="chattask-bearer">
                  担当者：
                  <span v-for="(userName,index) in chatTask.chattaskcharges" :key="index">
                    <span round v-if="!isEmpty(userName.users)">
                      {{userName.users[0].name}}
                    </span>
                  </span>
                </li>
                <li class="chattask-dependent">
                  依頼者：
                  <span round v-if="!isEmpty(chatTask.users)">
                    {{chatTask.users[0].name}}
                  </span>
                </li>
              </ul>
              <ul class="rowcontent">
                <li><p class="noteDetail msline" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id)"></p></li>
              </ul>
              <ul v-if="chatTask.message_id" class="rowbuttom task-finish">
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">未完了にする</el-button>
                <el-button class="buttonright" @click.prevent="leaveToChat(chatTask)">追加時点へ移動</el-button>
              </ul>
              <ul class="rowbuttom" v-else>
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">未完了にする</el-button>
              </ul>
            </section>
          </el-tab-pane>
        </el-tabs>
        <!--検索なしの場合-->
        <el-tabs :tab-position="tabPosition" class="chatTaskFlow" v-model="tabListOK" v-else>
          <el-tab-pane :name="'tabListOK'+chatTask.id" v-for="(chatTask,index) in chatTasksYes" v-bind:key="'selectTask**'+chatTask.id+index">
            <span slot="label">
              <p class="note msline" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id)"></p>
              <p><i class="el-icon-time"></i>
                {{dayFormat(chatTask.limit_date)}}
                <span round v-if="!isEmpty(chatTask.users)" class="chatTask-users">
                  {{chatTask.users[0].name}}
                </span>
              </p>
            </span>
            <!--一覧画面の場合-->
            <section class="chatcontent" v-if="taskFlag === 1">
              <ul class="chattask-header">
                <li class="rowfrist">
                  <h3><i class="el-icon-time"></i>
                    {{dayFormat(chatTask.limit_date)}}&nbsp&nbsp&nbsp&nbsp
                  </h3>
                </li>
                <li class="chattask-bearer">
                  担当者：
                  <span v-for="(userName,index) in chatTask.chattaskcharges" :key="index">
                    <span round v-if="!isEmpty(userName.users)">
                      {{userName.users[0].name}}
                    </span>
                  </span>
                </li>
                <li class="chattask-dependent">
                  依頼者：
                  <span round v-if="!isEmpty(chatTask.users)">
                    {{chatTask.users[0].name}}
                  </span>
                </li>
              </ul>
              <ul class="rowcontent">
                <li><p class="noteDetail msline" v-html="showMessage(chatTask.note,chatTask.chatmessages,chatTask.group_id)"></p></li>
              </ul>
              <ul v-if="chatTask.message_id" class="rowbuttom task-finish">
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">未完了にする</el-button>
                <el-button class="buttonright" @click.prevent="leaveToChat(chatTask)">追加時点へ移動</el-button>
              </ul>
              <ul class="rowbuttom" v-else>
                <el-button class="buttonleft" @click.prevent="deleteChatTaskList(chatTask.id,chatTask.note)">削除</el-button>
                <el-button class="buttonright" @click.prevent="completedTask(chatTask.id,chatTask.note)">未完了にする</el-button>
              </ul>
            </section>
          </el-tab-pane>
        </el-tabs>
      </el-tab-pane>

      <!--新規画面の場合-->
      <section class="chatcontent chattask-creat" v-if="taskFlag === 2">
        <ul class="rowfrist">
          <el-form :model="taskCreateData" :rules="rules" ref="form" class="block">
            <el-row>
              <el-col :span="16">
                <el-form-item label="期限：" prop="limit_date">
                  <el-date-picker class="block" v-model="taskCreateData.limit_date" value-format="yyyy-MM-dd"
                                  prefix-icon="" :clearable="clearable"
                                  onkeypress="if (event.keyCode === 13) return false;">
                  </el-date-picker>
                </el-form-item>
              </el-col>
              <el-col :span="8" style="width:30%;margin-left:4px">
                <el-form-item prop="time_date">
                  <el-time-select
                          class="block" v-model="taskCreateData.time_date" :picker-options="timeObj"
                          :clearable="clearable">
                  </el-time-select>
                </el-form-item>
              </el-col>
            </el-row>
            <el-form-item label="通知" class="myInput" style="margin-top: 10px">
              <el-select class="padding-model schdeulecreat-input" v-model="taskCreateData.notify">
                <el-option v-for="notifyType in notifyTypeColsTmp" :key="notifyType.id" :label="notifyType.name"
                           :value="notifyType.value"></el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </ul>
        <div class="chattask-creat-bearer">
          担当者：
          <span v-for="(userName,index) in taskCreateData.chattaskcharges" :key="index">
            <span round v-if="!isEmpty(userName.users)">
              {{userName.users[0].name}}&nbsp&nbsp&nbsp&nbsp
            </span>
          </span>
          <span v-if="!taskCreateData.chattaskcharges || taskCreateData.chattaskcharges.length === 0">
            <span round>
              {{loginUserName}}&nbsp&nbsp&nbsp&nbsp
            </span>
          </span>
        </div>
        <ul class="rowcontent">
          <el-form :model="taskCreateData" :rules="rules" ref="form" class="block">
            <el-form-item label="内容" prop="note">
              <el-input type="textarea" :rows="8" v-model="taskCreateData.note">
              </el-input>
            </el-form-item>
          </el-form>

        </ul>
        <ul class="rowbuttom">
          <el-button class="buttonleft" @click.prevent="indexTask()">キャンセル</el-button>
          <el-button v-if="editFlag" class="buttonright" @click.prevent="editChatTask(taskCreateData)">タスクを変更する</el-button>
          <el-button v-else class="buttonright" @click.prevent="insertChatTask()">タスクを追加する</el-button>
        </ul>
      </section>
    </el-tabs>
  </div>
</template>

<script>
  import Messages from "../../mixins/Messages";
  import validation from '../../validations/task';
  import ScheduleParticipantsSelectModal from '../../components/schedule/ScheduleParticipantsSelectModal.vue';
  import UserProfile from "../../components/common/UserProfile";
  import Calendar from "../../mixins/Calendar";
  import formatChatMsg from '../../mixins/FormatChatMsg.js'

  window.taskStatus = false;

  export default {
    name: "ChatTask",
    mixins: [
      Messages,
      Calendar,
      validation,
      formatChatMsg,
    ],
    components: {
      ScheduleParticipantsSelectModal,
      UserProfile
    },
    props: [
      'searchTaskWord',
    ],
    data: function () {
      return {
        editFlag:false,
        clearable: false,
        DialogVisible: false,
        current: '',
        imageUrl: '',
        validateMessage: '',
        imgFile: null,
        searchGroupPerson: '',
        inputpopover: '',
        tabPosition: 'left',
        chatMessageSearchWord: '',
        inputtask: '',
        authName: '',
        chatTasks: {},         // チェットタスクデータ
        chatTasksNoSelf: {},       // 未完了 自分 チェットタスク
        chatTasksNoOther: {},       // 未完了 依頼 チェットタスク
        chatTasksNo: {},       // 未完了 チェットタスク
        chatTasksYesSelf: {},      // 完了 自分 チェットタスク
        chatTasksNoAll: {},      // 完了 依頼 チェットタスク
        chatTasksYes: {},      // 完了 チェットタスク
        selectTasksNoSelf: {},       // 未完了 自分 チェットタスク
        selectTasksNoOther: {},       // 未完了 依頼 チェットタスク
        selectTasksNo: {},       // 未完了 チェットタスク
        selectTasksYesSelf: {},      // 完了 自分 チェットタスク
        selectTasksNoAll: {},      // 完了 依頼 チェットタスク
        selectTasksYes: {},      // 完了 チェットタスク
        currentDate: new Date(),// 現在の日付
        taskCreateData: {},     // チェットタスク新規データ
        members: [],            // 担当者
        taskFlag: 1,           // 1:一覧画面；2：新規画面；3：変更画面
        activeName: 'second',
        radioNo: 3,
        radioYes: 6,
        visible: false,
        taskWord: '',
        timer: '',
        searchTask:'',
        taskStatus:window.taskStatus,
        notifyTypeColsTmp: [
          {"name": 'なし', "id": '0', "value": ""},
          {"name": '期限の時刻', "id": '1', "value": "0"},
          {"name": '5分前', "id": '2', "value": "5"},
          {"name": '10分前', "id": '3', "value": "10"},
          {"name": '30分前', "id": '4', "value": "30"},
          {"name": '1時間前', "id": '5', "value": "60"},
          {"name": '前日', "id": '6', "value": "1440"},
        ],
        timeObj: {
          start: '00:00',
          step: '00:05',
          end: '23:55'
        },
        tabListOK:'',
        tabList:'',
        tabSearch:'',
        createTaskIdFlag:'',
        editTaskIdFlag:'',
        loginUserName:'',
      }
    },
    methods: {
      showMessage(message, fileName, group_id) {
        if (message) {
          message = message.replace(/\[icon:101\]/g, `<img src="./images/chat/101.png" width="16px" height="16px">`);
          message = message.replace(/\[icon:102\]/g, `<img src="./images/chat/102.png" width="16px" height="16px">`);
          if (fileName){
            fileName = fileName.file_name;
          }else{
            fileName = '';
          }
          message = formatChatMsg.quote(this.HTMLEncode(message), fileName, group_id, 1);
          return this.emoji(message);
        }
      },
      editChatTaskList(chatTask) {
        this.taskFlag=2;
        this.editFlag=true;
        this.$set(this.taskCreateData,'limit_date',chatTask.limit_date);
        this.$set(this.taskCreateData,'time_date', chatTask.limit_date.substring(11,16));
        this.$set(this.taskCreateData,'note',this.HTMLDecode(chatTask.note));
        this.$set(this.taskCreateData,'id',chatTask.id);
        this.$set(this.taskCreateData,'chatmessages',chatTask.chatmessages);
        this.$set(this.taskCreateData,'chattaskcharges',chatTask.chattaskcharges);
        if(chatTask.notify==0){
          this.$set(this.taskCreateData,'notify','期限の時刻');
        }else if(chatTask.notify==5){
          this.$set(this.taskCreateData,'notify','5分前');
        }else if(chatTask.notify==10){
          this.$set(this.taskCreateData,'notify','10分前');
        }else if(chatTask.notify==30){
          this.$set(this.taskCreateData,'notify','30分前');
        }else if(chatTask.notify==60){
          this.$set(this.taskCreateData,'notify','1時間前');
        }else if(chatTask.notify==1440){
          this.$set(this.taskCreateData,'notify','前日');
        }else{
          this.$set(this.taskCreateData,'notify','なし');
        }
      },
      leaveToChat(chatTask) {
        let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.insert;
        let url = "/api/getPro";
        let data = new FormData();
        data.append('id', chatTask.group_id);
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        axios.post(url, data, headers).then(res => {
          let data = res.data.params.group;
          if (data){
            this.$emit('leaveToChat', {messageId: chatTask.message_id,
              groupId: chatTask.group_id,parentId: data.parent_id,pro: chatTask.group_id});
          } else{
            this.$emit('leaveToChat', {messageId: chatTask.message_id, groupId: chatTask.group_id});
            loading.close();
          }
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },
      closeEnter(e) {
        this.$refs.chatListModel.visible = false;
      },
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj == ""
      },
      dayFormat(day) {
        if (!this.isEmpty(day)) {
          return Calendar.dateFormat(day, 'yyyy年MM月dd日 hh時mm分');
        }
        return '';
      },
      handleClick(tab, event) {
      },

      // 自分、依頼、すべて
      taskType() {
        // 未完了
        if (this.radioNo === 1) {
          this.chatTasksNo = this.chatTasksNoSelf;
          this.selectTasksNo = this.selectTasksNoSelf;
        } else if (this.radioNo === 2) {
          this.chatTasksNo = this.chatTasksNoOther;
          this.selectTasksNo = this.selectTasksNoOther;
        } else if (this.radioNo === 3) {
          this.chatTasksNo = this.chatTasksNoAll;
          this.selectTasksNo = this.selectTasksNoAll;
        }
        // 完了
        if (this.radioYes === 4) {
          this.selectTasksYes = this.selectTasksYesSelf;
          this.chatTasksYes = this.chatTasksYesSelf;
        } else if (this.radioYes === 5) {
          this.selectTasksYes = this.selectTasksYesOther;
          this.chatTasksYes = this.chatTasksYesOther;
        } else if (this.radioYes === 6) {
          this.selectTasksYes = this.selectTasksYesAll;
          this.chatTasksYes = this.chatTasksYesAll;
        }
      },

      // タスク一覧画面
      fetchChatTaskList: function (taskId,editFlag = false) {
        if (this.searchTask && this.searchTask.length > 0) {
          if (taskId) {
            this.searchChatTask(this.searchTask,taskId);
          } else {
            this.searchChatTask(this.searchTask);
          }

          return;
        }
        let type = this.$route.query.taskType
        let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.chatTaskList;
        let url = "/api/getChatTaskList";
        let data = new FormData();
        if (type == 3){
          data.append('type', this.$route.query.taskType);
        }
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        axios.post(url, data, headers).then(res => {
          if (type) {
            this.taskFlag = 0;
          }else{
            this.taskFlag = 1;
          }
          this.chatTasksNoSelf = res.data.chatTaskNoSelf;
          this.chatTasksNoOther = res.data.chatTaskNoOther;
          this.chatTasksNoAll = res.data.chatTaskNoAll;
          if (editFlag === false) {
            this.radioNo = 3;
            this.chatTasksNo = res.data.chatTaskNoAll;
          }
          if (type ===1){
            this.radioNo = 1;
            this.chatTasksNo = res.data.chatTaskNoSelf;
          } else if(type ===2){
            this.radioNo = 2;
            this.chatTasksNo = res.data.chatTaskNoOther;
          } else{
            this.chatTasksNo = res.data.chatTaskNoAll;
          }
          if (editFlag === true) {
            if (this.radioNo === 1) {
              this.chatTasksNo = res.data.chatTaskNoSelf
            } else if(this.radioNo === 2) {
              this.chatTasksNo = res.data.chatTaskNoOther
            } else if(this.radioNo === 3) {
              this.chatTasksNo = res.data.chatTaskNoAll;
            }
          }
          this.chatTasksYesSelf = res.data.chatTaskYesSelf;
          this.chatTasksYesOther = res.data.chatTaskYesOther;
          this.chatTasksYesAll = res.data.chatTaskYesAll;
          this.chatTasksYes = res.data.chatTaskYesAll;
          this.authName = res.data.authName;

          if (taskId) {
            //ダッシュボード 遷移
            //task 完了  判断する
            let finished = false;
            //task 完了  判断する
            for(let i = 0;i < this.chatTasksYesAll.length;i++){
              if(taskId === this.chatTasksYesAll[i].id){
                //完了
                this.activeName = 'first';
                finished = true;
              }
            }
            //task 完了  判断する
            for (let i = 0;i < this.chatTasksNoAll.length;i++) {
              if(taskId === this.chatTasksNoAll[i].id){
                //未完了
                this.activeName = 'second';
                finished = false;
              }
            }

            // 新しいタブを作成して、新しいタブに移動します。
            //task 完了  判断する
            if (finished) {
              //完了
              this.tabListOK = 'tabListOK'+taskId;
            } else {
              //未完了
              this.tabList = 'tabList'+taskId;
            }

            if (editFlag === false) {
              // 新しいタブを作成して、新しいタブに移動します。
              this.createTaskIdFlag = taskId;

              let elTabs = document.getElementsByClassName("el-tabs__nav-scroll");
              let tabListId = '';
              if (finished) {
                //完了
                tabListId = '#tab-tabListOK'+taskId;

                // スクロールバージャンプ  scroll
                $(document).ready(function(){
                  elTabs[6].scrollTop = $(tabListId)[0].offsetTop;
                });
              } else {
                //未完了
                tabListId = '#tab-tabList' + taskId;

                // スクロールバージャンプ  scroll
                $(document).ready(function(){
                  elTabs[5].scrollTop = $(tabListId)[0].offsetTop;
                });
              }
            }
          } else {
            if (this.chatTasksNoAll && this.chatTasksNoAll.length) {
              this.tabList = 'tabList'+ this.chatTasksNoAll[0].id;
            }
          }
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
        loading.close();
      },

      // メッセージ、タスクを検索
      searchChatTask(searchTaskWord = null,taskId) {
        if (searchTaskWord.length < 1) {
          this.fetchChatTaskList();
          return;
        }
        this.searchTask = searchTaskWord;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.chatTaskList;
        axios.get('/api/getChatTaskSearch?q=' + searchTaskWord).then((res) => {
          this.taskFlag = 1;
          this.radioNo = 3;
          this.radioYes = 6;
          this.selectTasksNoSelf = res.data.selectTaskNoSelf;
          this.selectTasksNoOther = res.data.selectTaskNoOther;
          this.selectTasksNoAll = res.data.selectTaskNoAll;
          this.selectTasksNo = res.data.selectTaskNoAll;

          this.selectTasksYesSelf = res.data.selectTaskYesSelf;
          this.selectTasksYesOther = res.data.selectTaskYesOther;
          this.selectTasksYesAll = res.data.selectTaskYesAll;
          this.selectTasksYes = res.data.selectTaskYesAll;
          this.authName = res.data.authName;

          if (taskId) {
            if (this.searchTask) {
              this.tabSearch = 'tabSearch' + taskId;
            } else {
              this.tabList = 'tabList' + taskId;
            }
            if (this.searchTask.indexOf(this.taskCreateData.note) > 0){
              this.createTaskIdFlag = taskId;
            }
          } else {
            if (this.selectTasksNoAll && this.selectTasksNoAll.length) {
              this.tabSearch = 'tabSearch'+ this.selectTasksNoAll[0].id;
            }
          }
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
        loading.close();
      },
      indexTask() {
        this.taskFlag = 1;
      },
      createTask() {
        this.editFlag=false;
        this.taskFlag = 2;
        this.taskCreateData = {};
        this.$set(this.taskCreateData,'limit_date',this.timeDefault()[0]);
        this.$set(this.taskCreateData,'time_date',this.timeDefault()[1]);
        this.$set(this.taskCreateData,'notify','');
        this.$set(this.taskCreateData,'note','');
      },
      editChatTask(taskCreateData){
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if(this.taskCreateData.notify=="期限の時刻"){
              this.$set(this.taskCreateData,'notify','0');
            }else if(this.taskCreateData.notify=="5分前"){
              this.$set(this.taskCreateData,'notify','5');
            }else if(this.taskCreateData.notify=="10分前"){
              this.$set(this.taskCreateData,'notify','10');
            }else if(this.taskCreateData.notify=="30分前"){
              this.$set(this.taskCreateData,'notify','30');
            }else if(this.taskCreateData.notify=="1時間前"){
              this.$set(this.taskCreateData,'notify','30');
            }else if(this.taskCreateData.notify=="前日"){
              this.$set(this.taskCreateData,'notify','1440');
            }else if(this.taskCreateData.notify=="なし"){
              this.$set(this.taskCreateData,'notify','');
            }
            this.taskCreateData.limit_date=this.taskCreateData.limit_date.substring(0,10)+' '+this.taskCreateData.time_date;

            let errMsg = this.commonMessage.error.chatTaskEdit;
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post("/api/editChatTask", {
              taskCreateData: this.taskCreateData,
            }).then(res => {
              this.editTaskIdFlag = this.taskCreateData.id;
              this.taskCreateData={};
              loading.close();
              if (res.data.result > 0) {
                let errorMessage = '';
                for (let i = 0; i < res.data.errors.length; i++) {
                  errorMessage += res.data.errors[i];
                }
                this.$alert(errorMessage, {showClose: false});
              } else {
                this.$alert(this.commonMessage.success.update, {showClose: false});
                this.saved = true;
                this.fetchChatTaskList(this.editTaskIdFlag,true);
              }
            }).catch(error => {
              this.$alert(errMsg, {showClose: false});
              loading.close();
            });
          } else {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              if (isError[0].querySelector('input') !== undefined && isError[0].querySelector('input') !== null) {
                isError[0].querySelector('input').focus();
              } else if (isError[0].querySelector('textarea') !== undefined && isError[0].querySelector('textarea') !== null) {
                isError[0].querySelector('textarea').focus();
              }
            }, 1);
            return false;
          }
        });
      },
      // タスク新規追加
      insertChatTask() {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            if(this.taskCreateData.notify=="期限の時刻"){
              this.$set(this.taskCreateData,'notify','0');
            }else if(this.taskCreateData.notify=="5分前"){
              this.$set(this.taskCreateData,'notify','5');
            }else if(this.taskCreateData.notify=="10分前"){
              this.$set(this.taskCreateData,'notify','10');
            }else if(this.taskCreateData.notify=="30分前"){
              this.$set(this.taskCreateData,'notify','30');
            }else if(this.taskCreateData.notify=="1時間前"){
              this.$set(this.taskCreateData,'notify','30');
            }else if(this.taskCreateData.notify=="前日"){
              this.$set(this.taskCreateData,'notify','1440');
            }else if(this.taskCreateData.notify=="なし"){
              this.$set(this.taskCreateData,'notify','');
            }
            this.taskCreateData.limit_date=this.taskCreateData.limit_date.substring(0,10)+' '+this.taskCreateData.time_date;

            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            axios.post("/api/setChatTask", {
              taskCreateData: this.taskCreateData,
            }).then(res => {
              loading.close();
              if (res.data.result > 0) {
                let errorMessage = '';
                for (let i = 0; i < res.data.errors.length; i++) {
                  errorMessage += res.data.errors[i];
                }
                this.$alert(errorMessage, {showClose: false});
              } else {
                this.$alert(this.commonMessage.success.insert, {showClose: false});
                this.saved = true;
                this.fetchChatTaskList(res.data.task_id);
              }
            }).catch(error => {
              loading.close();
              this.$alert(this.commonMessage.error.insert, {showClose: false});
            });
          } else {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              if (isError[0].querySelector('input') !== undefined && isError[0].querySelector('input') !== null) {
                isError[0].querySelector('input').focus();
              } else if (isError[0].querySelector('textarea') !== undefined && isError[0].querySelector('textarea') !== null) {
                isError[0].querySelector('textarea').focus();
              }
            }, 1);
            return false;
          }
        });
      },

      // タスク 完了/未完了 更新
      completedTask(id,msg) {
        let errMessage = this.commonMessage.error.update;
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post("/api/updateChatTask", {
          id: id,
          msg:msg
        }).then(res => {
          loading.close();
          if (res.data.result > 0) {
            let errorMessage = '';
            for (let i = 0; i < res.data.errors.length; i++) {
              errorMessage += res.data.errors[i];
            }
            this.$alert(errorMessage, {showClose: false});
          } else {
            this.$alert(this.commonMessage.success.update, {showClose: false});
            this.saved = true;
            this.fetchChatTaskList();
          }
        }).catch(error => {
          //完了/未完了 判断する
          if(this.activeName === 'first'){
            //完了の場合
            errMessage = this.commonMessage.error.chatNotCompletedTask;
          }
          if(this.activeName === 'second') {
            //未完了の場合
            errMessage = this.commonMessage.error.chatCompletedTask;
          }
          this.$alert(errMessage, {showClose: false});
          loading.close();
        });
      },

      // タスク削除処理
      deleteChatTaskList: function (id) {
        this.$confirm(this.commonMessage.confirm.delete.task).then(() => {
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          let message = this.commonMessage.success.delete;
          let errMessage = this.commonMessage.error.chatTaskDelete;
          axios.post("/api/deleteChatTask", {
            id: id,
          }).then(res => {
            this.$alert(message, {showClose: false});
            loading.close();
            this.fetchChatTaskList();
          }).catch(error => {
            loading.close();
            this.$alert(errMessage, {showClose: false});
          });
        }).catch(action => {
        });
      },

      //now date get
       dayDefault() {
        var date = new Date();
        var s1 = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
        return s1;
      },
      timeDefault() {
            //タイムスタンプを取得する
            let timestamp =(new Date()).valueOf();
            //現在の時刻を取得する
            let date = new Date();
            let minute=date.getMinutes();
            let add=0;
            //分が追加されました
            if(minute%5>0){
                add=5-minute%5;
            }
          let time = new Date(timestamp);
          //分数を増やす
          time.setMinutes(time.getMinutes()+add);
          let min=time.getMinutes();
          let hour=time.getHours();
          if(min <10){
              min='0'+min;
          }
          if(hour <10){
              hour='0'+hour;
          }
          let arr=[];
          //日付を取得する
          arr[0]=time.getFullYear() + "-" + (time.getMonth() + 1) + "-" + time.getDate();
          //時間をもらう
          arr[1]=hour+ ":" + min;
          return arr;
        },
      checkStatus(){
        if (window.videoChat && !window.rejectFlag) {
          if (window.videoChat.callInFalg && !window.videoChat.group_type) {
            this.$emit('conversion');
            return false;
          }
        }
        if(window.taskStatus){
          window.taskStatus = false;
          //silent update
          let url = "/api/getChatTaskList";
          let data = new FormData();
          let headers = {headers: {"Content-Type": "multipart/form-data"}};
          axios.post(url, data, headers).then(res => {
            this.chatTasksNoSelf = res.data.chatTaskNoSelf;
            this.chatTasksNoOther = res.data.chatTaskNoOther;
            this.chatTasksNoAll = res.data.chatTaskNoAll;
            this.chatTasksNo = res.data.chatTaskNoAll;

            this.chatTasksYesSelf = res.data.chatTaskYesSelf;
            this.chatTasksYesOther = res.data.chatTaskYesOther;
            this.chatTasksYesAll = res.data.chatTaskYesAll;
            this.chatTasksYes = res.data.chatTaskYesAll;
            this.authName = res.data.authName;
            window.taskStatus = false;
          }).catch(error => {
            this.$alert(errMessage, {showClose: false});
          });
        }
      },
      //HTMLタグを処理
      HTMLDecode(text) {
        let temp = document.createElement("div");
        temp.innerHTML = text;
        let output = temp.innerText || temp.textContent;
        temp = null;
        return output;
      },
      //HTMLタグを処理
      HTMLEncode(html) {
        let temp = document.createElement("div");
        (temp.textContent != null) ? (temp.textContent = html) : (temp.innerText = html);
        let output = temp.innerHTML;
        temp = null;
        return output;
      },
    },
    created() {
      let _user_info = sessionStorage.getItem('_user_info');
      let user;
      if (_user_info) {
        user = JSON.parse(_user_info);
        this.loginUserName = user.name;
      }
    },
    mounted() {
      this.timer = setInterval(this.checkStatus, 500);
    },
    beforeDestroy() {
      clearInterval(this.timer);
    },
    watch:{
      activeName(){
        //完了 未完了
        if (this.searchTask && this.searchTask.length) {
          if(this.activeName === 'first' && this.chatTasksYes && this.chatTasksYes.length){
            //完了
            this.tabSearch = 'tabSearch' + this.chatTasksYes[0].id;
          }
          if(this.activeName === 'second' && this.chatTasksNo && this.chatTasksNo.length) {
            //未完了
            this.tabSearch = 'tabSearch' + this.chatTasksNo[0].id;
          }
        } else {
          if(this.activeName === 'first' && this.chatTasksYes && this.chatTasksYes.length){
            //完了
            this.tabListOK = 'tabListOK' + this.chatTasksYes[0].id;
          }
          if(this.activeName === 'second' && this.chatTasksNo && this.chatTasksNo.length) {
            //未完了chatTasksNoAll
            if (this.createTaskIdFlag) {
              //新規
              this.tabList = 'tabList' + this.createTaskIdFlag;
            } else {
              this.tabList = 'tabList' + this.chatTasksNo[0].id;
            }
          }
        }

      },
      radioYes(){
        if (this.searchTask && this.searchTask.length) {
          if (this.radioYes === 4 && this.selectTasksYesSelf && this.selectTasksYesSelf.length) {
            this.tabSearch = 'tabSearch'+this.selectTasksYesSelf[0].id;
          } else if(this.radioYes === 5 && this.selectTasksYesOther && this.selectTasksYesOther.length) {
            this.tabSearch = 'tabSearch'+this.selectTasksYesOther[0].id;
          } else if(this.radioYes === 6 && this.selectTasksYesAll && this.selectTasksYesAll.length) {
            this.tabSearch = 'tabSearch'+this.selectTasksYesAll[0].id;
          }
        } else {
          if (this.radioYes === 4 && this.chatTasksYesSelf && this.chatTasksYesSelf.length) {
            this.tabListOK = 'tabListOK'+this.chatTasksYesSelf[0].id;
          }
          if(this.radioYes === 5 && this.chatTasksYesOther && this.chatTasksYesOther.length) {
            this.tabListOK = 'tabListOK'+this.chatTasksYesOther[0].id;
          }
          if(this.radioYes === 6 && this.chatTasksYesAll && this.chatTasksYesAll.length) {
            this.tabListOK = 'tabListOK'+this.chatTasksYesAll[0].id;
          }
        }
      },
      radioNo(){
        if (this.searchTask && this.searchTask.length) {
          if (this.radioNo === 1 && this.selectTasksNoSelf && this.selectTasksNoSelf.length) {
            this.tabSearch = 'tabSearch'+this.selectTasksNoSelf[0].id;
          } else if(this.radioNo === 2 && this.selectTasksNoOther && this.selectTasksNoOther.length) {
            this.tabSearch = 'tabSearch'+this.selectTasksNoOther[0].id;
          } else if(this.radioNo === 3 && this.selectTasksNoAll && this.selectTasksNoAll.length) {
            // this.tabSearch = 'tabSearch'+this.selectTasksNoAll[0].id;
            if (this.createTaskIdFlag) {
              //新規
              this.tabSearch = 'tabSearch'+this.createTaskIdFlag;
              this.createTaskIdFlag = '';
            } else {
              this.tabSearch = 'tabSearch'+this.selectTasksNoAll[0].id;
            }
          }
        } else {
          if (this.radioNo === 1 && this.chatTasksNoSelf && this.chatTasksNoSelf.length) {
            this.tabList = 'tabList'+this.chatTasksNoSelf[0].id;
          }
          if(this.radioNo === 2 && this.chatTasksNoOther && this.chatTasksNoOther.length) {
            this.tabList = 'tabList'+this.chatTasksNoOther[0].id;
          }
          if(this.radioNo === 3 && this.chatTasksNoAll && this.chatTasksNoAll.length) {
            if (this.createTaskIdFlag) {
              //新規
              this.tabList = 'tabList'+this.createTaskIdFlag;
              this.createTaskIdFlag = '';
            } else {
              this.tabList = 'tabList'+this.chatTasksNoAll[0].id;
            }
          }
        }
      }
    }
  }
</script>
<style>
  .msline{
    white-space: pre-line;
  }
</style>

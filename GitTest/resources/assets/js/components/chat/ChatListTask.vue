<template>
  <transition name="fade">
    <!--modal-->
    <div class="modal wd1 modal-show chatlist-task">
      <div class="modalBody" ref="modalBody" v-bind:style="{'margin-left': '-350px','margin-top': modalTop}">
        <div class="modal-close" @click.prevent="taskCancel">×</div>
        <div class="modalBodycontent" style="overflow: hidden auto">
          <h3>タスク追加</h3>
          <div class="chatlist-task-form">
            <el-input
                    type="textarea"
                    :rows="3"
                    placeholder="タスク内容"
                    v-model="task.taskContent">
            </el-input>
            <div>
              担当者選択：
              <el-popover popper-class="taskuser-search popover-location" placement="bottom" v-model="showTaskUserFlag">
                <el-button slot="reference" @click.prevent="getGroupUsers">選択＋</el-button>
                <div>
                  <el-input class="popoverto-input" placeholder="検索"
                            suffix-icon="searchForm-submit" v-model="groupSearchWord"
                            @change="getGroupUsers"></el-input>
                  <el-tabs type="border-card" class="popoverto-tabs chatlist-task-toChoice">
                    <p v-for="(user,index) in userArrs" class="center-style"
                         @click.prevent="addTaskUser(user)" :key="'addTaskUser'+index">
                      <img :src="'file/users/' + user.user.file" alt="" v-if="user.user.file">
                      <img src="images/icon-chatperson.png" alt="" v-else>
                      {{user.user.name}}
                    </p>
                  </el-tabs>
                </div>
              </el-popover>
            </div>
            <div class="popover-img center-style">
              <img :src="'file/users/' + task.userFile" alt="" v-if="task.userFile">
              <img src="images/icon-chatperson.png" alt="" v-else-if="task.userId">
              {{task.userName}}
            </div>
            <p style="color: red" v-if="errShowFlag">担当者は必須項目です</p>
            <div class="task-datepick rowfrist">
              <el-form ref="form" class="block">
              <el-form-item label="日付指定：" >
              <el-date-picker type="date" v-model="task.taskDate" class="chatlist-task-data block"
                              value-format="yyyy-MM-dd" placeholder="カレンダー" :clearable="clearable"></el-date-picker>
              <el-time-select
                      v-model="task.taskTime" :picker-options="timeObj" class="chatlist-task-data block" :clearable="clearable">
              </el-time-select>
                </el-form-item>
                <el-form-item label="通知：" >
                  <el-select class="padding-model schdeulecreat-input" v-model="task.notify">
                    <el-option v-for="notifyType in notifyTypeColsTmp" :key="notifyType.id" :label="notifyType.name"
                               :value="notifyType.value"></el-option>
                  </el-select>
                </el-form-item>
              </el-form>

            </div>
            <div class="popover-btn">
              <el-button class="chatlist-task-back" @click.prevent="taskCancel">キャンセル</el-button>
              <el-button class="chatlist-task-add" @click.prevent="addTask">タスク追加</el-button>
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
  import messages from "../../mixins/Messages";

  export default {
    mixins: [
      messages,
    ],
    name: 'ChatListTask',
    props: ['taskGroupUser', 'chatClickMessage', 'groupId','chatJumpIdFn'],
    data: function () {
      return {
        clearable: false,
        isMounted: false,
        task: {
          taskContent: '',
          taskDate: '',
          taskTime: '',
          notify:'',
          userId: '',
          groupId: '',
          userName: '',
          userFile: '',
          msgId:'',
        },
        showTaskUserFlag: false,
        groupSearchWord: '',
        errShowFlag: false,
        userArrs: [],
        timeObj: {
          start: '00:00',
          step: '00:05',
          end: '23:55'
        },
        notifyTypeColsTmp: [
            {"name": 'なし', "id": '0', "value": ""},
            {"name": '期限の時刻', "id": '1', "value": "0"},
            {"name": '5分前', "id": '2', "value": "5"},
            {"name": '10分前', "id": '3', "value": "10"},
            {"name": '30分前', "id": '4', "value": "30"},
            {"name": '1時間前', "id": '5', "value": "60"},
            {"name": '前日', "id": '6', "value": "1440"},
        ],
      }
    },
    methods: {
      getGroupUsers() {
        let headers = {headers: {"Content-Type": "multipart/form-data"}};
        axios.get('/api/getGroupUser', {
          params: {
            id: this.groupId,
            words: this.groupSearchWord,
          }
        }, headers).then((res) => {
          if (res.data.result === 0) {
            this.userArrs = res.data.params;
          //  this.groupSearchWord = '';
          }
        }).catch(err => {
        });
      },
      taskCancel() {
        this.errShowFlag = false;
        this.task = {
          taskContent: '',
          taskDate: '',
          userId: '',
          groupId: '',
          userName: '',
          userFile: '',
        };
        this.task.taskContent = this.chatClickMessage;
        this.$emit('taskBack','');
      },
      addTask() {
        if (this.task.userId === '') {
          this.errShowFlag = true;
        } else {
          if (this.task){
            this.task.msgId = this.chatJumpIdFn;
          }
          let errMessage = this.commonMessage.error.chatListTaskCreate;
          this.task.taskDate=this.task.taskDate+' '+this.task.taskTime;
          axios.post('/api/addUserTask', {
            task: this.task,
          }).then((res) => {
            if (res.data.result === 0) {
                this.$emit('taskBack', res.data.params);
              this.task = {
                taskContent: '',
                taskDate: '',
                taskTime: '',
                notify: '',
                userId: '',
                groupId: '',
                userName: '',
                userFile: '',
              };
              this.task.taskContent = this.chatClickMessage;
            }
          }).catch(err => {
            this.$alert(errMessage, {showClose: false});
          });
        }
      },
      addTaskUser(user) {
        this.errShowFlag = false;
        this.showTaskUserFlag = false;
        this.task.groupId = user.group_id;
        this.task.userId = user.user_id;
        this.task.userName = user.user.name;
        this.task.userFile = user.user.file;
      },
      //now date get
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
            //日付と時刻を取得する
            arr[0]=time.getFullYear() + "-" + (time.getMonth() + 1) + "-" + time.getDate();
            //時間をもらう
            arr[1]=hour+ ":" + min;
            return arr;
        },
      dayDefault() {
        var date = new Date();
        var s1 = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
        return s1;
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
    created() {
      this.isMounted = true;
      this.task.taskContent = this.chatClickMessage;
      this.task.taskTime = this.timeDefault()[1];
      this.task.taskDate = this.timeDefault()[0];
    },
    watch:{
      chatClickMessage(val) {
        this.task.taskContent = this.chatClickMessage;
      },
      taskGroupUser(val){
        this.userArrs = this.taskGroupUser;
      }
    }
  }
</script>

<template>
  <el-popover :col="col" :popper-class="'schedule_box s_'+col+'_'+schedule.id" placement="bottom" width="380"
              trigger="click" :visible-arrow="false" @hide="hideStatus" @after-enter="editStatus">
    <div class="schedule-show" @click="hideHandle">
      <a class="scheduleId-del" @click.stop="deleteSchedule" v-if="editable && isEmpty(schedule.pid) && !schedule.invite">
        <img src="images/icon-del.png"/>
      </a>
      <span class="scheduleId-upd" @click.stop="updateSchedule(mineName,type=0)" v-if="editable && isEmpty(schedule.pid) && !schedule.invite" style="cursor: pointer">
        <img src="images/edit@2x.png"/>
      </span>

      <el-popover class="popover-type" placement="bottom" v-model="showType" :visible-arrow="false">
        <el-button @click="processType(1)" size="small">その日のみ</el-button>
        <el-button @click="processType(2)" size="small">その日以後すべて</el-button>
        <el-button @click="processType(3)" size="small">すべて</el-button>
      </el-popover>

      <div class="schedule-popover-header">
        <label  v-if="schedule.type" id="checkbox_label" :class=" 'checkbox_label' + schedule.type">{{typeName(schedule.type.toString())}}</label>
        {{schedule.subject}}</div>
      <div v-if="schedule.all_day&&schedule.repeat_kbn==='0'">
        <p v-if="schedule.st_datetime!==schedule.ed_datetime">{{schedule.st_datetime}}～{{schedule.ed_datetime}}&nbsp&nbsp終日</p>
        <p v-else>{{schedule.st_datetime}}&nbsp&nbsp終日</p>
      </div>
      <div v-else-if="schedule.all_day&&schedule.repeat_kbn!=='0'">
        {{schedule.stDate}}&nbsp&nbsp終日
      </div>
      <div v-else-if="schedule.repeat_kbn==='0'&&toDay(schedule.st_datetime) !== toDay(schedule.ed_datetime)">
       {{timeFormat(schedule.st_datetime)}}{{schedule.st_time}}&nbsp～&nbsp{{timeFormat(schedule.ed_datetime)}}{{schedule.ed_time}}
      </div>
      <div v-else-if="schedule.repeat_kbn!=='0'&&schedule.stDate !== schedule.edDate">
        {{schedule.stDate}}&nbsp&nbsp{{schedule.startTime}}&nbsp～&nbsp{{schedule.edDate}}&nbsp&nbsp{{schedule.finishTime}}
      </div>
      <div v-else>{{dayFormat}}（{{getWeekName}}）{{schedule.startTime}}～{{schedule.finishTime}}</div>
      <div>場所：{{schedule.address}}</div>
      <p>{{schedule.content}}</p>
      <div v-if="schedule.participantUsers"><span class="participant-name">参加者：</span><span
              class="participant-name" v-for="(pu,index) in schedule.participantUsers">
          {{pu.name}}さん <span v-if="index!==schedule.participantUsers.length-1">、</span>
        </span>
      </div>
      <div v-if="schedule.createUser"><span class="participant-name">登録者：</span><span
              class="participant-name">{{schedule.createUser.name}}さん</span> <span
              v-if="schedule.createDate" class="participant-name">{{createDateFormat}}</span>
      </div>
      <div v-if="schedule.updateUser"><span class="participant-name">更新者：</span><span
              class="participant-name">{{schedule.updateUser.name}}さん</span> <span
              v-if="schedule.updateDate" class="participant-name">{{updateDateFormat}}</span>
      </div>
      <p></p>
    </div>
    <div slot="reference">
      <slot></slot>
    </div>
  </el-popover>

</template>

<script>
  import ScheduleLists from '../../mixins/ScheduleLists'
  import Calendar from "../../mixins/Calendar";
  import Messages from "../../mixins/Messages";

  export default {
    name: "ScheduleDetailModel",
    mixins: [ScheduleLists,Calendar, Messages],
    props: {
      editable: {
        Type: Boolean,
        default: true
      },
      day: {
        Type: Object,
        default: () => {
        }
      },
      schedule: {
        Type: Object,
        default: () => {
        }
      },
      mineName: {
        Type: String,
      },
      pageName: {
        Type: String,
      },
      topY: 0,
      col: 0,
      stDate:{
          Type: String,
      },
      edDate:{
          Type: String,
      }
    },
    data() {
      return {
        showType: false,
        isUpdate: false,
        type: 0,
      }
    },
    computed: {
      dayFormat: function () {
        if (this.day) {
          return Calendar.dateFormat(this.day.date, 'yyyy年MM月dd日');
        }
        return '';
      },
      createDateFormat: function () {
        return Calendar.dateFormat(this.schedule.createDate, '（yyyy年MM月dd日 hh:mm）');
      },
      updateDateFormat: function () {
        return Calendar.dateFormat(this.schedule.updateDate, '（yyyy年MM月dd日 hh:mm）');
      },
      getWeekName: function () {
        return Calendar.getDayWeekName(this.day.date);
      },
    },
    methods: {
        timeFormat(time){
            if(time.indexOf(":") !== -1){
                return Calendar.dateFormat(time, 'yyyy/MM/dd hh:mm');
                let date=new Date(time);
                let year=date.getYear() + 1;
                let month=date.getMonth() + 1;
                let day=date.getDate();
                let getHours=date.getHours();
                let getMinutes=date.getMinutes();
                return year+'-'+month+'-'+day+' '+getHours+':'+getMinutes;
            }else{
                return time+' ';
            }
        },
      toDay(time){
          return Calendar.dateFormat(time, 'yyyy/MM/dd');
      },
      editStatus() {
        this.$emit('showHandle');
      },
      hideStatus() {
        this.$emit('hideHandle');
      },
      // スケジュールを削除
      deleteSchedule(type = 0) {
        this.isUpdate = false;
        let isDel = false;
        if (this.schedule.subId) {
          if (type > 0) {
            isDel = true;
          } else {
              if(this.schedule.repeat_kbn > 0){
                  this.showType = !this.showType;
              }else{
                  isDel = true;
              }
          }
        } else {
          this.showType = false;
          isDel = true;
        }

        if (isDel) {
          this.$confirm(this.commonMessage.confirm.delete.scheduleWeek).then(() => {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            let updateDate = Calendar.dateFormat(this.day.date, 'yyyy-MM-dd');
            let subId='';
            if(this.schedule.repeat_kbn > 0){
                subId=this.schedule.subId;
            }
            axios.post("/api/deleteSchScheduleWeek", {
              id: this.schedule.id ? this.schedule.id : this.schedule.scheduleId,
              subId: subId,
              userId: this.schedule.userId,
              updateDate: updateDate,
              updateType: type,
              title: this.schedule.subject,
            }).then(res => {
              this.$emit('reload');
              //loading.close();
            }).catch(error => {
              loading.close();
              this.$alert(this.commonMessage.error.delete, {showClose: false});
            });
          }).catch(action => {
          });
        }
      },
      // 更新スケジュール
      updateSchedule(uName, type = 0) {
        this.isUpdate = true;

        let edit = false;
        if (this.schedule.subId) {
          if (type > 0) {
            edit = true;
          } else {
            if(this.schedule.repeat_kbn > 0){
                this.showType = !this.showType;
            }else{
                edit = true;
            }
          }
        } else {
          this.showType = false;
          edit = true;
        }

        if (edit) {
          let updateDate = Calendar.dateFormat(this.day.date, 'yyyy-MM-dd');
          //check cross-day
          axios.post("/api/checkSchedule", {
            id: this.schedule.id ? this.schedule.id : this.schedule.scheduleId,
            subId: this.schedule.subId,
            updateDate: updateDate,
          }).then(res => {
            this.$router.push({
              path: "/schedule/edit",
              query: {
                id: this.schedule.id ? this.schedule.id : this.schedule.scheduleId,
                updateDate: res.data,
                updateType: type,
                pageName: this.pageName,
              }
            });
          }).catch(error => {
            this.$alert(this.commonMessage.error.update, {showClose: false});
          });

        }
      },
      //詳細ウィンドウが閉じたときに発生します
      hideHandle() {
        this.showType = false;
      },
      //空かどうかを確認
      isEmpty: function (obj) {
        if (obj == 0) {
          return true;
        } else {
          return typeof obj == 'undefined' || obj == null || obj == ""
        }
      },
      processType(type) {
        this.type = type;
        if (this.isUpdate) {
          this.updateSchedule('', type);
        } else {
          this.deleteSchedule(type);
        }
      },
      addNewStyle(newStyle) {
        let styleElement = document.getElementById('styles_js');
        if (!styleElement) {
          styleElement = document.createElement('style');
          styleElement.type = 'text/css';
          styleElement.id = 'styles_js';
          document.getElementsByTagName('head')[0].appendChild(styleElement);
        }
        styleElement.appendChild(document.createTextNode(newStyle));
      },
      getStDate(date){
          let schDate = new Date(date);
          let stDate = new Date(this.stDate);
          if(schDate < stDate){
              return this.stDate;
          }else{
              return date;
          }
      },
      getEdDate(date){
          let schDate = new Date(date);
          let edDate = new Date(this.edDate);
          if(schDate > edDate){
              return this.edDate;
          }else{
              return date;
          }
      },
    },
    mounted() {
      if (this.topY) {
        let classCheck = '.s_' + this.col + '_' + this.schedule.id;
        let minHeight = $(classCheck).height();
        let maxHeight = window.screen.availHeight;
        if (maxHeight - this.topY < minHeight + 180) {
          this.addNewStyle(classCheck + '{top: ' + (maxHeight - minHeight - 180) + 'px !important;}');
        } else {
          this.addNewStyle(classCheck + '{top: ' + this.topY + 'px !important;}');
        }
      }

    },
    watch: {
      topY: function () {
        if (this.topY) {
          let classCheck = '.s_' + this.col + '_' + this.schedule.id;
          let minHeight = $(classCheck).height();
          let maxHeight = window.screen.availHeight;
          if (maxHeight - this.topY < minHeight + 180) {
            this.addNewStyle(classCheck + '{top: ' + (maxHeight - minHeight - 180) + 'px !important;}');
          } else {
            this.addNewStyle(classCheck + '{top: ' + this.topY + 'px !important;}');
          }
        }
      }
    }
  }
</script>

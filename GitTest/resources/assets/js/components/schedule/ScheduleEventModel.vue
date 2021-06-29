<template>
  <div class="schedul-popover-content">

    <a class="add-content" @click="create(dayFn.date,mineId)"></a>
    <div class="calendar-day-sckedule-wrap" v-if="schedules[mineId]">
      <div class="day-scheduled-content clearfix btns"
           v-for="(i,index) in scheduleArr" v-if="scheduleArr&& index < schLength"
           :style="{backgroundColor:getColor(scheduleArr[index].type),top:index*34+'px',
           width:scheduleArr[index].flag?(scheduleArr[index].count*100)+'%':'100%',color:getColorWord(scheduleArr[index].type)}">
        <div v-if="scheduleArr[index].id>0">
          <ScheduleDetailModel :day="dayFn" :schedule="scheduleArr[index]" :editable="isEditable(scheduleArr[index])"
                               @reload="initWeek" :pageName="pageName" @showHandle="showHandle"
                               :mineName="mineName" @hideHandle="hideHandle">
            <span v-if="!scheduleArr[index].index || scheduleArr[index].stDate===firstDayOfWeek">
              <p  v-if="scheduleArr[index].all_day&&scheduleArr[index].repeat_kbn > 0">{{editDate(scheduleArr[index].stDate)}}-{{editDate(scheduleArr[index].stDate)}}&nbsp;</p>
              <p  v-else-if="scheduleArr[index].all_day&&scheduleArr[index].repeat_kbn==='0'">{{editDate(scheduleArr[index].st_datetime)}}-{{editDate(scheduleArr[index].ed_datetime)}}&nbsp;</p>
              <p  v-else-if="(dayFn.date === scheduleArr[index].st_datetime && dayFn.date === scheduleArr[index].ed_datetime)||scheduleArr[index].repeat_kbn > 0">{{scheduleArr[index].st_time}}-{{scheduleArr[index].ed_time}}&nbsp;</p>
              <p  v-else-if="dayFn.date === scheduleArr[index].st_datetime && dayFn.date !== scheduleArr[index].ed_datetime">{{scheduleArr[index].st_time}}-{{editDate(scheduleArr[index].ed_datetime)}}&nbsp;</p>
              <p  v-else-if="dayFn.date !== scheduleArr[index].st_datetime && dayFn.date === scheduleArr[index].ed_datetime">{{editDate(scheduleArr[index].st_datetime)}}-{{scheduleArr[index].ed_time}}&nbsp;</p>
              <p  v-else>{{editDate(scheduleArr[index].st_datetime)}}-{{editDate(scheduleArr[index].ed_datetime)}}&nbsp;</p>
              <p>{{scheduleArr[index].subject}}&nbsp;</p>
            </span>
            <span v-else-if="!scheduleArr[index].index || scheduleArr[index].stDate===firstDayOfWeek">
              <p>{{scheduleArr[index].stDate}}-{{scheduleArr[index].edDate}}&nbsp;</p>
              <p>{{scheduleArr[index].subject}}&nbsp;</p>
            </span>
            <span v-else>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
          </span>
          </ScheduleDetailModel>
        </div>
      </div>
    </div>
    <div class="calendar-day-sckedule-wrap" v-if="schedules[mineId]"
         v-for="(i,index) in schCount">
      <div class="day-scheduled-content clearfix btns"
           v-if="getProInfo(index,day).stDate === day.date && getProInfo(index,day).id<0"
           :style="{backgroundColor:getColor(getProInfo(index,day).type),top:34*index+'px',
           width:getProInfo(index,day).count*100+'%',color:getColorWord(getProInfo(index,day).type)}">
        <div>
          <span>
              <p>{{getProInfo(index,day).stDate}}-{{getProInfo(index,day).edDate}}&nbsp;</p>
              <p>{{getProInfo(index,day).subject}}&nbsp;</p>
            </span>
        </div>
      </div>
    </div>
    <div class=" calendar-schedule-others"  v-for="(i,index) in scheduleArr" v-if="scheduleArr&& index >= schLength"  :style="{top:schLength*34+'px'}">
      <el-popover placement="top" trigger="click" :visible-arrow="false">
        <div class="schedule-others week">{{getWeekName(day.date)}}</div>
        <div class="schedule-others day">{{day.day}}</div>
        <div v-for="(i,index) in scheduleArr" v-if="index > schLength-1"
             :style="{backgroundColor:getColor(scheduleArr[index].type), marginTop:'2px'}">
            <ScheduleDetailModel class="schdeuledetail-other" :day="dayFn" :schedule="scheduleArr[index]" :pageName="pageName"
                                 :editable="isEditable(scheduleArr[index])" @reload="initWeek">
              {{scheduleArr[index].startTime}}-{{scheduleArr[index].finishTime}}&nbsp;{{scheduleArr[index].subject}}
            </ScheduleDetailModel>

        </div>
        <div slot="reference">
          他{{scheduleArr.length - schLength}}件
        </div>
      </el-popover>
    </div>
  </div>
</template>

<script>
  import Calendar from "../../mixins/Calendar";
  import Messages from "../../mixins/Messages";
  import ScheduleDetailModel from "./ScheduleDetailModel";

  export default {
    name: "ScheduleEventModel",
    components: {
      ScheduleDetailModel,
    },
    mixins: [Calendar, Messages],
    props: {
      dayFn: {
        Type: Object,
        default: () => {
        }
      },
      schedulesFn: {
        Type: Object,
        default: () => {
        }
      },
      mineId: {
        Type: String,
      },
      loginUserId: {
        Type: String,
      },
      firstWeekday: {
        Type: String,
      },
      mineName: {
        Type: String,
      },
      status: {
        Type: String,
      },
      schCount: {
        Type: String,
      },
      scheduleArr:{
          Type: Object,
      },
    },
    computed: {
      firstDayOfWeek: function () {
        return Calendar.dateFormat(this.firstWeekday);
      },

    },
    data() {
      return {
        pageName: 'schedule',
        topStyle: '',
        currentDate: new Date(),//現在週の日付
        delDialog: false,
        updateDialog: false,
        day: this.dayFn,
        schedules: this.schedulesFn,
        current: '',
        //フォーマット済み日付
        dateStr: '',
        outerVisible: false,
        createScheduleDate: "",
        scount:0,
        schLength:20,
      }
    },
    methods: {
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
      getColorWord(type){
        let color = "#FFF";
        if(type === 0){
          color="#0F0F0F";
        }else{
          color="#FFF";
        }
        return color;
      },
      getProInfo(num, day) {
        let date = day.date;
        if (!this.schedules[this.mineId][num]) {
          return false;
        }
        if (!this.schedules[this.mineId][num][date]) {
          return false;
        }
        if (!this.schedules[this.mineId][num][date][0].id>0) {
          return false;
        }
        return this.schedules[this.mineId][num][date][0];
      },
      editDate(date){
          return  date.slice(5);
      },
      getSchCount: function (sch, date) {
        let j = 0;
        for (let i = 0; i < sch.length; i++) {
          if (sch[i].stDate === date) {
            j = j + 1;
          }
        }
        return j;
      },
      getWeekName: function (date) {
        return Calendar.getDayWeekName(date);
      },
      isEditable: function (schedule) {
        if (this.mineId === this.loginUserId) {
          return true;
        }
        if (schedule.participantUsers) {
          for (let i in schedule.participantUsers) {
            if (schedule.participantUsers[i].id === this.loginUserId) {
              return true;
            }
          }
        }
        if (schedule.createUser.id===this.loginUserId){
          return true;
        }
        return false;
      },
      //初期化イベント
      initEvent: function () {
        this.schedules = {};
        this.current = '';
      },
      //初期化週間
      initWeek: function () {
        this.initEvent();
        this.$emit('fetch');
      },
      //初期化月
      initMonth: function () {
        this.outerVisible = false;
        this.days = Calendar.getMonthListWithShift(this.monthDate);
      },
      //スケジュールを作成する
      create(date, id) {
        if (this.status) {
          return false;
        }
        //#5175 Question No.1,set createScheduleDate as a date string
        this.createScheduleDate = Calendar.dateFormat(date, 'yyyy-MM-dd');
        this.$router.push({
          path: "/schedule/create", query: {
            createDate: this.createScheduleDate,
            id: id,
            pageName: 'scheduleOrgWeek'
          }
        });
      },
      //ポップアップウィンドウをイベント詳細を発生したとき
      showHandle() {
        this.$emit('showHandle');
      },
      //イベント詳細ウィンドウが閉じたときに発生します
      hideHandle() {
        this.delDialog = false;
        this.updateDialog = false;
        this.$emit('hideHandle');
      },
    },
    mounted() {
      this.dateStr = Calendar.dateFormat(this.dayFn.date);
    },
    watch: {
      schedulesFn: function () {
        this.schedules = this.schedulesFn;
      },
      dayFn: function () {
        this.dateStr = Calendar.dateFormat(this.dayFn.date);
      },
      firstWeekday: function () {
        this.outerVisible = false;
      }
    }
  }
</script>

<style scoped>
  .popover-type .el-button {
    padding: 10px;
  }
</style>

<template>
  <!--container-->
  <div class="container clearfix schedule commonAll">
    <header>
      <h1>
        <router-link to="/schedule/people/month">
          <div class="commonLogo">
            <ul>
              <li class="bold">SCHEDULE</li>
              <li>スケジュール</li>
            </ul>
          </div>
        </router-link>
      </h1>

      <div class="title-wrap week-title">
        <h2>スケジュール（個人・月間）</h2>
        <ul class="header-nav schedule">
          <li class="header-nav-schedule-li">
            <span class="non-current-li"><router-link
                    :to="{path: '/schedule', query: {createDate: emitCurrentDate}}">組織</router-link></span>
            <span class="button-wrap current-li"><router-link
                    :to="{path: '/schedule/week', query: {createDate: emitCurrentDate}}">個人</router-link></span>
          </li>
          <li class="buttons non-currentdata">
            <router-link :to="{path: '/schedule/week', query: {createDate: emitCurrentDate}}">週</router-link>
          </li>
          <li class="buttons currentdata">
            <router-link :to="{path: '/schedule/people/month', query: {createDate: emitCurrentDate}}">月</router-link>
          </li>
        </ul>
      </div>
      <UserProfile/>
    </header>

    <!--schedule-wrapper-->
    <section class="schedule-wrapper  schedule-frist">

      <!--schedule-side-item-->
      <article class="schedule-side-wrap">
        <div class="calendar-mini">
          <div class="calendar-mini-item clearfix">
            <div class="year-month" @click.prevent="openScheduleModal" v-for="(day,index) in days" v-if="index===7">
              <span>{{year}}/&nbsp;</span>
              <span class="year-month-bold">{{month}}</span>
              <i class="el-icon-caret-bottom" style="color: #9dc815;"></i>
            </div>
            <!--<div class="year-month" @click.prevent="openScheduleModal"><span>{{year}}/&nbsp;</span><span class="year-month-bold">{{month}}</span></div>-->

          </div>

          <div class="calendar-mini-wrap clearfix">
            <div class="week-wrap clearfix">
              <div class="sun"></div>
              <div class="mon"></div>
              <div class="tue"></div>
              <div class="wed"></div>
              <div class="thu"></div>
              <div class="fri"></div>
              <div class="sat"></div>
            </div>
            <div class="day-wrap clearfix">
              <div v-for="day in days" :class="{today : day.isToday, 'other-date' : day.type != 'currentMonth', holidayColor : holiday(day.date)}"><a
                      href="javascript:void(0)">{{day.day}}</a></div>
            </div>
          </div>
        </div>
      </article>
      <!--/schedule-side-item-->

      <!--schedule-main-wrap-->
      <section class="schedule-org-main-wrap schedule-org-main-top clearfix floatLeft">

        <div class="calendar">
          <div class="calendar-wrap clearfix">
            <div class="calendar-org-week-wrap-month">
              <div class="header-calendar-nav" style="padding: auto">
                <i class="el-icon-arrow-left" @click="beforeMonth"></i>
                <i class="buttons today" @click="today">今日</i>
                <i class="el-icon-arrow-right" @click="nextMonth"></i>
              </div>
            </div>

            <div class="week-wrap clearfix">
              <div class="sun"></div>
              <div class="mon"></div>
              <div class="tue"></div>
              <div class="wed"></div>
              <div class="thu"></div>
              <div class="fri"></div>
              <div class="sat"></div>
            </div>
            <div class="sch-calendar-person-day">
              <div class="day-wrap calendar-person-day clearfix">
                <div v-for="day in days"
                     :class="{today : day.isToday, nextday : day.type != 'currentMonth', holiday : holiday(day.date)}" :style="{height:getHeight(day.date)+'px'}">
                  <a href="javascript:void(0)" v-if="day.day !==''" @click="create(day.date)">
                    <span class="calendar-date">{{day.day}}</span>
                    <span class="six-week-date">{{sixWeekDay(day.date)}}</span>
                  </a>
                  <div class="calendar-day-sckedule-wrap" v-if="schedules[mineId]">
                    <div class="calendar-sckedule clearfix btns" v-for="(i,index) in schedules[mineId]" v-if="getSchInfo(index,day.date)&&index < schLength"
                         :style="{backgroundColor:getColor(getSchInfo(index,day.date).type),color:getColorWord(getSchInfo(index,day.date).type), top:index*20+'px',margin: '0 5px',
                       width:getSchInfo(index,day.date).flag?getWidth(getSchInfo(index,day.date)):'calc(100% - 10px)'}">
                    <ScheduleDetailModel :day="day" :schedule="getSchInfo(index,day.date)" @reload="fetchSubject"
                                         :pageName="pageName" @showHandle="showHandle" @hideHandle="hideHandle"
                                         :editable="isEditable(getSchInfo(index,day.date))">
                      <span v-if="getSchInfo(index,day.date).all_day!==1" :style="{color:getColorWord(getSchInfo(index,day.date).type)}">{{getSchInfo(index,day.date).startTime}}&nbsp;{{getSchInfo(index,day.date).subject}}</span>
                      <span v-else :style="{color:getColorWord(getSchInfo(index,day.date).type)}">終日&nbsp;{{getSchInfo(index,day.date).subject}}</span>
                    </ScheduleDetailModel>
                  </div>
                  <div class=" calendar-schedule-others" v-for="(i,index) in getCount(day.date)" v-if="getSchInfo(index,day.date)&&index > schLength - 1" :style="{top:schLength*20+'px'}">
                    <el-popover placement="top" trigger="click" :visible-arrow="false">
                      <div class="schedule-others week">{{getWeekName(day.date)}}</div>
                      <div class="schedule-others day">{{day.day}}</div>
                      <div v-for="(i,index) in getCount(day.date)" v-if="index > schLength-1" :style="{backgroundColor:getColor(getSchInfo(index,day.date).type), marginTop:'2px'}">
                        <ScheduleDetailModel class="schdeuledetail-other" :day="day"
                                             :schedule="getSchInfo(index,day.date)" :pageName="pageName" @reload="today">

                          <span v-if="getSchInfo(index,day.date).all_day!==1" :style="{color:getColorWord(getSchInfo(index,day.date).type)}">{{getSchInfo(index,day.date).startTime}}&nbsp;{{getSchInfo(index,day.date).subject}}</span>
                          <span v-else :style="{color:getColorWord(getSchInfo(index,day.date).type)}">終日&nbsp;{{getSchInfo(index,day.date).subject}}</span>
                        </ScheduleDetailModel>
                      </div>
                      <div slot="reference">
                        他{{getCount(day.date) - schLength}}件
                      </div>
                    </el-popover>
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!--/schedule-main-inner-->
    </section>
    <!--/dashubord-schedule-wrapper-->
    <ScheduleModal v-if="showScheduleModal" @setModalMonth="getModalMonth" @closeScheduleModal="closeScheduleModal"
                   :emitCurrentDate="currentDate"></ScheduleModal>
    <div class="invite-form">
      <div class="pagination-center" v-if="msg !== ''">
        {{msg}}
      </div>
    </div>
  </div>
</template>

<script>
  import Calendar from '../../mixins/Calendar'
  import UserProfile from "../../components/common/UserProfile"
  import ScheduleModal from '../../components/schedule/ScheduleModal'
  import Messages from "../../mixins/Messages";
  import ScheduleDetailModel from "../../components/schedule/ScheduleDetailModel";

  export default {
    components: {
      UserProfile,
      ScheduleModal,
      ScheduleDetailModel
    },
    mixins: [Calendar, Messages],
    data() {
      return {
        status: false,
        mineId: 0,
        loginUserId: 0,
        currentDate: new Date(),
        days: [],
        schedules: [],
        schNo: [],
        scheduleMap: {},
        japaneseDate: {},
        showDetailModal: false,//スケジュールのモーダル表示フラグ
        seen: false,
        current: 0,
        date: '',
        showScheduleModal: false,//スケジュールのモーダル表示フラグ
        stDate: new Date(),
        edDate: new Date(),
        createScheduleDate: "",
        pageName: 'schedulePeopleMonth',
        emitCurrentDate: '',
        msg:'',
        schLength:20,
        scheduleSelf: [],
        scheduleSelfCount: [],
      }
    },
    methods: {
      getmsg() {
        if (window.successMsg) {
          this.msg = window.successMsg;
          setTimeout(() => {
            this.msg = '';
            window.successMsg = '';
          }, 3000);
        }
      },
      getCount(data){
        let date = new Date(data);
        let year = date.getFullYear();
        let monthKey = date.getMonth() + 1;
        if(monthKey < 10) {
          monthKey = '0' + monthKey;
        }
        let dayKey = date.getDate();
        if(dayKey < 10) {
          dayKey = '0' + dayKey;
        }
        let key = year+'/'+monthKey+'/'+dayKey;

        let length = 0;
        for (let keyFor in this.scheduleSelfCount){
          if(key === keyFor) {
            length = this.scheduleSelfCount[key];
          }
        }
        return length;
      },
      getHeight(data){
          let date=new Date(data);
          let week = date.getDay();
          let minus = week ? week : 0;
          date.setDate(date.getDate() -minus);
          let year = date.getFullYear();
          let month = date.getMonth() + 1;
          let day = date.getDate();
          let weekArr=[];
          weekArr.push(year+'/'+month+'/'+day);
          for(let i=1;i<7;i++){
              let val=new Date(weekArr[i-1]);
              let value=new Date(val.setDate(val.getDate()+1));
              weekArr.push(value.getFullYear()+'/'+(value.getMonth() + 1)+'/'+value.getDate());
          }
          let length=0;
          let end=0;
          let schLength=0;
          if(this.schedules[this.mineId]){
              schLength=this.schedules[this.mineId].length;
          }
          for(let i=0;i<schLength;i++){
              let schedule=this.schedules[this.mineId][i];
              for(let key in schedule){
                  for(let j=0;j<weekArr.length;j++){
                      let date1 = new Date(key);
                      let date2 = new Date(weekArr[j]);
                      if(date1 - date2 === 0){
                          length++;
                          end=1;
                          break;
                      }
                  }
                  if(end === 1){
                      end=0;
                      break;
                  }
              }
          }
          if(length > this.schLength){
              length = this.schLength+1;
          }
          let height=123;
          height=(length)*22+25;
          if(height<123){
              height=123;
          }
          return height;
      },
      getSchLength(data){
        if(data){
            return data.length;
        }else{
            return 0;
        }
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
      getColorWord(type){
        let color = "#FFF";
        if(type == 0){
          color="#0F0F0F";
        }else{
          color="#FFF";
        }
        return color;
      },
      getWidth(info){
          return 'calc(' + (info.count*100) + '% + ' +(info.count)+ 'px' + ' - 10' + 'px)';
      },
      getSchInfo(num,date){
        if (!this.schedules[this.mineId][num]){
          return false;
        }
        if (!this.schedules[this.mineId][num][this.formatDate(date)]) {
          return false;
        }
        return this.schedules[this.mineId][num][this.formatDate(date)][0];
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
      initMonth: function () {
        this.initEvent();
        this.days = Calendar.getMonthListWithOther(this.currentDate);
      },
      initEvent: function () {
        this.schedules = [];
        this.scheduleMap = {};
      },
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj == ""
      },
      getWeekName: function (date) {
        return Calendar.getDayWeekName(date);
      },
      sortScheduleByStartTime: function (obj1, obj2) {
        return obj1.startTime < obj2.startTime ? -1 : obj1.startTime > obj2.startTime ? 1 : 0;
      },

      fetchSubject: function () {

        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.scheduleList;
        let day = this.currentDate.getFullYear() + '/' + (this.currentDate.getMonth() + 1) + '/' + this.getMonthLast(this.currentDate).getDate();
        let nextMonthFirstDay = Calendar.addDay(day, 1);
        let lastYear = nextMonthFirstDay.getFullYear()
        let lastMonth = nextMonthFirstDay.getMonth()+1
        let lastDay = nextMonthFirstDay.getDate()
        axios.post('/api/getScheduleSubjects', {
          stDate: this.currentDate.getFullYear() + '/' + (this.currentDate.getMonth() + 1) + '/' + this.getMonthFirst(this.currentDate).getDate(),
          edDate: lastYear + '/' + lastMonth + '/' + lastDay
        }).then((res) => {
          this.japaneseDate = res.data.japaneseDate;
          this.schedules = res.data.schedule;
          this.mineId = res.data.userId;
          this.loginUserId = res.data.userId;
          this.scheduleSelf = res.data.scheduleSelf;
          this.scheduleSelfCount = res.data.scheduleSelfCount;
          loading.close();
        }).catch(e => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
      },
      //六曜
      sixWeekDay: function (date) {
        let dt = Calendar.dateFormat(date);
        if (this.japaneseDate && this.japaneseDate[dt]) {
          return this.japaneseDate[dt].six_weekday;
        }
        return "";
      },
      //祝日
      holiday: function (date) {
        let dt = Calendar.dateFormat(date);
        // データ存在
        if (this.japaneseDate && this.japaneseDate[dt]) {
          return this.japaneseDate[dt].holiday;
        }
        return "";
      },
      beforeMonth: function () {
        this.currentDate = Calendar.getOtherMonth(this.currentDate, 'before');
        this.emitCurrentDate = Calendar.dateFormat(this.currentDate, 'yyyy-MM-dd');
        this.initMonth();
        this.fetchSubject();
      },
      nextMonth: function () {
        this.currentDate = Calendar.getOtherMonth(this.currentDate, 'next');
        this.emitCurrentDate = Calendar.dateFormat(this.currentDate, 'yyyy-MM-dd');
        this.initMonth();
        this.fetchSubject();
      },
      openScheduleModal() {
        this.showScheduleModal = true;
      },
      closeScheduleModal() {
        this.showScheduleModal = false;
      },
      getModalMonth(data) {
        this.initEvent();
        this.days = Calendar.getMonthListWithOther(new Date(data));
        this.currentDate = (new Date(data));
        this.fetchSubject();
      },
      today: function () {
        this.currentDate = new Date();
        this.initMonth();
        this.fetchSubject()
      },
      formatDate: function (str) {
        let myDate = new Date(str);
        return Calendar.dateFormat(myDate);
      },
      // 該当月の初日を取得
      getMonthFirst(date) {
        let firstDate = date.setDate(1);
        return new Date(firstDate);
      },
      // 該当月の終日を取得
      getMonthLast(date) {
        let currentMonth = date.getMonth();
        let nextMonth = ++currentMonth;
        let nextMonthFirstDay = new Date(date.getFullYear(), nextMonth, 1);
        let oneDay = 1000 * 60 * 60 * 24;
        return new Date(nextMonthFirstDay - oneDay);
      },
      addMonth(date, add = 1) {
        return new Date(date.getFullYear(), date.getMonth() + add, date.getDate());
      },
      getDay(date) {
        return date instanceof Date ? date.getDay() : date;
      },
      getDate(date) {
        return date instanceof Date ? date.getDate() : date;
      },
      showHandle:function () {
        this.status = true;
      },
      hideHandle:function() {
        this.status = false;
      },
      create(date) {
        if (this.status) {
          return false;
        }
        //#5175 Question No.1,set createScheduleDate as a date string
        this.createScheduleDate = Calendar.dateFormat(date, 'yyyy-MM-dd');
        this.$router.push({
          path: "/schedule/create",
          query: {createDate: this.createScheduleDate, pageName: 'schedulePeopleMonth'}
        });
      },
      isEditable: function (schedule) {
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
    },
    computed: {
      year: function () {
        return Calendar.getYear(this.currentDate)
      },
      month: function () {
        return Calendar.getMonth(this.currentDate)
      },
    },
    created() {
      if (this.$route.query.createDate) {
        this.currentDate = new Date(this.$route.query.createDate)
        this.emitCurrentDate = Calendar.dateFormat(this.$route.query.createDate, 'yyyy-MM-dd');
      }
      this.initMonth();
      this.fetchSubject();
      this.getmsg();
    },
  }
</script>
<style scoped>
  .header-calendar-nav-important {
    float: none !important;
    display: flex !important;
  }

  ::-webkit-scrollbar {
    width: 0;
    height: 110px;
    background-color: #F5F5F5;
  }

</style>

<template>
  <div class="container clearfix schedule schdule-org-week commonAll schdule-week">
    <header>
      <h1>
        <router-link to="/schedule/week">
          <div class="commonLogo">
            <ul>
              <li class="bold">SCHEDULE</li>
              <li>スケジュール</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <div class="title-wrap week-title">
        <h2>スケジュール（個人・週間）</h2>
        <ul class="header-nav schedule">
          <li class="header-nav-schedule-li">
            <span class="non-current-li"><router-link :to="{path: '/schedule', query: {createDate: emitCurrentDate}}">組織</router-link></span>
            <span class="button-wrap current-li"><router-link :to="{path: '/schedule/week', query: {createDate: emitCurrentDate}}">個人</router-link></span>
          </li>
          <li class="buttons currentdata"><router-link :to="{path: '/schedule/week', query: {createDate: emitCurrentDate}}">週</router-link></li>
          <li class="buttons non-currentdata"><router-link :to="{path: '/schedule/people/month', query: {createDate: emitCurrentDate}}">月</router-link></li>
        </ul>
      </div>
      <UserProfile/>
    </header>
    <el-container  class="schedule-wrapper schedule-frist">
      <el-aside width="200px">
        <article class="schedule-side-wrap">
          <div class="calendar-mini">
            <div class="calendar-mini-item clearfix">
              <div class="year-month" @click.prevent="openScheduleModal" v-for="(day,index) in days" v-if="index===7">
                <span>{{formatDate(day.date).substring(0,5)}}</span>
                <span class="year-month-bold">{{parseInt(formatDate(day.date).substring(5,7))}}</span>
                <i class="el-icon-caret-bottom" style="color: #9dc815;"></i>
              </div>
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
                <div v-for="day in days" :class="{today : day.isToday, 'other-date':day.type!='currentMonth', holidayColor : holiday(day.date)}"
                     @click.prevent="curr(day.date)"><a href="javascript:void(0)">{{day.day}}</a></div>
              </div>
            </div>
          </div>
        </article>
      </el-aside>
      <el-container>
        <section class="schedule-org-main-wrap schedule-org-main-top clearfix">
          <div class="schdule-org-week-datalist">
            <div class="calendar-org-week-wrap">
              <div class="header-calendar-nav" style="padding: auto">
                <i class="el-icon-arrow-left" @click="beforeWeek"></i>
                <i class="buttons today" @click="today(1)">今日</i>
                <i class="el-icon-arrow-right" @click="nextWeek"></i>
              </div>
            </div>
            <div class="calendar-org-week-wrap" v-for="day in weekDays" :key="day.day"
                 :class="{today : day.isToday, holiday : holiday(day.date)}">
              <h3><p>{{day.day}}({{getWeekName(day.date)}})</p>
                <span>{{sixWeekDay(day.date)}}</span>
              </h3>
            </div>
          </div>
          <!-- 終日の場合-->
          <div class="day-mine-wrap schdule-org-week-dataevent clearfix" :style="{height:getHeight(scheduleAllDay[mineId])+'px'}">
            <div class="mine-wrap-comment">
              <!--<p class="schedule-week-mine-wrap-comment">終日</p>-->
            </div>
            <div v-for="(day,dayIndex) in weekDays" :key="dayIndex" :class="{today : day.isToday}" :style="{height:getHeight(scheduleAllDay[mineId])+'px'}">
              <div class="schedul-popover-content">
                <a class="add-content" @click="createAllDay(day.date)"></a>
                <div class="calendar-day-sckedule-wrap">
                  <div class="calendar-sckedule clearfix btns" v-for="(i,index) in scheduleAllDay[mineId]" v-if="getSchInfo(index,day.date)&&index < schLength"
                       :style="{backgroundColor:getColor(getSchInfo(index,day.date).type),color:getColorWord(getSchInfo(index,day.date).type), top:index*20+'px',margin: '0 5px',
                       width:getSchInfo(index,day.date).flag?getWidth(getSchInfo(index,day.date)):'calc(100% - 10px)'}">
                    <ScheduleDetailModel :day="day" :schedule="getSchInfo(index,day.date)" @reload="today"
                                         :pageName="pageName" @showHandle="showHandle" @hideHandle="hideHandle"
                                         :editable="isEditable(getSchInfo(index,day.date))">
                      <span v-if="timeFormat(getSchInfo(index,day.date).st_datetime) !== timeFormat(getSchInfo(index,day.date).ed_datetime)" :style="{color:getColorWord(getSchInfo(index,day.date).type),whiteSpace: 'nowrap',textOverflow: 'ellipsis'}">{{timeFormat(getSchInfo(index,day.date).st_datetime)}}~{{timeFormat(getSchInfo(index,day.date).ed_datetime)}}&nbsp&nbsp{{getSchInfo(index,day.date).subject}}</span>
                      <span v-else="timeFormat(getSchInfo(index,day.date).st_datetime) !== timeFormat(getSchInfo(index,day.date).ed_datetime)" :style="{color:getColorWord(getSchInfo(index,day.date).type),whiteSpace: 'nowrap',textOverflow: 'ellipsis'}">{{timeFormat(getSchInfo(index,day.date).st_datetime)}}&nbsp&nbsp{{getSchInfo(index,day.date).subject}}</span>

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
        </section>
        <section class="schedule-wrapper schedule-secont" v-on:mouseleave="mouseleave" :style="{top:getHeight(scheduleAllDay[mineId])+60+'px', height:' calc(100vh - 200px)',position:' absolute',}">
          <section class="schedule-org-main-wrap clearfix">
            <div class="schedule-week-others">
              <div class="calendar-week-wrap">
                <div class="calendar-schedule-week-others"><a></a></div>
              </div>
              <div class="calendar-week-wrap" v-for="(day,dayIndex) in weekDays" :key="dayIndex">
                <div class=" calendar-schedule-others calendar-schedule-week-others" v-if="spliceScheduleArr(schResorted[dayIndex],20).length>0"  :style="{top:schLength2*34+'px'}">
                  <el-popover placement="top" trigger="click" :visible-arrow="false">
                    <div class="schedule-others week">{{getWeekName(day.date)}}</div>
                    <div class="schedule-others day">{{day.day}}</div>
                    <div v-for="(i,index) in allDayScheduleArr(spliceScheduleArr(schResorted[dayIndex],20))"
                         :style="{backgroundColor:getColor(allDayScheduleArr(spliceScheduleArr(schResorted[dayIndex],20))[index].type), marginTop:'2px'}">
                      <ScheduleDetailModel class="schdeuledetail-other" :day="day" :schedule="allDayScheduleArr(spliceScheduleArr(schResorted[dayIndex],20))[index]" :pageName="pageName"
                                           :editable="true" @reload="today">
                        {{allDayScheduleArr(spliceScheduleArr(schResorted[dayIndex],20))[index].startTime}}-{{allDayScheduleArr(spliceScheduleArr(schResorted[dayIndex],20))[index].finishTime}}&nbsp;{{allDayScheduleArr(spliceScheduleArr(schResorted[dayIndex],20))[index].subject}}
                      </ScheduleDetailModel>

                    </div>
                    <div slot="reference">
                      他{{allDayScheduleArr(spliceScheduleArr(schResorted[dayIndex],20)).length}}件
                    </div>
                  </el-popover>
                </div>
                <div class=" calendar-schedule-others calendar-schedule-week-others" v-else :style="{top:schLength2*34+'px'}">
                  他0件
                </div>
              </div>
            </div>
            <div class="calendar-week-wrap" style="width: 12.5%">
              <div class="calendar-time calendar-daytime" v-for="dayTime in dayTimes"
                   v-if="(dayTime.substring(3,5)) === '00'">
                <div class="calendar-daytime-time">{{dayTime}}</div>
                <div class="calendar-daytime-border"></div>
              </div>
            </div>
            <div class="calendar-week-wrap" v-for="(day,dayIndex) in weekDays" :key="dayIndex"
                 :class="{today : day.isToday}">

              <!--列表示-->
              <div class="calendar-time" v-for="(dayTime,index) in dayTimes"
                   :key="index" :id=index+24*dayIndex
                   v-on:mousedown="mousedown(day,dayTime,index,index+24*dayIndex)"
                   v-on:mouseover="mouseover(day,dayTime,index,index+24*dayIndex)"
                   v-on:mouseup="mouseup(index)">　
                <!--スケジュールボックスの表示-->
              </div>

              <WeekScheduleCol v-if="dayIndex === 0 && schResorted[dayIndex]" :schResorted="schResorted[dayIndex]"
                               :status="status" :day="day" @reload="today" :col="0" pageName="scheduleWeek"
                               @showHandle="showHandle" @hideHandle="hideHandle" :loginUserId="loginUserId" :mineId="mineId"></WeekScheduleCol>
              <WeekScheduleCol v-if="dayIndex === 1 && schResorted[dayIndex]" :schResorted="schResorted[dayIndex]"
                               :status="status" :day="day" @reload="today" :col="1" pageName="scheduleWeek"
                               @showHandle="showHandle" @hideHandle="hideHandle" :loginUserId="loginUserId" :mineId="mineId"></WeekScheduleCol>
              <WeekScheduleCol v-if="dayIndex === 2 && schResorted[dayIndex]" :schResorted="schResorted[dayIndex]"
                               :status="status" :day="day" @reload="today" :col="2" pageName="scheduleWeek"
                               @showHandle="showHandle" @hideHandle="hideHandle" :loginUserId="loginUserId" :mineId="mineId"></WeekScheduleCol>
              <WeekScheduleCol v-if="dayIndex === 3 && schResorted[dayIndex]" :schResorted="schResorted[dayIndex]"
                               :status="status" :day="day" @reload="today" :col="3" pageName="scheduleWeek"
                               @showHandle="showHandle" @hideHandle="hideHandle" :loginUserId="loginUserId" :mineId="mineId"></WeekScheduleCol>
              <WeekScheduleCol v-if="dayIndex === 4 && schResorted[dayIndex]" :schResorted="schResorted[dayIndex]"
                               :status="status" :day="day" @reload="today" :col="4" pageName="scheduleWeek"
                               @showHandle="showHandle" @hideHandle="hideHandle" :loginUserId="loginUserId" :mineId="mineId"></WeekScheduleCol>
              <WeekScheduleCol v-if="dayIndex === 5 && schResorted[dayIndex]" :schResorted="schResorted[dayIndex]"
                               :status="status" :day="day" @reload="today" :col="5" pageName="scheduleWeek"
                               @showHandle="showHandle" @hideHandle="hideHandle" :loginUserId="loginUserId" :mineId="mineId"></WeekScheduleCol>
              <WeekScheduleCol v-if="dayIndex === 6 && schResorted[dayIndex]" :schResorted="schResorted[dayIndex]"
                               :status="status" :day="day" @reload="today" :col="6" pageName="scheduleWeek"
                               @showHandle="showHandle" @hideHandle="hideHandle" :loginUserId="loginUserId" :mineId="mineId"></WeekScheduleCol>

            </div>
          </section>
          <ScheduleModal v-if="showScheduleModal" @setModalMonth="getModalMonth"
                         @closeScheduleModal="closeScheduleModal"  :emitCurrentDate="currentDate"></ScheduleModal>
        </section>
      </el-container>
    </el-container>

    <div class="invite-form">
      <div class="pagination-center" v-if="msg !== ''">
        {{msg}}
      </div>
    </div>
  </div>
</template>
<script>
  import Calendar from '../../mixins/Calendar';
  import UserProfile from "../../components/common/UserProfile";
  import ScheduleModal from "../../components/schedule/ScheduleModal";
  import Messages from "../../mixins/Messages";
  import WeekScheduleCol from "../../components/schedule/WeekScheduleCol";
  import ScheduleDetailModel from "../../components/schedule/ScheduleDetailModel";

  export default {
    components: {
      UserProfile,
      ScheduleModal,
      WeekScheduleCol,
      ScheduleDetailModel
    },
    mixins: [
      Calendar,
      Messages],
    data() {
      return {
        status: false,
        delDialog: false,
        updateDialog: false,
        flag: true,
        mineId: 0,
        loginUserId: 0,
        showScheduleModal: false,
        currentDate: new Date(),// 現在週の日付
        monthDate: new Date(),  // カレンダーの日付
        dayTimes: [],            // 時間リスト
        schedules: [],           // スケジュールデータ
        scheduleAllDay:[],
        scheduleMap: {},
        showDetailModal: false,// 詳細モーダル表示フラグ
        postDetail: null,       // 詳細モーダルへ渡すデータ
        days: [],
        japaneseDate: {},
        schResorted: {},
        seen: false,
        current: 0,
        selectFlag: false,
        createDate: {
          st_day: "",
          st_hour: "",
          st_minute: "",
          ed_day: "",
          ed_hour: "",
          ed_minute: "",
          all_day: "",
        },
        startId: '',
        endId: '',
        selectArr: [],
        pageName: 'scheduleWeek',
        emitCurrentDate: '',
        msg:'',
        schLength:20,
        schLength2:20,
      }
    },
    methods: {
      allDayScheduleArr(dataArr){
        let res = [];
        if (dataArr.length > 0) {
          for (let i = 0; i<dataArr.length ; i++) {
            for (let j = 0;j<dataArr[i].length; j++) {
              res.push(dataArr[i][j]);
            }
          }
        }
        return res;
      },

      spliceScheduleArr(dataArr,number){
        if (dataArr&&dataArr.sch_items_sorted.length > number) {
          let res = dataArr.sch_items_sorted.slice(number,dataArr.sch_items_sorted.length);
          return res;
        } else {
          let res = [];
          return res;
        }
      },

      getWidth(info){
        return 'calc(' + (info.count*100) + '% '  + ' - 10px)';
      },
      getHeight(data){
          let height=100;
          if (data) {
              let schLength=data.length;
              if(schLength > this.schLength){
                  schLength = this.schLength + 1;
              }
              height=schLength*20+10;
              if(height<100){
                  height=100;
              }
          }
          return height;
      },
      getSchInfo(num,date){
          if (!this.scheduleAllDay[this.mineId][num]){
              return false;
          }
          if (!this.scheduleAllDay[this.mineId][num][this.formatDate(date)]) {
              return false;
          }
          return  this.scheduleAllDay[this.mineId][num][this.formatDate(date)][0];
      },
      timeFormat(date){
          return  Calendar.dateFormat(date,'MM月dd日');
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
        getCount(data){
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
            if(this.scheduleAllDay[this.mineId]){
                schLength=this.scheduleAllDay[this.mineId].length;
            }
            for(let i=0;i<schLength;i++){
                let schedule=this.scheduleAllDay[this.mineId][i];
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
            return length;
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
      getmsg() {
        if(window.successMsg){
          this.msg=window.successMsg;
          setTimeout(() => {
            this.msg='';
            window.successMsg='';
          }, 3000);
        }
      },
      touchstart(e) {
          let dom = e.currentTarget;
          $(dom).removeClass('schedule-week-top');
          if (e.pageX || e.pageY) {
              this.topY = e.pageY;
          }
      },
      beTop(e) {
          let dom = e.currentTarget;
          $(dom).addClass('schedule-week-top');
      },
      unbeTop(e) {
          let dom = e.currentTarget;
          $(dom).removeClass('schedule-week-top');
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
      showHandle:function () {
        this.status = true;
      },
      hideHandle:function() {
        this.status = false;
      },
      mouseleave() {
        this.selectFlag = false;
        for (let i = 0; i < this.selectArr.length; i++) {
          document.getElementById(this.selectArr[i]).style.background = "white";
        }
      },
      mousedown(day, dayTime, index, id) {
        this.selectFlag = true;
        let timeArr = dayTime.split(":");
        this.createDate.st_day = day.date;
        this.createDate.st_hour = timeArr[0];
        this.createDate.st_minute = timeArr[1];
        this.createDate.ed_day = day.date;
        this.createDate.ed_hour = timeArr[0];
        this.createDate.ed_minute = timeArr[1];
        this.startId = id;
      },
      mouseover(day, dayTime, index, id) {
        if (this.selectFlag) {
          let timeArr = dayTime.split(':');
          this.createDate.ed_day = day.date;
          this.createDate.ed_hour = timeArr[0];
          this.createDate.ed_minute = timeArr[1];
          this.endId = id;
          for (let i = 0; i < this.selectArr.length; i++) {
            document.getElementById(this.selectArr[i]).style.background = "white";
          }
          let startCol = 0;
          let endCol = 0;
          let startRow = 0;
          let endRow = 0;
          if ((new Date(this.createDate.ed_day).getTime() >= new Date(this.createDate.st_day).getTime()) &&
              (parseInt(this.createDate.ed_hour) >= parseInt(this.createDate.st_hour))) {
            startCol = parseInt(this.startId / 24);
            endCol = parseInt(this.endId / 24);
            startRow = this.startId % 24;
            endRow = this.endId % 24;
          } else if ((new Date(this.createDate.ed_day).getTime() < new Date(this.createDate.st_day).getTime()) &&
              (parseInt(this.createDate.ed_hour) > parseInt(this.createDate.st_hour))) {
            startCol = parseInt(this.endId / 24);
            endCol = parseInt(this.startId / 24);
            startRow = this.startId % 24;
            endRow = this.endId % 24;
          } else if ((new Date(this.createDate.ed_day).getTime() <= new Date(this.createDate.st_day).getTime()) &&
              (parseInt(this.createDate.ed_hour) <= parseInt(this.createDate.st_hour))) {
            startCol = parseInt(this.endId / 24);
            endCol = parseInt(this.startId / 24);
            startRow = this.endId % 24;
            endRow = this.startId % 24;
          } else if ((new Date(this.createDate.ed_day).getTime() > new Date(this.createDate.st_day).getTime()) &&
              (parseInt(this.createDate.ed_hour) < parseInt(this.createDate.st_hour))) {
            startCol = parseInt(this.startId / 24);
            endCol = parseInt(this.endId / 24);
            startRow = this.endId % 24;
            endRow = this.startId % 24;
          }
          for (let i = startCol; i <= endCol; i++) {
            for (let j = startRow; j <= endRow; j++) {
              document.getElementById(i * 24 + j).style.background = "cornflowerblue";
              this.selectArr.push(i * 24 + j);
            }
          }
        }
      },
      mouseup(index) {
        if (this.status) {
          this.selectFlag = false;
          return false;
        }
        this.flag = true;
        this.selectFlag = false;
        this.createDate.ed_day = this.createDate.ed_day.replace(/\//g, '-');
        this.createDate.st_day = this.createDate.st_day.replace(/\//g, '-');
        this.clickIndex = index;
        const TIME_COUNT = 1;
        if (!this.timer) {
          this.count = TIME_COUNT;
          this.show = false;
          this.timer = setInterval(() => {
            if (this.count > 0 && this.count <= TIME_COUNT) {
              this.count--;
            } else {
              clearInterval(this.timer);
              this.timer = null;
              if (this.flag) {
                if ((new Date(this.createDate.ed_day).getTime() >= new Date(this.createDate.st_day).getTime()) &&
                    (parseInt(this.createDate.ed_hour) >= parseInt(this.createDate.st_hour))) {
                } else if ((new Date(this.createDate.ed_day).getTime() < new Date(this.createDate.st_day).getTime()) &&
                    (parseInt(this.createDate.ed_hour) > parseInt(this.createDate.st_hour))) {
                  let date = this.createDate.ed_day;
                  this.createDate.ed_day = this.createDate.st_day;
                  this.createDate.st_day = date;
                } else if ((new Date(this.createDate.ed_day).getTime() <= new Date(this.createDate.st_day).getTime()) &&
                    (parseInt(this.createDate.ed_hour) <= parseInt(this.createDate.st_hour))) {
                  let date = this.createDate.ed_day;
                  this.createDate.ed_day = this.createDate.st_day;
                  this.createDate.st_day = date;
                  let time = this.createDate.ed_hour;
                  this.createDate.ed_hour = this.createDate.st_hour;
                  this.createDate.st_hour = time;
                } else if ((new Date(this.createDate.ed_day).getTime() > new Date(this.createDate.st_day).getTime()) &&
                    (parseInt(this.createDate.ed_hour) < parseInt(this.createDate.st_hour))) {
                  let time = this.createDate.ed_hour;
                  this.createDate.ed_hour = this.createDate.st_hour;
                  this.createDate.st_hour = time;
                }
                this.createDate.ed_hour = (Array(2).join('0') + (parseInt(this.createDate.ed_hour) + 1)).slice(-2);
                if(this.createDate.ed_hour == '24'){
                  this.createDate.ed_hour ='23';
                  this.createDate.ed_minute ='55';
                }
                this.$router.push({
                  path: "/schedule/create",
                  query: {
                    //#5175 Question No.1,set createDate as a date string,add parameter transfer time
                    createDate: this.createDate.st_day,
                    stH: this.createDate.st_hour,
                    stM: this.createDate.st_minute,
                    edH: this.createDate.ed_hour,
                    edM: this.createDate.ed_minute,
                    pageName: 'scheduleWeek',
                  }
                });
              }
            }
          }, 100)
        }
      },
      initEvent: function () {
        this.schedules = [];
        this.scheduleMap = {};
        this.scheduleAllDay = [];
      },
      initWeek: function () {
        this.initEvent();
        this.fetch();
      },
      initMonth: function () {
        this.days = Calendar.getMonthListWithOther(this.monthDate);
      },
      beforeMonth: function () {
        this.currentDate = Calendar.getOtherMonth(this.monthDate, 'before');
        this.monthDate = Calendar.getOtherMonth(this.monthDate, 'before');
        this.initMonth();
        this.initWeek();
      },
      nextMonth: function () {
        this.currentDate = Calendar.getOtherMonth(this.monthDate, 'next');
        this.monthDate = Calendar.getOtherMonth(this.monthDate, 'next');
        this.initMonth();
        this.initWeek();
      },
      beforeWeek: function () {
        this.currentDate = Calendar.addDay(this.currentDate, -7);
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      nextWeek: function () {
        this.currentDate = Calendar.addDay(this.currentDate, 7);
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      today(initToday = 0) {
        if (initToday) {
          //今日
          this.currentDate = new Date();
        } else {
          //
        }
        this.monthDate = this.currentDate;

        this.initWeek();
        this.initMonth();
      },
      getWeekName: function (day) {
        return Calendar.getDayWeekName(day);
      },
      processScheduleResult: function (schedules, userId) {
        if (!schedules || schedules.length < 1) {
          return;
        }
        for (let i = 0; i < schedules.length; i++) {
          if (schedules[i].all_day === 1 || (schedules[i].repeat_type == 0 && schedules[i].st_span != schedules[i].ed_span)) {
            let schedule = schedules[i];
            let info = {};
            info.userId = userId;
            info.scheduleId = schedule.id;
            info.subject = schedule.subject;
            info.content = schedule.comment;
            info.address = schedule.location;
            info.duration = schedule.duration;
            info.type = schedule.type;
            info.hidden = false;
            info.color = this.getColor(schedule.type, schedule.repeat_kbn);
            info.subId = schedule.subId;
            info.createDate = schedule.created_at;
            info.createUser = schedule.create_by;
            info.participantUsers = schedule.users;
            info.all_day = schedule.all_day;

            // 年月日を取得する
            info.day = Calendar.dateFormat(schedule.st_datetime);
            info.stDate = info.day;
            info.edDate = Calendar.dateFormat(schedule.ed_datetime);

            // 時分を取得する
            let startTime = '00:00';
            let finishTime = '23:59';
            // 終日ではない場合
            if (schedule.all_day !== 1) {
              startTime = Calendar.dateFormat(schedule.st_datetime, 'hh:mm');
              finishTime = Calendar.dateFormat(schedule.ed_datetime, 'hh:mm')
            }
            info.startTime = startTime;
            info.finishTime = finishTime;

            // 前月のデータ
            let firstDayOfCalendar = Calendar.getFirstDayOfWeek(Calendar.getFirstDayOfMonth(this.currentDate));
            if (info.stDate <= firstDayOfCalendar && info.edDate >= firstDayOfCalendar) {
              info.day = firstDayOfCalendar
            }

            let dayDiff = Calendar.dateDiff(schedule.st_datetime, schedule.ed_datetime);
            if (schedule.schedule_subs && schedule.schedule_subs.length > 0) {
              this.addSubsToScheduleMap(info, schedule.schedule_subs)
            }
            // more days
            else if (dayDiff > 0) {
              this.processMoreDays(info);
            } else {
              this.addToScheduleMap(info);
            }
          }
        }
        this.sortScheduleMap();
      },
      processMoreDays: function (info) {
        let firstDayOfWeek = Calendar.getFirstDayOfWeek(info.stDate);
        let lastDayOfWeek = Calendar.getLastDayOfWeek(info.stDate);
        let stDate = new Date(info.stDate);
        let edDate = new Date(info.edDate);
        // 毎日に区切る
        for (let day = stDate; day <= edDate; day = Calendar.addDay(day, 1)) {
          let dayInfo = JSON.parse(JSON.stringify(info));
          dayInfo.day = Calendar.dateFormat(day);
          dayInfo.hidden = false;
          this.addToScheduleMap(dayInfo);
        }
      },
      addToScheduleMap: function (info) {
        let daysList = this.scheduleMap[info.day];
        if (!daysList) {
          daysList = [];
          this.$set(this.scheduleMap, info.day, daysList);
        }
        daysList.push(info);
      },
      addSubsToScheduleMap: function (sch, subs) {
        for (let i in subs) {
          let sub = subs[i];
          let info = JSON.parse(JSON.stringify(sch));
          info.subId = sub.id;
          info.day = Calendar.dateFormat(sub.s_date);
          info.stDate = info.day;
          info.edDate = info.day;
          this.addToScheduleMap(info);
        }
      },
      sortScheduleMap: function () {
        let $this = this;
        Object.keys(this.scheduleMap).forEach(function (key) {
          $this.scheduleMap[key].sort($this.sortScheduleByStartTime);
        });
      },
      sortScheduleByStartTime: function (obj1, obj2) {
        return obj1.startTime < obj2.startTime ? -1 : obj1.startTime > obj2.startTime ? 1 : 0;
      },
      curr(date) {
        this.currentDate = new Date(date);
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      createAllDay(date) {
        //#5175 Question No.1,set createDate as a date string
        this.$router.push({
          path: "/schedule/create",
          query: {
            createDate: Calendar.dateFormat(date, 'yyyy-MM-dd'),
            pageName: 'scheduleWeek'
          }
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
      getHoliday(date){
        let dt = Calendar.dateFormat(date);
        // データ存在
        if (this.japaneseWeek && this.japaneseWeek[dt]) {
          return this.japaneseWeek[dt].holiday;
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
      // 一覧画面
      fetch: function () {
        let loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMessage = this.commonMessage.error.scheduleList;

        axios.post('/api/indexSchWeekTest', {
          stDate: this.year + '/' + this.weekMonth + '/' + this.firstDay,
          edDate: this.lastYear + '/' + this.lastMonth + '/' + this.nextDay,
        }).then((res) => {
          this.japaneseDate = res.data.japaneseDate;
          this.processScheduleResult(res.data.schedule, res.data.userId);
          this.scheduleAllDay=res.data.scheduleAllDay;
          this.schResorted = res.data.sorted;
          if (!this.mineId) {
            this.mineId = res.data.userId;
            this.loginUserId = res.data.userId;
          }
          loading.close();
        }).catch(error => {
          loading.close();
          this.$alert(errMessage, {showClose: false});
        });
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

      // 空チェック
      isEmpty: function (obj) {
        return typeof obj == "undefined" || obj == null || obj == ""
      },
      // 日付フォーマット　yyyy-MM-dd
      formatDate: function (str) {
        let myDate = new Date(str);
        return Calendar.dateFormat(myDate);
      },
      // 時間線取得
      getHmTimeList: function (startHour, endHour = 24, step = 60) {
        const arr = [];
        let startTime = new Date();
        startTime.setHours(0, 0, 0);
        let endTime = new Date();
        if (endHour === 24) {
          endTime.setHours(23, 59);
        } else {
          endTime.setHours(endHour, 0);
        }
        while (startTime < endTime) {
          let hours = startTime.getHours();
          let minutes = startTime.getMinutes();
          arr.push((hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes));
          startTime.setMinutes(minutes + step);
        }
        return arr;
      },
      //カレンダーポップアップを開く
      openScheduleModal() {
        this.showScheduleModal = true;
      },
      closeScheduleModal() {
        this.showScheduleModal = false;
      },
      getModalMonth(data) {
        this.currentDate = (new Date(data));
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      }
    },
    computed: {
      firstDayOfWeek: function () {
        return Calendar.getFirstDayOfWeek(this.currentDate)
      },
      year: function () {
        return Calendar.getYear(this.firstDayOfWeek)
      },
      month: function () {
        return Calendar.getMonth(this.monthDate)
      },
      weekMonth: function () {
        return Calendar.getMonth(this.firstDayOfWeek)
      },
      firstDay: function () {
        return this.firstDayOfWeek.getDate();
      },
      lastYear: function () {
        return Calendar.addDay(this.firstDayOfWeek, 6).getFullYear();
      },
      lastMonth: function () {
        return Calendar.addDay(this.firstDayOfWeek, 6).getMonth() + 1;
      },
      lastDay: function () {
        return Calendar.addDay(this.firstDayOfWeek, 6).getDate();
      },
      weekDays: function () {
        return Calendar.getWeekList(this.currentDate);
      },
      nextDay:function(){
        return Calendar.addDay(this.firstDayOfWeek, 6).getDate() ;
      },
    },
    created() {
      if(this.$route.query.createDate){
        this.currentDate = new Date(this.$route.query.createDate);
        this.monthDate = new Date(this.$route.query.createDate);
      }
      this.dayTimes = this.getHmTimeList(0);
      this.initMonth();
      this.fetch();
      this.getmsg();
    },
    watch:{
      currentDate: function () {
        this.emitCurrentDate = Calendar.dateFormat(this.currentDate,'yyyy-MM-dd');
      }
    }
  }
</script>

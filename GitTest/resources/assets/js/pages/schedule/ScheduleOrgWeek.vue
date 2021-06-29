<template>
  <div class="container clearfix schedule schdule-org-week commonAll">
    <header>
      <h1>
        <router-link to="/schedule">
          <div class="commonLogo">
            <ul>
              <li class="bold">SCHEDULE</li>
              <li>スケジュール</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <!-- 機能タイトルヘッダー -->
      <div class="title-wrap week-title">
        <h2>スケジュール（組織・週間）</h2>
        <el-form :model="project" label-width="300px" class="title-form">
          <el-form-item cenctype="multipart/form-data">
            <el-select v-model="project.progress_status" style="width: 300px;" class='schedule-project' @change="fetch_select()"
                       placeholder="案件名" id="select">
              <el-option label="すべて表示" value=" "  ></el-option>
              <el-option label="社内" value="-1" ></el-option>
              <el-option label="協力会社" value="-2"></el-option>
              <el-option v-for="progress in caseList" :key="progress.id" :label="progress.place_name"
                         :value="progress.id" :data-users="progress.users"  style="width: 300px;"></el-option>
            </el-select>
          </el-form-item>

        </el-form>
        <ul class="header-nav schedule" style="min-width:680px;">
          <li class="buttons non-currentdata" style="float: right;min-width:120px;"><router-link :to="{path: '/schedule/scheduleDay', query: {createDate: emitCurrentDate}}">日</router-link></li>
          <li class="buttons currentdata" style="float: right;"><router-link :to="{path: '/schedule', query: {createDate: emitCurrentDate}}">週</router-link></li>
          <li class="header-nav-schedule-li" style="float: right;">
            <span class="current-li"><router-link to="/schedule">組織</router-link></span>
            <span class="button-wrap non-current-li"><router-link :to="{path: '/schedule/week', query: {createDate: emitCurrentDate}}">個人</router-link></span>
          </li>
          <li style="width:200px;float: right">案件関係期間表示
            <span>
              <el-switch class="switch-on-off" v-model="showProjectSchedule" active-color="#13ce66"
                         inactive-color="#c6c6c6"
                         active-text="ON" inactive-text="OFF"></el-switch>
            </span>
          </li>
        </ul>
      </div>
      <UserProfile :isSchedule=isSchedule @fetch="fetch"/>
    </header>

    <el-container  class="schedule-wrapper schedule-frist">
      <el-aside width="200px" class="schedule-side-wrap">
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
      </el-aside>
      <el-container>
        <section class="schedule-org-main-wrap schedule-org-main-top clearfix">
          <div class="schedule-frist-top">
            <div class="calendar-org-week-wrap">
              <div class="header-calendar-nav" style="padding: auto">
                <i class="el-icon-arrow-left" @click="beforeWeek"></i>
                <i class="buttons today" @click="today">今日</i>
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
          <section class="schedule-wrapper">
            <div class="schedule-frist-section" >
              <div class="schedule-org-calendar day-mine-wrap clearfix">
                <div class="mine-wrap-comment" :style="{height:getHeight(schedules[mineId])+'px'}">
                  {{mineName}}
                </div>
                <div v-for="day in weekDays" v-if="showNormalSch" :style="{height:getHeight(schedules[mineId])+'px'}">
                  <ScheduleEventModel :dayFn="day" :schedulesFn="schedules" :mineId="mineId" @fetch="fetch" :status="status"
                                      :loginUserId="mineId" @showHandle="showHandle" @hideHandle="hideHandle"
                                      :firstWeekday="weekDays[0].date" :mineName="mineName" :scheduleArr="scheduleArr(schedules[mineId],day.date)"/>
                </div>
                <div class="mine-wrap-com" v-if="proSch[mineId] && showProjectSchedule"
                     :style="{height:(Object.keys(proSch[mineId]).length)*34+2+'px'}">
                </div>
                <div  class="mine-wrap-com-content" :style="{borderTop: '1px solid #a9a9ab',height:
              (Object.keys(proSch[mineId]).length)*34+2+'px'}" v-for="day in weekDays" v-if="proSch[mineId]  && showProjectSchedule">
                  <ScheduleEventModel :dayFn="day" :schedulesFn="proSch" :mineId="mineId" @fetch="fetch" :status="status"
                                      :loginUserId="mineId" @showHandle="showHandle" @hideHandle="hideHandle"
                                      :schCount="Object.keys(proSch[mineId]).length"
                                      :firstWeekday="weekDays[0].date" :mineName="mineName"/>
                </div>
              </div>
            </div>
            <section class=" schedule-secont schedule-org-main-wrap clearfix">

              <div class="schedule-org-calendar day-mine-wrap clearfix" v-for="item in userNames" v-if="mineId!==item.id">
                <div class="mine-wrap-comment"  :style="{height:getHeight(schedules[item.id])+'px',borderBottom:proSch[item.id]&&showProjectSchedule?'0px':'1px solid #A9A9AB'}">
                  {{item.name}}
                </div>
                <div v-for="day in weekDays" class="mine-wrap" v-if="showNormalSch" :style="{height:getHeight(schedules[item.id])+'px'}">
                  <ScheduleEventModel :dayFn="day" :schedulesFn="schedules" :mineId="item.id" @fetch="fetch" :status="status" :scheduleArr="scheduleArr(schedules[item.id],day.date)"
                                      :loginUserId="mineId" @showHandle="showHandle" @hideHandle="hideHandle"
                                      :firstWeekday="weekDays[0].date" :mineName="item.name"/>
                </div>
                <div class="mine-wrap-comment" v-if="proSch[item.id] && showProjectSchedule"
                     :style="{height:(Object.keys(proSch[item.id]).length)*34+2+'px'}">

                </div>
                <div v-for="day in weekDays" class="mine-wrap mine-wrap-com" v-if="proSch[item.id] && showProjectSchedule"
                     :style="{height:(Object.keys(proSch[item.id]).length)*34+2+'px'}">
                  <ScheduleEventModel :dayFn="day" :schedulesFn="proSch" :mineId="item.id" @fetch="fetch" :status="status"
                                      :loginUserId="mineId" @showHandle="showHandle" @hideHandle="hideHandle"
                                      :schCount="Object.keys(proSch[item.id]).length"
                                      :firstWeekday="weekDays[0].date" :mineName="item.name"/>
                </div>
              </div>
            </section>

            <ScheduleModal v-if="showScheduleModal" @setModalMonth="getModalMonth"
                           @closeScheduleModal="closeScheduleModal" :emitCurrentDate="currentDate"></ScheduleModal>
          </section>
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
  import Calendar from '../../mixins/Calendar'
  import UserProfile from "../../components/common/UserProfile";
  import ScheduleModal from "../../components/schedule/ScheduleModal";
  import Messages from "../../mixins/Messages";
  import ScheduleEventModel from "../../components/schedule/ScheduleEventModel"

  export default {
    components: {
      UserProfile,
      ScheduleModal,
      ScheduleEventModel,
    },
    mixins: [
      Calendar,
      Messages],
    data() {
      return {
        isSchedule:true,
        showNormalSch:false,
        proSch:{},
        showProjectSchedule: true,
        showScheduleModal: false,
        userNames: [],
        japaneseWeek: {},
        mineId: '',
        mineName: '',
        currentDate: new Date(),//現在週の日付
        monthDate: new Date(),  //カレンダーの日付
        dayTimes: [],            //時間リスト
        schedules: {},           //スケジュールデータ
        schedulesWithProject: {},//スケジュールデータ
        schedulesWithoutProject: {},//スケジュールデータ
        scheduleStr: '{}',           //スケジュールデータ
        showDetailModal: false,//詳細モーダル表示フラグ
        days: [],
        emitCurrentDate:'',
        msg:'',
        status:false,
        project: {
          place_name: '',
          progress_status: ' '
        },
        caseList: [],
        schLength:20,
      }
    },
    watch:{
      currentDate: function () {
        this.emitCurrentDate = Calendar.dateFormat(this.currentDate,'yyyy-MM-dd');
      }
    },
    methods: {
      scheduleArr(data,day){
          let arr=[];
          if(data){
              for(let i=0;i<data.length;i++){
                  if (data[i]&&data[i][day]&&data[i][day][0].id>0) {
                      if((data[i][day][0].repeat_kbn ==='0')&&(day !== data[i][day][0].st_datetime)){
                          data[i][day][0].startTime='00:00';
                      }
                      let date=new Date(this.year + '/' + this.weekMonth + '/' + this.firstDay+' '+data[i][day][0].startTime);
                      data[i][day][0].time=date.getTime();

                       arr.push(data[i][day][0]);
                      }
                  }
              arr.sort(this.compare('time'));
          }
          return arr;
      },
      compare(key){
        return function(a,b){
            let value1 = a[key];
            let value2 = b[key];
            return value1 - value2;
        }
      },
      topHeight(idArr,showProjectSchedule){
        if (idArr&&showProjectSchedule&&idArr.length<=3){
          return 300+idArr.length*34+'px';
        } else if(idArr&&showProjectSchedule&&idArr.length>3){
          return 300+3*34+'px';
        } else {
          return '300px';
        }
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
      getHeight(data){
          let height=100;
          if (data) {
              let schLength=data.length;
              if(schLength > this.schLength){
                  schLength = this.schLength + 1;
              }
              height=schLength*34+10;
              if(height<100){
                  height=100;
              }
          }
          return height;
      },
      showHandle:function () {
        this.status = true;
      },
      hideHandle:function() {
        this.status = false;
      },
      //初期化イベント
      initEvent: function () {
        this.schedules = {};
        this.proSch={};
        this.showNormalSch = false;
      },
      //初期化週間
      initWeek: function () {
        this.initEvent();
        this.fetch_select();
      },
      //初期化月
      initMonth: function () {
        this.days = Calendar.getMonthListWithOther(this.monthDate);
      },
      //先週
      beforeWeek: function () {
        this.currentDate = Calendar.addDay(this.currentDate, -7);
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      //来週
      nextWeek: function () {
        this.currentDate = Calendar.addDay(this.currentDate, 7);
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      //今日
      today: function () {
        this.currentDate = new Date();
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      //カレンダーの日付を選択
      curr(date) {
        this.currentDate = new Date(date)
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      //カレンダーポップアップを閉じる
      closeDetailModal() {
        this.showDetailModal = false;
      },
      getColor: function (type, repeat_kbn) {
        let color = '#48C279';
        if (repeat_kbn !== '0') {
          color = '#30A5E9';
        } else if (type === 7) {
          color = '#E546BB';
        }
        return color;
      },
      //スケジュール情報を受け入れる
      fetch: function () {
        let postUrl='/api/getScheduleListSelect';
        let progress_status=sessionStorage.getItem('progress_status');
        if(progress_status == null||progress_status == " "){
          postUrl='/api/getScheduleList';
        }
        this.schedulesWithProject = {};
        this.schedulesWithoutProject = {};
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.scheduleList;
        axios.post(postUrl, {
          stDate: this.year + '/' + this.weekMonth + '/' + this.firstDay,
          edDate: this.lastYear + '/' + this.lastMonth + '/' + this.nextDay,
          progress_status: progress_status
        }).then((res) => {
          if (!this.mineId) {
            this.mineId = res.data.userId;
          }
          if (!this.mineName) {
            this.mineName = res.data.userName;
          }
          this.userNames = res.data.user;
          this.japaneseWeek = res.data.japaneseWeek;

          this.schedules = res.data.schedule;
          this.showNormalSch = true;
          this.proSch=res.data.proSch;
          loading.close();
        }).catch(e => {
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
      //スケジュール情報を受け入れる
      fetch_select: function () {
        $(document).ready(function(){
          let select = document.getElementById("select");
          select.selectionStart=0;
          select.selectionEnd=0;
        });
        sessionStorage.setItem('progress_status',this.project.progress_status);
        this.fetch();
      },
      //ケースリストクエリ
      caseListMethod: async function () {
        //非同期処理
        return new Promise((resolve, reject) => {
          this.caseList = {};
          const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
          let errMsg = this.commonMessage.error.scheduleList;
          axios.post('/api/getCaseList').then((res) => {
            this.caseList = res.data.params;
            loading.close();
            resolve();
          }).catch(e => {
            loading.close();
            this.$alert(errMsg, {showClose: false});
            console.warn(e)
            reject()
          });
        });
      },

      //六曜
      sixWeekDay: function (date) {
        let dt = Calendar.dateFormat(date);
        if (this.japaneseWeek && this.japaneseWeek[dt]) {
          return this.japaneseWeek[dt].six_weekday;
        }
        return "";
      },
      //祝日
      holiday: function (date) {
        let dt = Calendar.dateFormat(date);
        // データ存在
        if (this.japaneseWeek && this.japaneseWeek[dt]) {
          return this.japaneseWeek[dt].holiday;
        }
        return "";
      },
      //フォーマット済み日付
      formatDate: function (str) {
        let myDate = new Date(str);
        return Calendar.dateFormat(myDate);
      },


      //カレンダーポップアップを開く
      openScheduleModal() {
        this.showScheduleModal = true;
      },
      //カレンダーポップアップを閉じる
      closeScheduleModal() {
        this.showScheduleModal = false;
      },
      //カレンダーポップアップの時間を受け入れる
      getModalMonth(data) {
        this.currentDate = (new Date(data));
        this.monthDate = this.currentDate;
        this.initWeek();
        this.initMonth();
      },
      getWeekName: function (day) {
        return Calendar.getDayWeekName(day);
      },
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
        return Calendar.addDay(this.firstDayOfWeek, 7).getMonth() + 1;
      },
      lastDay: function () {
        return Calendar.addDay(this.firstDayOfWeek, 6).getDate() ;
      },
      nextDay:function(){
        return Calendar.addDay(this.firstDayOfWeek, 7).getDate() ;
      },
      weekDays: function () {
        let currentDate = Calendar.getWeekList(this.currentDate);
        let current = [];
        for (let i = 0; i < currentDate.length; i++) {
          // currentDate[i]['date'] = currentDate[i]['date'].pattern("yyyy/MM/dd");
          let str=currentDate[i]['date'].split('/');
          if(str[1].length<2){
            str[1]='0'+str[1]
          }if(str[2].length<2){
            str[2]='0'+str[2]
          }
          currentDate[i]['date']=str[0]+'/'+str[1]+'/'+str[2];
          current.push(currentDate[i]);
        }
        return current;
      },
    },
    created() {
      if (this.$route.query.createDate) {
        this.currentDate = new Date(this.$route.query.createDate);
        this.monthDate=this.currentDate;
        this.emitCurrentDate = Calendar.dateFormat(this.$route.query.createDate,'yyyy-MM-dd');
      }
      this.fetch();
      this.initMonth();
      this.getmsg();
      this.caseListMethod().then(res=>{
        let progress_status=sessionStorage.getItem('progress_status');
        if(progress_status>0){
          progress_status=parseInt(progress_status);
        }
        if(progress_status!=null){
          this.project.progress_status=progress_status;
        }else{
          this.project.progress_status=' ';
        }
      }).catch(e => {
        console.warn(e);
      });
    }
  }
</script>

<style scoped>
  .schedule-show-text p {
    color: #fff;
  }
</style>

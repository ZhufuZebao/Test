<template>
  <!--container-->
  <div class="container clearfix schedule">
    <header>
      <h1>
        <a href="#/schedule/week">スケジュール</a>
        <div class="commonLogo">
          <ul>
            <li class="bold">SCHEDULE</li>
            <li>スケジュール</li>
          </ul>
        </div>
      </h1>
      <ul class="header-nav schedule">
        <li class="current"><a href="javascript:void(0)">月</a></li>
        <li><router-link to="/schedule/week">週</router-link></li>
        <li><a href="javascript:void(0)">日</a></li>
        <li><router-link to="/schedule/create">予定追加</router-link></li>
      </ul>
      <div class="title-wrap">
        <h2>{{year}}年<span>{{month}}</span>月</h2>
        <div class="header-calendar-nav">
          <span class="before" @click="beforeMonth">前月</span>
          <span class="next" @click="nextMonth">翌月</span>
        </div>
      </div>
      <UserProfile/>
    </header>

    <!--schedule-wrapper-->
    <el-container  class="schedule-wrapper">
      <el-aside width="200px">
        <article class="schedule-side-wrap">
          <div class="calendar-mini">
            <div class="calendar-mini-item clearfix">
              <div class="year-month">{{year}}-{{month}}</div>
              <div class="calendar-nav">
                <span class="before-month" @click="beforeMonth"></span>
                <span class="next-month" @click="nextMonth"></span>
              </div>
            </div>
          
            <div class="calendar-mini-wrap clearfix">
              <div class="week-wrap clearfix">
                <div class="mon"></div>
                <div class="tue"></div>
                <div class="wed"></div>
                <div class="thu"></div>
                <div class="fri"></div>
                <div class="sat"></div>
                <div class="sun"></div>
              </div>
              <div class="day-wrap clearfix">
                <div v-for="day in days" :class="{today : day.isToday}"><a href="javascript:void(0)">{{day.day}}</a></div>
              </div>
            </div>
          </div>
        </article>
      </el-aside>
      <el-container>
        <section class="schedule-main-wrap clearfix">
          <div class="calendar">
            <div class="calendar-wrap clearfix">
              <div class="week-wrap clearfix">
                <div class="mon"></div>
                <div class="tue"></div>
                <div class="wed"></div>
                <div class="thu"></div>
                <div class="fri"></div>
                <div class="sat"></div>
                <div class="sun"></div>
              </div>
              <div class="day-wrap clearfix">
                <div v-for="day in days" :class="{today : day.isToday}" @click="openDetailModal(day.date)">
                  <a href="#" v-if="day.day !==''"><span class="calendar-date">{{day.day}}</span></a>
                  <div class="calendar-day-sckedule-wrap">
                    <div class="calendar-sckedule clearfix btns" v-for="schedule in schedules" v-if="schedule.day == day.date ">
                      <span class="calendar-sckedule-time_start">{{schedule.startTime}}</span>
                      <span class="calendar-sckedule-time_finish">{{schedule.finishTime}}</span>
                      <span class="calendar-sckedule-content">{{schedule.content}}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </el-container>
    </el-container>
    <!--/dashubord-schedule-wrapper-->
  </div>
</template>

<script>
  import Calendar from '../../mixins/Calendar'
  import UserProfile from "../../components/common/UserProfile";
  export default {
    components: {
      UserProfile
    },
    mixins: [Calendar],
    data() {
      return {
        currentDate: new Date(),
        days: [],            //日報データ
        schedules: [],            //日報データ
        searchWord: "",         //検索文字列
        showDetailModal: false,//詳細モーダル表示フラグ
        postDetail: null,      //詳細モーダルへ渡すデータ
      }
    },
    methods: {
      initMonth: function () {
        this.days = Calendar.getMonthListWithShift(this.currentDate);
      },
      beforeMonth: function () {
        this.currentDate = Calendar.getOtherMonth(this.currentDate, 'before')
        this.initMonth()
      },
      nextMonth: function () {
        this.currentDate = Calendar.getOtherMonth(this.currentDate, 'next')
        this.initMonth()
      },
      fetchReports: function () {
        axios.get('/api/getReportList').then((res) => {
          this.reports = res.data.data
          this.current_page = res.data.current_page
          this.last_page = res.data.last_page
          this.total = res.data.total
          this.from = res.data.from
          this.to = res.data.to
        })
      },
      load(page, searchWord, sort_col) {
        axios.get('/api/getReportSearch?q=' + searchWord + '&page=' + page + '&sort=' + sort_col).then((res) => {
          this.reports = res.data.data
          this.current_page = res.data.current_page
          this.last_page = res.data.last_page
          this.total = res.data.total
          this.from = res.data.from
          this.to = res.data.to
        })
      },
      openDetailModal(date) {
        this.postDetail = date;
        if (date) {
          this.schedules.push({day: date, startTime: '10:00', finishTime: '12:00', content: 'aaaa'});
        }
        this.showDetailModal = true;
      },
      closeDetailModal() {
        this.showDetailModal = false;
      }
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
      this.initMonth()
    },
  }
</script>

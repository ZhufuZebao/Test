<template>
  <div>
    <transition name="customer-search-modal">
      <div class="customer-search-modal-mask modal modal-show  schedulemodal">
        <div class="modalBody" ref="modalBody" @click.self="$emit('closeScheduleModal')"
             v-bind:style="{'margin-left': '-350px','margin-top': modalTop}">
          <div class="modal-container">
            <div class="modal-close" @click.self="$emit('closeScheduleModal')">×</div>
            <div class="modalBodycontent clearfix">

              <div class="zss-panel">
                <!-- header -->
                <div class="zss-panel-header">
                  <div class="zss-date-year">
                    <div class="zss-date-prev zss-text-hover" @click="prevYear"><i class="el-icon-arrow-left"></i></div>
                    <div class="zss-date-text zss-text-hover zss-text-year">{{this.emitDate.year}}</div>
                    <div class="zss-date-next zss-text-hover" @click="nextYear"><i class="el-icon-arrow-right"></i></div>
                  </div>
                </div>
                <!-- /header -->

                <div class="zss-panel-body">

                  <div class="zss-month-box">
                    <div class="zss-date-prev zss-text-hover" @click="prevMonth"><i class="el-icon-arrow-left"></i></div>
                    <div class="zss-month" v-for="(item,index) in monthArray">
                      <!--month-->
                      <!--schedule-side-item-->
                      <div class="calendar-mini">
                        <div class="calendar-mini-item clearfix">
                          <div class="year-month">{{item.month+1}}</div>
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
                          <div class="day-wrap clearfix ">
                            <div v-for="day in initMonth(item.year,item.month)"
                                 :class="{today : day.isToday, 'other-date' : day.type != 'currentMonth', holidayColor : holiday(day.date)}"
                                 @click="clickMonth(day.date)">
                              <a>{{day.day}}</a></div>
                          </div>
                        </div>
                      </div>
                      <!--/schedule-side-item-->
                    </div>
                    <div class="zss-date-next zss-text-hover" @click="nextMonth"><i class="el-icon-arrow-right"></i></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modalBK"></div>
      </div>
    </transition>
  </div>
</template>

<script>
  import Calendar from '../../mixins/Calendar'
  import Messages from "../../mixins/Messages";

  export default {
    mixins: [Calendar,Messages],
    props: {
      emitCurrentDate: {
        Type: String,
      },
    },
    data(){
      return {
        firstDate:[],  //first day of month
        days: [],
        japaneseDate:{},
        emitDate:{
          year:new Date().getFullYear(),
          month:new Date().getMonth(),
          day:new Date().getDate(),
        },
      }
    },
    methods:{
      //祝日
      holiday: function (date) {
        let dt = Calendar.dateFormat(date);
        // データ存在
        if (this.japaneseDate && this.japaneseDate[dt]) {
          return this.japaneseDate[dt].holiday;
        }
        return "";
      },
      initMonth(year,month){
        return Calendar.getMonthListWithOther(new Date(Date.UTC(year,month)));
      },
      //before year
      prevYear(){
        this.emitDate.year -= 1;
        this.getJapaneseDate(this.emitDate.year, this.emitDate.month);
      },
      //next year
      nextYear(){
        this.emitDate.year += 1;
        this.getJapaneseDate(this.emitDate.year, this.emitDate.month);
      },
      //before 3 months
      prevMonth(){
        //中間の日付は1の場合
        if (this.emitDate.month == 0){
          this.emitDate.month = 9;
          this.emitDate.year -= 1;
        }
        //中間の日付は2の場合
        else if (this.emitDate.month == 1){
          this.emitDate.month = 10;
          this.emitDate.year -= 1;
        }
        //中間の日付は3の場合
        else if (this.emitDate.month == 2){
          this.emitDate.month = 11;
          this.emitDate.year -= 1;
        }
        //中間の日付は4の場合
        else if (this.emitDate.month == 3){
          this.emitDate.month = 0;
        }
        //中間の日付は5-12の場合
        else if (this.emitDate.month >= 4 && this.emitDate.month<=11){
          this.emitDate.month -= 3
        }
        this.getJapaneseDate(this.emitDate.year, this.emitDate.month);
      },
      //next 3 months
      nextMonth(){
        //中間の日付は1-9の場合
        if (this.emitDate.month >= 0 && this.emitDate.month<=8) {
          this.emitDate.month += 3
        }
        //中間の日付は10の場合
        else if(this.emitDate.month == 9){
          this.emitDate.month = 0
          this.emitDate.year += 1;
        }
        //中間の日付は11の場合
        else if(this.emitDate.month == 10){
          this.emitDate.month = 1
          this.emitDate.year += 1;
        }
        //中間の日付は12の場合
        else if(this.emitDate.month == 11){
          this.emitDate.month = 2
          this.emitDate.year += 1;
        }
        this.getJapaneseDate(this.emitDate.year, this.emitDate.month);
      },
      clickMonth(date){
        this.$emit("setModalMonth",date);
        this.$emit("closeScheduleModal");
      },
      closeModal() {
        this.$emit('closeScheduleModal');
      },
      getJapaneseDate(year,month) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        let errMsg = this.commonMessage.error.scheduleList;
        axios.post('/api/getJapaneseDate', {
          stDate: year + '/' + month + '/' + 1,
        }).then((res) => {
          this.japaneseDate = res.data.params.japaneseDate;
          loading.close();
        }).catch(e => {
          loading.close();
          this.$alert(errMsg, {showClose: false});
        });
      },
    },
    computed:{
      modalLeft: function () {
        if (!this.isMounted)
          return;
        return '-' + this.$refs.modalBody.clientWidth / 2 + 'px';
      },
      modalTop: function () {
        return '0px';
      },
      monthArray() {
        let beforeMonth = '';
        let beforeYear = this.emitDate.year;
        let nextMonth = '';
        let nextYear = this.emitDate.year;
        //中間の日付は12の場合
        if (this.emitDate.month == 11) {
          beforeMonth = 10;
          nextMonth = 0;
          nextYear = this.emitDate.year + 1;
        }
        //中間の日付は1の場合
        if (this.emitDate.month == 0 ){
          beforeMonth = 11;
          nextMonth = 1;
          beforeYear = this.emitDate.year - 1;
        }
        //中間の日付は2－11の場合
        if (this.emitDate.month >= 1 && this.emitDate.month <= 10){
          beforeMonth = this.emitDate.month - 1;
          nextMonth = this.emitDate.month + 1;
        }
        return [{year:beforeYear,month:beforeMonth}, {year:this.emitDate.year,month:this.emitDate.month},{year:nextYear,month:nextMonth}]
      }
    },
    created() {
      this.emitDate.year = this.emitCurrentDate.getFullYear();
      this.emitDate.month = this.emitCurrentDate.getMonth();
      this.getJapaneseDate(this.emitDate.year, this.emitDate.month);
    }
  }

</script>



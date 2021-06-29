<template>
  <!--container-->
  <div class="container clearfix commonAll schedule datapoper">
    <header>
      <h1>
        <router-link :to="{ path: '/schedule',query: {flag:'success'}}">
          <div class="commonLogo">
            <ul>
              <li class="bold">SCHEDULE</li>
              <li>スケジュール</li>
            </ul>
          </div>
        </router-link>
      </h1>

      <div class="title-wrap">
        <el-breadcrumb separator-class="el-icon-arrow-right">
          <el-breadcrumb-item :to="{ path: '/schedule',query: {createDate: incomingDate, flag:'success'}}"
                              v-if="this.$route.query.pageName === 'scheduleOrgWeek' || this.$route.query.pageName === 'schedule'">
            <span>スケジュール（組織・週間）</span>
          </el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/schedule/week',query: {createDate: incomingDate}}"
                              v-else-if="this.$route.query.pageName === 'scheduleWeek'">
            <span>スケジュール（個人・週間）</span>
          </el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/schedule/people/month',query: {createDate: incomingDate}}"
                              v-else-if="this.$route.query.pageName === 'schedulePeopleMonth'">
            <span>スケジュール（個人・月間）</span>
          </el-breadcrumb-item>
          <el-breadcrumb-item :to="{ path: '/schedule/scheduleDay',query: {createDate: incomingDate}}"
                              v-else-if="this.$route.query.pageName === 'scheduleDay'">
            <span>スケジュール（組織・日）</span>
          </el-breadcrumb-item>
          <el-breadcrumb-item v-if="$route.name === 'scheduleEdit'">
            {{scheduleName('scheduleMsg')}}（{{scheduleName('update')}}）
          </el-breadcrumb-item>
          <el-breadcrumb-item v-else>
            {{scheduleName('scheduleMsg')}}（{{scheduleName('topAdd')}}）
          </el-breadcrumb-item>
        </el-breadcrumb>
      </div>
      <UserProfile/>
    </header>
    <!--schedule-wrapper-->
    <section class="common-wrapper">
      <div class="common-view common-wrapper-container">
        <div class="content scheduleadd">
          <el-form :model="schedule" :rules="scheduleRules" ref="form" label-width="150px">
            <div style="display: flex;align-items: center;">
              <el-checkbox v-model="allDayChecked" @change="changeEqual" class="scheduleadd-allday">{{scheduleName('allDay')}}</el-checkbox>
              <el-form-item :label="scheduleName('repeat')" class="myInput repeatTypename">
                <el-select class="padding-model schdeulecreat-input" v-model="schedule.repeatType"
                           @change="changeRepeatType" :disabled="updateType===1 || updateType==='1'">
                  <el-option v-for="repeatType in repeatTypeColsTmp" :key="repeatType.id" :label="repeatType.name"
                             :value="repeatType.id"></el-option>
                </el-select>
              </el-form-item>
            </div>
            <el-form-item :label="planName('week')" v-if="schedule.repeatType==='2'" class="myInput repeatTypename">
              <div class="el-input">
                <el-checkbox class="" v-for="week in weeks" :key="week.id" @change="checkWeek(week.id)"
                             :checked="week.isChecked">{{week.name}}
                </el-checkbox>
              </div>
            </el-form-item>

            <div class="myInput" style="margin-bottom: 7px">
              <div v-show="schedule.repeatType==='0'">
                <el-form-item prop="stDay" class="scheduleaddform">
                  <el-date-picker class="scheduleadd-fristdata" type="date" v-model="schedule.stDay"
                                  :picker-options="stPickerOptions"
                                  value-format="yyyy-MM-dd" @change="clickTime"
                                  :disabled="updateType===1||updateType===2||updateType==='1'||updateType==='2'" :clearable="clearable">
                  </el-date-picker>
                </el-form-item>
                <el-select popper-class="dataselect" class="scheduleadd-select"
                        ref="st_time_option"
                        @visible-change="upStOption('st_time_option')"
                        @change="clickTime"
                        v-model="stTime"
                        v-if="!allDayChecked"
                        :filter-method="filterStMethod"
                        :clearable="clearable"
                        filterable>
                  <el-option
                          v-for="item in stTimeArr"
                          :key="item"
                          :label="item"
                          :value="item">
                  </el-option>
                </el-select>
                <span class="scheduleaddand">～</span>
                <el-form-item prop="edDay" class="scheduleaddform">
                  <el-date-picker type="date" v-model="schedule.edDay" :picker-options="pickerOptions"
                                  value-format="yyyy-MM-dd" :disabled="updateType===1||updateType==='1'" @change="clickTime"
                                  :clearable="clearable"></el-date-picker>
                </el-form-item>
                <el-select popper-class="dataselect" class="scheduleadd-select"
                        ref="ed_time_option"
                        @visible-change="upEdOption('ed_time_option')"
                        @change="clickTime"
                        v-model="edTime"
                        v-if="!allDayChecked"
                        :filter-method="filterEdMethod"
                        :clearable="clearable"
                        filterable>
                  <el-option
                          v-for="item in edTimeArr"
                          :key="item"
                          :label="item"
                          :value="item">
                  </el-option>
                </el-select>
              </div>

              <div v-show="schedule.repeatType!=='0'">
                <el-form-item prop="stDay" class="scheduleaddform">
                  <el-date-picker class="scheduleadd-fristdata" type="date" v-model="schedule.stDay"
                                  :picker-options="stPickerOptions"
                                  value-format="yyyy-MM-dd" @change="clickTime"
                                  :disabled="updateType===1||updateType===2||updateType==='1'||updateType==='2'" :clearable="clearable">
                  </el-date-picker>
                </el-form-item>
                <span class="scheduleaddand">～</span>
                <el-form-item prop="edDay" class="scheduleaddform">
                  <el-date-picker type="date" v-model="schedule.edDay" :picker-options="pickerOptions"
                                  value-format="yyyy-MM-dd" :disabled="updateType===1||updateType==='1'" @change="clickTime"
                                  :clearable="clearable"></el-date-picker>
                </el-form-item>
                <el-select popper-class="dataselect" class="scheduleadd-select"
                        ref="st_time_option_1"
                        @visible-change="upStOption('st_time_option_1')"
                        @change="clickTime"
                        v-model="stTime"
                        v-if="!allDayChecked"
                        :filter-method="filterStMethod"
                        :clearable="clearable"
                        filterable>
                  <el-option
                          v-for="item in stTimeArr"
                          :key="item"
                          :label="item"
                          :value="item">
                  </el-option>
                </el-select>
                <span class="scheduleaddand" v-if="!allDayChecked">～</span>
                <el-select popper-class="dataselect" class="scheduleadd-select"
                        ref="ed_time_option_1"
                        @visible-change="upEdOption('ed_time_option_1')"
                        @change="clickTime"
                        v-model="edTime"
                        v-if="!allDayChecked"
                        :filter-method="filterEdMethod"
                        :clearable="clearable"
                        filterable>
                  <el-option
                          v-for="item in edTimeArr"
                          :key="item"
                          :label="item"
                          :value="item">
                  </el-option>
                </el-select>
              </div>
            </div>

            <el-form-item prop="subject" class="scheduleaddform  myInput">
              <el-input class="myInputWidth" v-model="schedule.subject" placeholder='予定タイトル' maxlength="91"></el-input>
            </el-form-item>

            <div class="formStyle schedule-creat-form">
              <el-form-item prop="comment" :label="scheduleName('comment')">
                <el-input class="fontWeight" type="textarea" v-model="schedule.comment" maxlength="500"></el-input>
              </el-form-item>

              <el-form-item prop="location" :label="scheduleName('location')">
                <el-input v-model="schedule.location" maxlength="101"></el-input>
              </el-form-item>

              <el-form-item class="display-checkbox" :label="scheduleName('select')">
                <input id="form011" type="checkbox" name="add-participant" :checked="checkTypeArr[0]"
                       @click="changeType(0)">
                <label for="form011" :class="'checkbox2 ' + typeClassName('1')">{{typeName('1')}}</label>

                <input id="form015" type="checkbox" name="add-participant" :checked="checkTypeArr[4]"
                       @click="changeType(4)">
                <label for="form015" :class="'checkbox2 ' + typeClassName('5')">{{typeName('5')}}</label>

                <input id="form012" type="checkbox" name="add-participant" :checked="checkTypeArr[1]"
                       @click="changeType(1)">
                <label for="form012" :class="'checkbox2 ' + typeClassName('2')">{{typeName('2')}}</label>

                <input id="form014" type="checkbox" name="add-participant" :checked="checkTypeArr[3]"
                       @click="changeType(3)">
                <label for="form014" :class="'checkbox2 ' + typeClassName('4')">{{typeName('4')}}</label>

                <input id="form013" type="checkbox" name="add-participant" :checked="checkTypeArr[2]"
                       @click="changeType(2)">
                <label for="form013":class="'checkbox2 ' + typeClassName('3')">{{typeName('3')}}</label>

                <input id="form016" type="checkbox" name="add-participant" :checked="checkTypeArr[5]"
                       @click="changeType(5)">
                <label for="form016" :class="'checkbox2 ' + typeClassName('6')">{{typeName('6')}}</label>
              </el-form-item>

              <el-form-item label="通知">
                <el-select v-model="schedule.notify_min_ago" @change="changeNotifyType">
                  <el-option v-for="notifyType in notifyTypeColsTmp" :key="notifyType.id" :label="notifyType.name"
                             :value="notifyType.value"></el-option>
                </el-select>
              </el-form-item>

              <el-form-item prop="participant" :label="scheduleName('participant')">
                <span v-for="(participantItem,index) in participantsCheckArr" :key="index">
                  <el-button round style="background:none;color:black;">
                    {{participantItem.name}}さん&nbsp;&nbsp;&nbsp;&nbsp;
                    <span @click.prevent="delCheckItem(index)">X</span>
                  </el-button>
                </span>
              </el-form-item>

              <el-form-item>
                <el-button class="padding-model" type="primary" round @click.prevent="openParticipantSelectModal">
                  {{scheduleName('other')}}
                </el-button>
              </el-form-item>
            </div>
            <div class="lander myInput" v-if="schedule.createUser">
              登録情報:{{schedule.createUser.name}}さん{{createDateFormat}}
            </div>
            <div class="lander myInput" v-if="schedule.updateUser">
              更新情報:{{schedule.updateUser.name}}さん{{updateDateFormat}}
            </div>

          </el-form>
        </div>
        <div class="pro-button">
          <a class="modoru" @click="cancel">{{scheduleName('cancel')}}</a>
          <a class="nextPage" @click="submitForm">{{scheduleName('add')}}</a>
        </div>
      </div>
    </section>
    <!--/dashubord-schedule-wrapper-->
    <ScheduleParticipantsSelectModal :participantsCheckArr="participantsCheckArr"
                                     @participantSelectBack="participantSelectBack"
                                     v-if="showParticipantsSelectModal"></ScheduleParticipantsSelectModal>
  </div>
</template>

<script>
  import Common from '../../mixins/Common'
  import ScheduleLists from '../../mixins/ScheduleLists'
  import validation from '../../validations/schedule.js'
  import ScheduleParticipantsSelectModal from '../../components/schedule/ScheduleParticipantsSelectModal.vue'
  import Messages from "../../mixins/Messages";
  import Calendar from "../../mixins/Calendar";
  import UserProfile from "../../components/common/UserProfile";

  export default {
    mixins: [validation, ScheduleLists, Messages, Calendar, Common],
    components: {ScheduleParticipantsSelectModal, UserProfile},
    data() {
      return {
        selectTimeArr:[],
        inputTimeArr:[],
        testTime:'2020/01/01',
        incomingDate: null, //着信日
        clearable: false,
        schedule: {
          id: '',
          st_datetime: '',
          ed_datetime: '',
          type: '0',
          open_range: '',
          subject: '',
          location: '',
          comment: '',
          repeat_kbn: '0',
          week1: '',
          week2: '',
          week3: '',
          week4: '',
          week5: '',
          week6: '',
          week7: '',
          st_span: '',
          ed_span: '',
          notify: '',
          notify_min_ago: '',
          stDay: '',
          edDay: '',
          interval: '1',
          times: '',
          checkWeekArr: [false, false, false, false, false, false, false],
          repeatType: '0',
          repeatIntervalType: '',
          repeatWeekType: '',
          repeatMonthType: '',
          allDay: '0',
          stDayWeekIndex: 0,
        },
        stTime: '13:00',
        stMinute: '',
        edTime: '14:00',
        edMinute: '',
        allDayChecked: false,
        showParticipantsSelectModal: false,
        participantsCheckArr: [],
        checkTypeArr: [false, false, false, false, false, false, false],
        weeks: [],
        dayName: '',
        updateType: 3,
        updateDate: '',
        typeRadio: 5,
        pickerOptions: {
          disabledDate: this.disabledDate,
        },
        stPickerOptions: {
          disabledDate: this.stDisabledDate,
        },
        stTimeArr: [],
        edTimeArr: [],
        otherId: -1,
        closed: false,
        repeatTypeColsTmp: [],
        userObj: {},
        selectableRangeSt: '',
        selectableRangeEd: '',
        intervalTypeColsArr: [],
        userUpdateDate: '',
        userUpdateName: '',
        notifyTypeColsTmp:[
          {"name": 'なし', "id": '0',"value": ""},
          {"name": '開始時刻', "id": '1',"value": "0"},
          {"name": '5分前', "id": '2',"value": "5"},
          {"name": '10分前', "id": '3',"value": "10"},
          {"name": '30分前', "id": '4',"value": "30"},
          {"name": '1時間前', "id": '5',"value": "60"},
        ],
         submit:true,
      }
    },
    computed: {
      createDateFormat: function () {
        return Calendar.dateFormat(this.schedule.created_at, '（yyyy年MM月dd日 hh:mm）');
      },
      updateDateFormat: function () {
        return Calendar.dateFormat(this.schedule.updated_at, '（yyyy年MM月dd日 hh:mm）');
      },
    },
    methods: {
      filterEdMethod(timeline){
        if (this.inputTimeArr.indexOf(timeline)>=0){
          this.edTime = timeline;
        }
      },
      filterStMethod(timeline) {
        if (this.inputTimeArr.indexOf(timeline)>=0){
          this.stTime = timeline;
        }
      },
      changeEndSpan() {
        this.schedule.st_span = this.schedule.stDay;
        switch (this.schedule.repeatType) {
            //繰り返しない
          case "0":
            break;
            //毎日
          case "1":
            this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, this.schedule.times - 1);
            this.schedule.edDay = this.schedule.ed_span;
            break;
            //毎週 平日(月～金)
          case "2":
            this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 7 * this.schedule.times - 1);
            break;
            //毎週 はん曜日
          case "3":
            this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 7 * this.schedule.times - 1);
            break;
            //毎月
          case "4":
            this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 30 * this.schedule.times - 1);
            break;
            //カスタマイズ
          case "5":
            if (this.schedule.repeatIntervalType === '1') {
              this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 7 * this.schedule.times + 7 * this.schedule.repeatWeekType - 1);
            } else if (this.schedule.repeatIntervalType === '2') {
              this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 30 * this.schedule.times + 30 * this.schedule.repeatMonthType - 1);
            }
            break;
        }
      },
      changeDayTime(){
        if (this.schedule.repeatType === '1' && this.stTime === this.edTime){
          if(this.edTime === '23:55'){
            this.stTime = '23:50'
          }else{
            this.edTime = this.addMin(this.stTime).toString();
          }
        }
      },
      addMin(stTime){
        let formatNumber = function (value) {
          if (value < 10) {
            return '0' + value;
          }
          return value;
        };
        let date = new Date(new Date(this.testTime +' '+stTime+':00').getTime() + 1000 * 60 * 15)
        return formatNumber(date.getHours()) + ':' + formatNumber(date.getMinutes())
      },
      upStOption(name) {
        this.stTimeArr = this.selectTimeArr;
        this.$refs[name].selectedLabel = this.stTime;
      },
      upEdOption(name) {
        this.edTimeArr = this.selectTimeArr;
        if (this.schedule.stDay === this.schedule.edDay && this.schedule.repeatType === '0') {
          this.edTimeArr = this.stTimeArr.filter(item => {
            if (new Date(this.testTime + ' ' + item).getTime() >
                new Date(this.testTime + ' ' + this.stTime).getTime()) {
              return item;
            }
          });
        }
        this.$refs[name].selectedLabel = this.edTime;
      },
      clickTime() {
        if(this.schedule.stDay){
            this.schedule.stDay=Calendar.dateFormat(this.schedule.stDay,'yyyy-MM-dd');
        }
        if(this.schedule.edDay){
            this.schedule.edDay=Calendar.dateFormat(this.schedule.edDay,'yyyy-MM-dd');
        }
        let num = new Date(this.schedule.stDay).getDay();
        if (num === 0) {
          this.schedule.stDayWeekIndex = 6;
        } else {
          this.schedule.stDayWeekIndex = num - 1;
        }
        if (this.schedule.stDay === this.schedule.edDay && this.schedule.repeatType === '0') {
          if (new Date(this.testTime + ' '+ this.stTime).getTime() >=
              new Date(this.testTime + ' '+ this.edTime).getTime()) {
            this.edTime = this.addMin(this.stTime).toString();
          }
        } else{
          if (this.schedule.repeatType === '2' || this.schedule.repeatType === '4'){
            if ( this.edTime === this.stTime){
              if(this.edTime === '23:55'){
                this.stTime = '23:50'
              }else if (this.stTime === '00:00'){
                this.edTime = this.addMin(this.stTime).toString();
              }else{
                this.edTime = this.addMin(this.stTime).toString();
              }
            }
          }
        }
        this.changeDayTime();
      },
      changeTime(type) {
        if (this.schedule.stDay !== this.schedule.edDay) {
          if (this.stTime !== '00:00' && this.stTime !== null) {
            this.selectableRangeSt = '00:00:00-23:59:00';
          }
          if (this.edTime !== '00:00' && this.edTime !== null) {
            this.selectableRangeEd = '00:00:00-23:59:00';
          }
          return;
        } else if ((this.schedule.stDay !== this.schedule.edDay) || this.stTime === null || this.edTime === null
            || this.stTime === '00:00' || this.edTime === '00:00') {
          return;
        }
        if (this.edTime.getTime() < this.stTime.getTime()) {
          if (type === 1) {
            this.stTime = this.edTime;
          } else if (type === 2) {
            this.edTime = this.stTime;
          }
        }
        this.selectableRangeEd = (Array(2).join('0') + this.stTime.getHours()).slice(-2) + ':' + (Array(2).join('0') + this.stTime.getMinutes()).slice(-2) + ':00-23:59:00';
        this.selectableRangeSt = '00:00:00-' + (Array(2).join('0') + this.edTime.getHours()).slice(-2) + ':' + (Array(2).join('0') + this.edTime.getMinutes()).slice(-2) + ':00';
      },
      disabledDate(time) {
          return time.getTime() < new Date(this.schedule.stDay).getTime() - 1000 * 60 * 60 * 24;
      },
      stDisabledDate(time) {
          if (this.schedule.ed_span === '') {
              return time.getTime() > new Date(this.schedule.edDay).getTime();
          } else {
              return (time.getTime() > new Date(this.schedule.ed_span).getTime()) || (time.getTime() > new Date(this.schedule.edDay).getTime());
          }
      },
      changeRepeatType() {
        this.clickTime();
        if (this.schedule.repeatType !== '2') {
          this.weekClear();
          this.schedule.checkWeekArr = [false, false, false, false, false, false, false];
          this.schedule.repeatIntervalType = '';
          for (let i = 0; i < 7; i++) {
            this.weeks[i].isChecked = false;
          }
        }
        this.changeDayTime();
      },

      changeNotifyType() {
        //
      },

      checkWeek(index) {
        this.schedule.checkWeekArr[index - 1] = !this.schedule.checkWeekArr[index - 1];
        if (this.schedule.checkWeekArr[index - 1]) {
          this.schedule["week" + index] = '1';
        } else {
          this.schedule["week" + index] = '';
        }
      },
      changeType(index) {
          if(this.schedule.type == index + 1){
              this.checkTypeArr = [false, false, false, false, false, false, false];
              this.schedule.type = 0;
          }else{
              this.checkTypeArr = [false, false, false, false, false, false, false];
              this.checkTypeArr[index] = true;
              this.schedule.type = index + 1;
          }
      },
      submitForm() {
        let isTrue = this.schedule.checkWeekArr.filter(data=> {
          if (data) {
            return data;
          }
        });
        if(this.submit){
          this.submit = false;
        }else {
          return;
        }
        if (this.participantsCheckArr.length === 0) {
          this.$alert(this.commonMessage.warning.noParticipant, {showClose: false});
          return;
        }
        if (!isTrue[0] && this.schedule.repeatType === '2'){
          let errorMsg = this.commonMessage.error.repeatWeekErrorMsg;
          this.$alert(errorMsg, {showClose: false});
          return;
        }
        this.$refs['form'].validate((valid) => {
          if (valid) {
            const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
            this.schedule.st_span = this.schedule.stDay;
            this.schedule.ed_span = this.schedule.edDay;
            switch (this.schedule.repeatType) {
                //繰り返しない
              case "0":
                this.schedule.repeat_kbn = '0';
                this.schedule.st_span = '';
                this.schedule.ed_span = '';
                break;
                //毎日
              case "1":
                this.schedule.repeat_kbn = '1';
                // 回数を設定
                if (this.schedule.interval === '2') {
                  this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, this.schedule.times - 1);
                }
                break;
                //毎週 平日(月～金)
              case "2":
                this.schedule.repeat_kbn = '4';
                if (this.schedule.interval === '2') {
                  this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 7 * this.schedule.times - 1);
                }
                break;
                //毎週 はん曜日
              case "3":
                this.schedule.repeat_kbn = '4';
                let startDay = new Date(this.schedule.stDay);
                let startDayId = startDay.getDay();
                this.checkWeek(startDayId + 1);
                if (this.schedule.interval === '2') {
                  this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 7 * this.schedule.times - 1);
                }
                break;
                //毎月
              case "4":
                // stDayWeekIndex
                this.schedule.repeat_kbn = '3';
                if (this.schedule.interval === '2') {
                  this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 30 * this.schedule.times - 1);
                }
                break;
                //カスタマイズ
              case "5":
                this.schedule.repeat_kbn = '4';
                if (this.schedule.repeatIntervalType === '1') {
                  if (this.schedule.interval === '2') {
                    this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 7 * this.schedule.times + 7 * this.schedule.repeatWeekType - 1);
                  }
                } else if (this.schedule.repeatIntervalType === '2') {
                  if (this.schedule.interval === '2') {
                    this.schedule.ed_span = this.dateAddDays(this.schedule.st_span, 30 * this.schedule.times + 30 * this.schedule.repeatMonthType - 1);
                  }
                }
                break;
            }
            switch (this.schedule.notify_min_ago) {
              case '開始時刻':
                this.schedule.notify_min_ago = '0';
                break;
              case '5分前':
                this.schedule.notify_min_ago = '5';
                break;
              case '10分前':
                this.schedule.notify_min_ago = '10';
                break;
              case '30分前':
                this.schedule.notify_min_ago = '30';
                break;
              case '1時間前':
                this.schedule.notify_min_ago = '60';
                break;
              case 'なし':
                this.schedule.notify_min_ago = '';
                break;
            }
            this.schedule.st_datetime = this.schedule.stDay + " " + this.stTime;
            this.schedule.ed_datetime = this.schedule.edDay + " " + this.edTime;
            let msg = this.commonMessage.success.insert;
            let errMsg = this.commonMessage.error.scheduleEdit;
            axios.post("/api/setSchedule", {
              schedule: this.schedule,
              participantsCheckArr: this.participantsCheckArr,
              id: this.schedule.id,
              updateType: this.updateType,
              updateDate: this.updateDate,
              allDay:this.schedule.allDay,
            }).then(res => {
              if (res.data.result === 0) {
                window.successMsg = msg;
                this.cancel();
                this.submit=true;
                loading.close();
              } else if (res.data.result === 1) {
                this.$alert(res.data.errors, {showClose: false});
                this.submit=true;
                loading.close();
              }
            }).catch(err => {
              loading.close();
              this.$alert(errMsg);
            });
          } else {
            this.submit=true;
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
            setTimeout(() => {
              let isError = document.getElementsByClassName("is-error");
              isError[0].querySelector('input').focus();
            }, 1);
            return false;
          }
        });
      },
      changeEqual() {
        if (this.allDayChecked) {
          this.stTime = "00:00";
          this.edTime = "23:55";
          this.schedule.allDay = '1'
        } else {
          this.schedule.allDay = '0'
        }
      },
      cancel() {
        if (this.$route.query.pageName) {
          if (this.$route.query.pageName === 'scheduleOrgWeek' || this.$route.query.pageName === 'schedule') {
            this.$router.push({path: "/schedule", query: {createDate: this.incomingDate, flag: 'success'}});
          } else if (this.$route.query.pageName === 'scheduleWeek') {
            this.$router.push({path: "/schedule/week", query: {createDate: this.incomingDate}});
          } else if (this.$route.query.pageName === 'schedulePeopleMonth') {
            this.$router.push({path: "/schedule/people/month", query: {createDate: this.incomingDate}});
          }else if (this.$route.query.pageName === 'scheduleDay') {
              this.$router.push({path: "/schedule/scheduleDay", query: {createDate: this.incomingDate}});
          }
        } else {
          this.$router.push({path: "/schedule", flag: 'success'});
        }
      },
      weekClear() {
        this.schedule.week1 = '';
        this.schedule.week2 = '';
        this.schedule.week3 = '';
        this.schedule.week4 = '';
        this.schedule.week5 = '';
        this.schedule.week6 = '';
        this.schedule.week7 = '';
      },
      openParticipantSelectModal() {
        this.showParticipantsSelectModal = true;
      },
      participantSelectBack(data) {
        this.showParticipantsSelectModal = false;
        if (data) {
          this.participantsCheckArr = data;
        }
          this.$refs['form'].validate((valid) => {});
      },
      delCheckItem(index) {
        this.participantsCheckArr.splice(index, 1);
      },
      dateAddDays(dataStr, dayCount) {
        let data = new Date(dataStr.replace(/-/g, "/"));
        data = new Date((data / 1000 + (86400 * dayCount)) * 1000);
        return data.getFullYear() + "-" + (data.getMonth() + 1) + "-" + (data.getDate());
      },
      scheduleFetch(scheduleId) {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.get("/api/getScheduleById", {
          params: {
            scheduleId: scheduleId,
          }
        }).then(res => {
          if (res.data.result === 0) {
            //schedule
            let result = res.data.params;
            this.schedule.id = result.id;
            this.schedule.createUser = result.create_by;
            this.schedule.updateUser = result.update_by;
            this.schedule.created_at = result.created_at;
            this.schedule.updated_at = result.updated_at;
            this.schedule.open_range = result.open_range;
            this.schedule.notify = result.notify;
            this.schedule.allDay = result.all_day;
            if(result.all_day){
                this.allDayChecked=true;
            }
            // this.schedule.notify_min_ago = result.notify_min_ago;
            switch (result.notify_min_ago) {
              case 0:
                this.schedule.notify_min_ago = '開始時刻';
                break;
              case 5:
                this.schedule.notify_min_ago = '5分前';
                break;
              case 10:
                this.schedule.notify_min_ago = '10分前';
                break;
              case 30:
                this.schedule.notify_min_ago = '30分前';
                break;
              case 60:
                this.schedule.notify_min_ago = '1時間前';
                break;
              default:
                this.schedule.notify_min_ago = 'なし';
            }
            this.schedule.st_datetime = result.st_datetime;
            this.schedule.ed_datetime = result.ed_datetime;
            this.schedule.type = result.type;
            this.typeRadio = result.type;
            this.checkTypeArr = [false, false, false, false, false, false];
            this.checkTypeArr[this.schedule.type - 1] = true;
            this.schedule.subject = result.subject;
            this.schedule.location = result.location;
            this.schedule.comment = result.comment;
            this.schedule.repeat_kbn = result.repeat_kbn;
            for (let i = 1; i <= 7; i++) {
              if (result['week' + i] == null) {
                this.schedule['week' + i] = '';
              } else {
                this.schedule['week' + i] = result['week' + i];
              }
            }
            this.schedule.st_span = result.st_span;
            this.schedule.ed_span = result.ed_span;
            if (!result.ed_span){
              this.schedule.ed_span = '';
            }
            let arrDay = result.st_datetime.split(" ");
            this.schedule.stDay = arrDay[0];
            this.stTime = arrDay[1].substring(0, 5);
            arrDay = result.ed_datetime.split(" ");
            this.schedule.edDay = arrDay[0];
            this.edTime = arrDay[1].substring(0, 5);
            // DB毎月（3）の場合、毎月（4）
            if (result.repeat_kbn === '3') {
              this.schedule.repeatType = '4';
              // 日曜指定（4）の場合、毎週（2）
            } else if (result.repeat_kbn === '4') {
              this.schedule.repeatType = '2';
              for (let i = 1; i <= 7; i++) {
                this.schedule.checkWeekArr[i - 1] = result['week' + i] === '1';
                if (this.schedule.checkWeekArr[i - 1]) {
                  this.weeks[i - 1].isChecked = true;
                }
              }
            } else {
              // DB一日（0）毎日（1）の場合、一日（0）毎日（1）
              this.schedule.repeatType = result.repeat_kbn;
              this.schedule.checkWeekArr = [false, false, false, false, false, false, false];
            }
            if (this.updateType === '1' || this.updateType === 1) {
              this.schedule.stDay = this.updateDate;
              this.schedule.edDay = this.updateDate;
              this.schedule.repeatType = '0';
              this.changeRepeatType();
            } else if (this.updateType === '2' || this.updateType === 2) {
              this.schedule.stDay = this.updateDate;
            }
            //participants
            for (let i = 0; i < result.schedule_participants.length; i++) {
              if (result.schedule_participants[i].user_info){
                this.participantsCheckArr.push({
                  id: result.schedule_participants[i].user_info.id,
                  name: result.schedule_participants[i].user_info.name,
                });
              }
            }
          } else {
            this.$alert(res.data.errors, {showClose: false,
              callback: action => {
                this.cancel();
              }
            });
          }
          loading.close();
        }).catch(err => {
          loading.close();
          console.warn(err);
        })
      },
    },
    mounted() {
      this.selectTimeArr = Common.getOptions('00:00','24:00',15);
      this.inputTimeArr = Common.getOptions('00:00','24:00',5);
      this.stTimeArr = this.selectTimeArr;
      this.edTimeArr = this.selectTimeArr;
      this.repeatTypeColsTmp = this.repeatTypeCols;
      this.weeks = this.weekCols;
      for (let i = 0; i < 7; i++) {
        this.weeks[i].isChecked = false;
      }
      //#5175 Question No.1,set "createDate" as a string
      if (this.$route.query.createDate && this.$route.query.createDate !== '' && typeof this.$route.query.createDate == "string"){
        let createDate = this.$route.query.createDate;
        this.schedule.stDay = createDate;
        this.incomingDate = Calendar.dateFormat(createDate);
        this.schedule.edDay = createDate;
        if (this.$route.query.stH && this.$route.query.stH !== '' && this.$route.query.stM && this.$route.query.stM !== ''){
          this.stTime = this.$route.query.stH + ':' + this.$route.query.stM;
        }else{
          this.stTime = '13:00';
        }
        if (this.$route.query.edH && this.$route.query.edH !== '' && this.$route.query.edM && this.$route.query.edM !== ''){
          this.edTime = this.$route.query.edH + ':' + this.$route.query.edM;
        }else{
          this.edTime = '14:00';
        }
        let num = new Date(createDate).getDay();
        if (num === 0) {
          this.schedule.stDayWeekIndex = 6;
        } else {
          this.schedule.stDayWeekIndex = num - 1;
        }
        this.clickTime();
      }
      if (this.$route.query.id && this.$route.query.id !== undefined && this.$route.query.id !== '') {
        this.otherId = this.$route.query.id;
      }
      if (this.$route.query && this.$route.query.updateDate) {
        this.incomingDate = Calendar.dateFormat(this.$route.query.updateDate);
      }
      axios.get("/api/getUser")
          .then(res => {
            this.userObj = res.data;
            if (this.$route.name === 'scheduleEdit') {
              this.updateType = this.$route.query.updateType;
              this.updateDate = this.$route.query.updateDate.replace('/', '-').replace('/', '-');
              this.scheduleFetch(this.$route.query.id);
            } else {
              // 組織・日の参加者を自動追加します。
              if(this.$route.query.pageName === 'scheduleDay' && this.$route.query.participantLists){
                let userIsHaveFlag = false;
                if(this.$route.query.participantLists){
                  this.participantsCheckArr = this.$route.query.participantLists;
                  for (let i = 0; i < this.participantsCheckArr.length; i++) {
                    if(this.participantsCheckArr[i].id === res.data.id){
                      userIsHaveFlag = true;
                      break;
                    }
                  }
                }
                if(!userIsHaveFlag || !this.$route.query.participantLists){
                  this.participantsCheckArr.push(res.data);
                }
              }
              if(this.otherId && !this.$route.query.participantLists){
                this.participantsCheckArr.push(res.data);
                axios.get("/api/getOtherUser", {
                  params: {
                    id: this.otherId,
                  }
                }).then(res => {
                  if (res.data.id !== this.participantsCheckArr[0].id && res.data !== '') {
                    this.participantsCheckArr.push(res.data);
                  }
                }).catch(err => {
                });
              }
            }
          }).catch(err => {
      });

    },
  }
</script>

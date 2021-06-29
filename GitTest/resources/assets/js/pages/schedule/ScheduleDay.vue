<template>
  <div class="container clearfix schedule schdule-day commonAll">
    <header>
      <h1>
        <router-link to="/schedule/scheduleDay">
          <div class="commonLogo">
            <ul>
              <li class="bold">SCHEDULE</li>
              <li>スケジュール</li>
            </ul>
          </div>
        </router-link>
      </h1>
      <div class="title-wrap week-title">
        <h2>スケジュール（組織・日）</h2>
        <ul class="header-nav schedule">
          <li class="header-nav-schedule-li">
            <span class="current-li"
              ><router-link
                :to="{
                  path: '/schedule',
                  query: { createDate: emitCurrentDate },
                }"
                >組織</router-link
              ></span
            >
            <span class="button-wrap non-current-li"
              ><router-link
                :to="{
                  path: '/schedule/week',
                  query: { createDate: emitCurrentDate },
                }"
                >個人</router-link
              ></span
            >
          </li>
          <li class="buttons non-currentdata">
            <router-link
              :to="{
                path: '/schedule',
                query: { createDate: emitCurrentDate },
              }"
              >週</router-link
            >
          </li>
          <li class="buttons currentdata">
            <router-link
              :to="{
                path: '/schedule/scheduleDay',
                query: { createDate: emitCurrentDate },
              }"
              >日</router-link
            >
          </li>
        </ul>
      </div>
      <UserProfile />
    </header>
    <el-container class="schedule-wrapper schedule-frist">
      <el-aside width="200px">
        <article class="schedule-side-wrap">
          <div class="calendar-mini">
            <div class="calendar-mini-item clearfix">
              <div
                class="year-month"
                @click.prevent="openScheduleModal"
                v-for="(day, index) in days"
                :key="index"
                v-if="index === 7"
              >
                <span>{{ formatDate(day.date).substring(0, 5) }}</span>
                <span class="year-month-bold">{{
                  parseInt(formatDate(day.date).substring(5, 7))
                }}</span>
                <i class="el-icon-caret-bottom" style="color: #9dc815"></i>
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
                <div
                  v-for="(day, index) in days"
                  :key="index"
                  :class="{
                    today: day.isToday,
                    'other-date': day.type != 'currentMonth',
                    holidayColor: holiday(day.date),
                  }"
                  @click.prevent="curr(day.date)"
                >
                  <a href="javascript:void(0)">{{ day.day }}</a>
                </div>
              </div>
            </div>
          </div>
        </article>
      </el-aside>
      <el-container>
        <section class="schedule-org-main-top clearfix">
          <div class="schdule-org-week-datalist">
            <div class="calendar-org-week-wrap">
              <div
                class="header-calendar-nav"
                style="padding: auto; cursor: pointer"
              >
                <i
                  class="el-icon-arrow-left"
                  style="color: #959dad"
                  @click="beforeDay"
                ></i>
                <i class="buttons today" style="background-color: #cccccc">{{
                  showToday()
                }}</i>
                <i
                  class="el-icon-arrow-right"
                  style="color: #959dad; font-weight: bold"
                  @click="nextDayFun"
                ></i>
              </div>
            </div>
            <div
              class="calendar-org-week-wrap"
              v-for="(user, userIndex) in userArr"
              :key="userIndex"
            >
              <h3>
                <p>{{ user.name }}</p>
              </h3>
            </div>
          </div>
          <!-- 終日の場合-->
          <div class="day-mine-wrap clearfix" style="cursor: pointer">
            <div
              class="mine-wrap-comment"
              :style="{ height: maxHeight + 'px' }"
            ></div>
            <div
              v-for="(user, userIndex) in userArr"
              :key="userIndex"
              :style="{ height: maxHeight + 'px' }"
            >
              <div class="schedul-popover-content">
                <a
                  class="add-content"
                  @click="createAllDay(todayDate, user.id)"
                ></a>
                <div class="calendar-day-sckedule-wrap">
                  <div
                    class="calendar-sckedule clearfix btns"
                    v-for="(i, index) in allDaySort(user.id)"
                    :key="index"
                    v-if="index < schLength"
                    :style="{
                      backgroundColor: getColor(i.type),
                      color: getColorWord(i.type),
                      top: index * 20 + 'px',
                      margin: '2px 5px',
                      width: i.flag ? getWidth(i) : 'calc(100% - 10px)',
                    }"
                  >
                    <ScheduleDetailModel
                      :day="getDay()"
                      :schedule="i"
                      @reload="today"
                      pageName="scheduleDay"
                      @showHandle="showHandle"
                      @hideHandle="hideHandle"
                      :editable="isEditable(i)"
                    >
                      <span
                        v-if="
                          timeFormat(i.st_datetime) !==
                          timeFormat(i.ed_datetime)
                        "
                        :style="{
                          color: getColorWord(i.type),
                          whiteSpace: 'nowrap',
                          textOverflow: 'ellipsis',
                        }"
                        >{{ timeFormat(i.st_datetime) }}~{{
                          timeFormat(i.ed_datetime)
                        }}</span
                      >
                      <span
                        v-else
                        :style="{
                          color: getColorWord(i.type),
                          whiteSpace: 'nowrap',
                          textOverflow: 'ellipsis',
                        }"
                        >{{ timeFormat(i.st_datetime) }}&nbsp&nbsp{{
                          i.subject
                        }}</span
                      >
                    </ScheduleDetailModel>
                  </div>
                </div>

                <div class="calendar-schedule-others" style="margin-top: 260px">
                  <el-popover
                    placement="top"
                    trigger="click"
                    :visible-arrow="false"
                  >
                    <div class="calendar-schedule-others-day"></div>
                    <div class="schedule-others week">
                      {{ getWeekName(todayDate) }}
                    </div>
                    <div class="schedule-others day">
                      {{ new Date(todayDate).getDate() }}
                    </div>
                    <div
                      v-for="(i, index) in allDaySort(user.id)"
                      :key="index"
                      v-if="index > schLength - 1"
                      :style="{
                        backgroundColor: getColor(i.type),
                        marginTop: '2px',
                      }"
                    >
                      <ScheduleDetailModel
                        class="schdeuledetail-other"
                        :day="getDay()"
                        :schedule="i"
                        :editable="isEditable(i)"
                        pageName="scheduleDay"
                        @reload="today"
                      >
                        <span :style="{ color: getColorWord(i.type) }"
                          >終日&nbsp;{{ i.subject }}</span
                        >
                      </ScheduleDetailModel>
                    </div>
                    <div
                      slot="reference"
                      v-show="allDaySort(user.id).length - schLength > 0"
                    >
                      他{{ allDaySort(user.id).length - schLength }}件
                    </div>
                  </el-popover>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section
          v-on:mouseleave="mouseleave"
          :style="{
            top: maxHeight + 60 + 'px',
            height: ' calc(100vh - 200px)',
            position: ' absolute',
            overflowY: 'auto',
          }"
        >
          <section
            class="schedule-day-main-wrap clearfix"
            style="display: block"
          >
            <div
              style="
                display: flex;
                border-top: 1px solid rgb(112, 112, 112);
                margin-top: 10px;
              "
              class="schedule-day-bottom-others"
            >
              <div
                style="width: 200px;height:30px;border-right: 1px solid #E5E5E5;"
              ></div>
              <div
                :key="userIndex"
                v-for="(user, userIndex) in userArr"
              >
                <div
                   class="schedule-day-bottom-others-content"
                  style="width: 200px"
                  v-if="normalDaySort(user.id, 20).length > 0"
                >
                  <el-popover
                    placement="top"
                    trigger="click"
                    :visible-arrow="false"
                  >
                    <div class="schedule-others week">
                      {{ getWeekName(todayDate) }}
                    </div>
                    <div class="schedule-others day">
                      {{ new Date(todayDate).getDate() }}
                    </div>
                    <div
                      v-for="(i, index) in normalDaySort(user.id, 20)"
                      :key="index"
                      :style="{
                        backgroundColor: getColor(i.type),
                        marginTop: '2px',
                      }"
                    >
                      <ScheduleDetailModel
                        class="schdeuledetail-other"
                        :day="getDay()"
                        :schedule="i"
                        pageName="scheduleDay"
                        :editable="isEditable(i)"
                        @reload="today"
                      >
                        {{ i.startTime }}-{{ i.finishTime }}&nbsp;{{
                          i.subject
                        }}
                      </ScheduleDetailModel>
                    </div>
              
                      <div
                        slot="reference"
                        style="
                          font-weight: bold;
                          color: #3e3d42;
                          cursor: pointer;
                          margin-left: 10px;
                        "
                      >
                        他{{ normalDaySort(user.id, 20).length }}件
                      </div>
                  </el-popover>
                </div>

                <div
                  v-else
                  class="schedule-day-bottom-others-content"
                  style="width: 200px"
                >
                  <div
                    style="
                      font-weight: bold;
                      color: #3e3d42;
                      cursor: pointer;
                      margin-left: 10px;
                    "
                  >
                    他0件
                  </div>
                </div>
              </div>
            </div>
            <div style="display: flex">
              <div class="calendar-week-wrap">
                <div
                  class="calendar-time calendar-daytime"
                  style="border-right: 0;"
                  v-for="(dayTime, index) in dayTimes"
                  :key="index"
                  v-if="dayTime.substring(3, 5) === '00'"
                >
                  <div class="calendar-daytime-time">{{ dayTime }}</div>
                  <div class="calendar-daytime-border"></div>
                </div>
              </div>
              <div
                class="calendar-week-wrap"
                :key="userIndex"
                v-for="(user, userIndex) in userArr"
              >
                <div
                  class="day_noborder"
                  v-for="(dayTime, index) in dayTimes"
                  :key="index"
                  :id="index + 24 * userIndex"
                  v-on:mousedown="
                    mousedown(
                      weekDays[0],
                      dayTime,
                      index,
                      index + 24 * userIndex
                    )
                  "
                  v-on:mouseover="
                    mouseover(
                      weekDays[0],
                      dayTime,
                      index,
                      index + 24 * userIndex
                    )
                  "
                  v-on:mouseup="mouseup(index, user.id, userIndex)"
                >
                  　
                  <!--スケジュールボックスの表示-->
                </div>
                <WeekScheduleCol
                  v-if="schResorted"
                  :schResorted="daySort(user.id)"
                  :status="status"
                  :day="getDay()"
                  @reload="today"
                  :col="0"
                  pageName="scheduleDay"
                  @showHandle="showHandle"
                  @hideHandle="hideHandle"
                  :loginUserId="mineId"
                  :mineId="mineId"
                ></WeekScheduleCol>
              </div>
            </div>
          </section>
          <ScheduleModal
            v-if="showScheduleModal"
            @setModalMonth="getModalMonth"
            @closeScheduleModal="closeScheduleModal"
            :emitCurrentDate="currentDate"
          ></ScheduleModal>
        </section>
      </el-container>
    </el-container>

    <div class="invite-form">
      <div class="pagination-center" v-if="msg !== ''">
        {{ msg }}
      </div>
    </div>
  </div>
</template>
<script>
import Calendar from "../../mixins/Calendar";
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
    ScheduleDetailModel,
  },
  mixins: [Calendar, Messages],
  data() {
    return {
      status: false,
      delDialog: false,
      updateDialog: false,
      flag: true,
      mineId: 0,
      loginUserId: 0,
      showScheduleModal: false,
      currentDate: new Date(), // 現在週の日付
      monthDate: new Date(), // カレンダーの日付
      dayTimes: [], // 時間リスト
      schedules: [], // スケジュールデータ
      scheduleAllDay: [],
      scheduleMap: {},
      showDetailModal: false, // 詳細モーダル表示フラグ
      postDetail: null, // 詳細モーダルへ渡すデータ
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
      startId: "",
      endId: "",
      selectArr: [],
      pageName: "scheduleWeek",
      emitCurrentDate: "",
      msg: "",
      userArr: [],
      todayDate: Calendar.dateFormat(new Date(), "yyyy/MM/dd"),
      schLength: 20,
      schLength2: 20,
      maxHeight: 100,
    };
  },
  methods: {
    allDayScheduleArr(dataArr) {
      let res = [];
      if (dataArr.length > 0) {
        for (let i = 0; i < dataArr.length; i++) {
          for (let j = 0; j < dataArr[i].length; j++) {
            res.push(dataArr[i][j]);
          }
        }
      }
      return res;
    },

    spliceScheduleArr(dataArr, number) {
      if (dataArr && dataArr.sch_items_sorted.length > number) {
        let res = dataArr.sch_items_sorted.slice(
          number,
          dataArr.sch_items_sorted.length
        );
        return res;
      } else {
        let res = [];
        return res;
      }
    },
    daySort(index) {
      let data = {};
      data.cols_nums = this.schResorted.cols_nums;
      data.date_str = this.schResorted.date_str;
      data.sch_items_sorted = [];
      let val = [];
      let sort = this.schResorted.sch_items_sorted[0];
      for (let i in sort) {
        let part = sort[i].participantUsers;
        for (let j in part) {
          if (part[j].user_id === index) {
            val.push(sort[i]);
          }
        }
      }
      // フォーマットデータは、「WeekScheduleCol」ページに提供して使用します。
      if (val && val.length > 0) {
        let delIndexArrTmp = [];
        // 終日のみ
        for (let scheduleIndex in val) {
          if (
            val[scheduleIndex]["all_day"] &&
            val[scheduleIndex]["all_day"] == 1
          ) {
            delIndexArrTmp.push(scheduleIndex);
          }
        }
        for (
          let scheduleIndex = delIndexArrTmp.length - 1;
          scheduleIndex >= 0;
          scheduleIndex--
        ) {
          val.splice(delIndexArrTmp[scheduleIndex], 1);
        }
        // その他
        if (val && val.length > 0) {
          while (true) {
            let backData = this.sortDayForWeekScheduleColPage(val);
            data.sch_items_sorted.push(backData[1]);
            val = backData[0];
            if (val.length == 0) {
              break;
            }
          }
        }
      }
      data.cols_nums = data.sch_items_sorted.length;
      return data;
    },
    // ルール:時間が重複しない場合は、同じグループに保存します。そうでなければ、次のグループに入れます
    sortDayForWeekScheduleColPage(scheduleArr) {
      let delIndexArr = [0];
      let backScheduleArr = [scheduleArr[0]];
      let firstSchedule = scheduleArr[0];
      for (
        let scheduleIndex = 1;
        scheduleIndex < scheduleArr.length;
        scheduleIndex++
      ) {
        if (scheduleArr[scheduleIndex].startTime >= firstSchedule.finishTime) {
          backScheduleArr.push(scheduleArr[scheduleIndex]);
          delIndexArr.push(scheduleIndex);
          firstSchedule = scheduleArr[scheduleIndex];
        }
      }
      for (
        let scheduleIndex = delIndexArr.length - 1;
        scheduleIndex >= 0;
        scheduleIndex--
      ) {
        scheduleArr.splice(delIndexArr[scheduleIndex], 1);
      }
      // scheduleArr:次のループに入るデータ; backScheduleArr:一回でクリアしたら、「data」に追加するデータが必要です。
      return [scheduleArr, backScheduleArr];
    },
    allDaySort(index) {
      let data = [];
      let todayTmp = Calendar.dateFormat(this.currentDate, "yyyy-MM-dd");
      for (let i in this.scheduleAllDay) {
        let scheduleSubs = this.scheduleAllDay[i].schedule_subs;
        if(this.scheduleAllDay[i].repeat_kbn == 4){
          let continueFlag = true;
          for (let j in scheduleSubs) {
            if (scheduleSubs[j].s_date === todayTmp) {
              continueFlag = false;
              break;
            }
          }
          if(continueFlag){
            continue;
          }
        }
        let part = this.scheduleAllDay[i].schedule_participants;
        for (let j in part) {
          if (part[j].user_id === index) {
            data.push(this.scheduleAllDayCleansing(this.scheduleAllDay[i]));
          }
        }
        //#5271,組織・日-終日,場所
        if (this.scheduleAllDay[i].address === undefined){
          this.scheduleAllDay[i].address = this.scheduleAllDay[i].location;
        }
      }
      return data;
    },
    // index:ユーザーID;count:20個後
    normalDaySort(index, count) {
      let backData = [];
      let data = this.daySort(index)["sch_items_sorted"];
      if (data.length > count) {
        for (let i = 20; i < data.length; i++) {
          for (let j in data[i]) {
            backData.push(data[i][j]);
          }
        }
      }
      return backData;
    },
    // データの書式設定 ScheduleDetailModelに使う
    scheduleAllDayCleansing(scheduleAllDayTmp) {
      // 登録者 更新者
      let scheduleTmp = scheduleAllDayTmp;
      let createUserTmp = scheduleTmp.create_by;
      let updateUserTmp = scheduleTmp.update_by;
      scheduleTmp.st_datetime = Calendar.dateFormat(
        scheduleTmp.st_datetime,
        "yyyy/MM/dd"
      );
      scheduleTmp.ed_datetime = Calendar.dateFormat(
        scheduleTmp.ed_datetime,
        "yyyy/MM/dd"
      );
      scheduleTmp.createDate = scheduleTmp.created_at;
      scheduleTmp.updateDate = scheduleTmp.updated_at;
      scheduleTmp.createUser = createUserTmp;
      scheduleTmp.updateUser = updateUserTmp;
      // 参加者
      scheduleTmp.participantUsers = scheduleTmp.schedule_participants;
      
      let subsTmp = scheduleAllDayTmp.schedule_subs;
      scheduleTmp.subId = 0;
      if(subsTmp){
        let todayTmp = Calendar.dateFormat(this.currentDate, "yyyy-MM-dd");
        for (let i in subsTmp) {
          if(subsTmp[i].s_date === todayTmp){
            scheduleTmp.subId = subsTmp[i].id;
          }
        }
      }
      return scheduleTmp;
    },
    getMaxHeight() {
      let maxCount = 0;
      let maxHeightTmp = 0;
      for (let i in this.userArr) {
        let data = this.allDaySort(this.userArr[i].id);
        if (data.length >= 20) {
          maxCount = 20;
          break;
        }
        if (data.length > maxCount) {
          maxCount = data.length;
        }
      }
      maxHeightTmp = maxCount * 17 + 10;
      return maxHeightTmp > 100 ? maxHeightTmp : 100;
    },
    getDay() {
      let data = [];
      data.date = this.todayDate;
      return data;
    },
    getWidth(info) {
      return "calc(" + info.count * 100 + "% " + " - 10px)";
    },
    getSchInfo(num, date, id) {
      if (!this.scheduleAllDay[id]) {
        return false;
      }
      if (!this.scheduleAllDay[id][num]) {
        return false;
      }
      if (!this.scheduleAllDay[id][num][this.formatDate(date)]) {
        return false;
      }
      return this.scheduleAllDay[id][num][this.formatDate(date)][0];
    },
    timeFormat(date) {
      return Calendar.dateFormat(date, "MM月dd日");
    },

    getSchCount(data) {
      let count = 0;
      if (data) {
        count = data.length;
      }
      return count;
    },
    isEditable: function (schedule) {
      if (schedule.created_user_id !== undefined && schedule.created_user_id === this.loginUserId) {
        return true;
      }
      if (schedule.created_by !== undefined && schedule.created_by === this.loginUserId){
        return true;
      }
      if (schedule.schedule_participants) {
        for (let i in schedule.schedule_participants) {
          if (schedule.schedule_participants[i].user_id === this.loginUserId) {
            return true;
          }
        }
      }

      return false;
    },
    getmsg() {
      if (window.successMsg) {
        this.msg = window.successMsg;
        setTimeout(() => {
          this.msg = "";
          window.successMsg = "";
        }, 3000);
      }
    },
    touchstart(e) {
      let dom = e.currentTarget;
      $(dom).removeClass("schedule-week-top");
      if (e.pageX || e.pageY) {
        this.topY = e.pageY;
      }
    },
    beTop(e) {
      let dom = e.currentTarget;
      $(dom).addClass("schedule-week-top");
    },
    unbeTop(e) {
      let dom = e.currentTarget;
      $(dom).removeClass("schedule-week-top");
    },
    getColorWord(type) {
      let color = "#FFF";
      if (type === 0) {
        color = "#0F0F0F";
      } else {
        color = "#FFF";
      }
      return color;
    },
    showHandle: function () {
      this.status = true;
    },
    hideHandle: function () {
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
      this.createDate.st_day = this.todayDate;
      this.createDate.st_hour = timeArr[0];
      this.createDate.st_minute = timeArr[1];
      this.createDate.ed_day = this.todayDate;
      this.createDate.ed_hour = timeArr[0];
      this.createDate.ed_minute = timeArr[1];
      this.startId = id;
    },
    mouseover(day, dayTime, index, id) {
      if (this.selectFlag) {
        let timeArr = dayTime.split(":");
        this.createDate.ed_day = this.todayDate;
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
        if (
          new Date(this.createDate.ed_day).getTime() >=
            new Date(this.createDate.st_day).getTime() &&
          parseInt(this.createDate.ed_hour) >= parseInt(this.createDate.st_hour)
        ) {
          startCol = parseInt(this.startId / 24);
          endCol = parseInt(this.endId / 24);
          startRow = this.startId % 24;
          endRow = this.endId % 24;
        } else if (
          new Date(this.createDate.ed_day).getTime() <
            new Date(this.createDate.st_day).getTime() &&
          parseInt(this.createDate.ed_hour) > parseInt(this.createDate.st_hour)
        ) {
          startCol = parseInt(this.endId / 24);
          endCol = parseInt(this.startId / 24);
          startRow = this.startId % 24;
          endRow = this.endId % 24;
        } else if (
          new Date(this.createDate.ed_day).getTime() <=
            new Date(this.createDate.st_day).getTime() &&
          parseInt(this.createDate.ed_hour) <= parseInt(this.createDate.st_hour)
        ) {
          startCol = parseInt(this.endId / 24);
          endCol = parseInt(this.startId / 24);
          startRow = this.endId % 24;
          endRow = this.startId % 24;
        } else if (
          new Date(this.createDate.ed_day).getTime() >
            new Date(this.createDate.st_day).getTime() &&
          parseInt(this.createDate.ed_hour) < parseInt(this.createDate.st_hour)
        ) {
          startCol = parseInt(this.startId / 24);
          endCol = parseInt(this.endId / 24);
          startRow = this.endId % 24;
          endRow = this.startId % 24;
        }
        for (let i = startCol; i <= endCol; i++) {
          for (let j = startRow; j <= endRow; j++) {
            document.getElementById(i * 24 + j).style.background =
              "cornflowerblue";
            this.selectArr.push(i * 24 + j);
          }
        }
      }
    },
    mouseup(index, user_id, endId) {
      if (this.status) {
        this.selectFlag = false;
        return false;
      }
      this.flag = true;
      this.selectFlag = false;
      this.createDate.ed_day = this.createDate.ed_day.replace(/\//g, "-");
      this.createDate.st_day = this.createDate.st_day.replace(/\//g, "-");
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
              if (
                new Date(this.createDate.ed_day).getTime() >=
                  new Date(this.createDate.st_day).getTime() &&
                parseInt(this.createDate.ed_hour) >=
                  parseInt(this.createDate.st_hour)
              ) {
              } else if (
                new Date(this.createDate.ed_day).getTime() <
                  new Date(this.createDate.st_day).getTime() &&
                parseInt(this.createDate.ed_hour) >
                  parseInt(this.createDate.st_hour)
              ) {
                let date = this.createDate.ed_day;
                this.createDate.ed_day = this.createDate.st_day;
                this.createDate.st_day = date;
              } else if (
                new Date(this.createDate.ed_day).getTime() <=
                  new Date(this.createDate.st_day).getTime() &&
                parseInt(this.createDate.ed_hour) <=
                  parseInt(this.createDate.st_hour)
              ) {
                let date = this.createDate.ed_day;
                this.createDate.ed_day = this.createDate.st_day;
                this.createDate.st_day = date;
                let time = this.createDate.ed_hour;
                this.createDate.ed_hour = this.createDate.st_hour;
                this.createDate.st_hour = time;
              } else if (
                new Date(this.createDate.ed_day).getTime() >
                  new Date(this.createDate.st_day).getTime() &&
                parseInt(this.createDate.ed_hour) <
                  parseInt(this.createDate.st_hour)
              ) {
                let time = this.createDate.ed_hour;
                this.createDate.ed_hour = this.createDate.st_hour;
                this.createDate.st_hour = time;
              }
              this.createDate.ed_hour = (
                Array(2).join("0") +
                (parseInt(this.createDate.ed_hour) + 1)
              ).slice(-2);
              if (this.createDate.ed_hour == "24") {
                this.createDate.ed_hour = "23";
                this.createDate.ed_minute = "55";
              }
              //#5175 Question No2: delete param "participantLists", use param "id"(otherId in ScheduleCreate.vue) to create participants
              this.$router.push({
                path: "/schedule/create",
                query: {
                  //#5175 Question No.1,set createDate as a date string,add parameter transfer time
                  createDate: this.createDate.st_day,
                  stH: this.createDate.st_hour,
                  stM: this.createDate.st_minute,
                  edH: this.createDate.ed_hour,
                  edM: this.createDate.ed_minute,
                  id: user_id,
                  pageName: "scheduleDay"
                },
              });
            }
          }
        }, 100);
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
      this.currentDate = Calendar.getOtherMonth(this.monthDate, "before");
      this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
      this.monthDate = Calendar.getOtherMonth(this.monthDate, "before");
      this.initMonth();
      this.initWeek();
    },
    nextMonth: function () {
      this.currentDate = Calendar.getOtherMonth(this.monthDate, "next");
      this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
      this.monthDate = Calendar.getOtherMonth(this.monthDate, "next");
      this.initMonth();
      this.initWeek();
    },
    beforeDay: function () {
      this.currentDate = Calendar.addDay(this.currentDate, -1);
      this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
      this.monthDate = this.currentDate;
      this.initWeek();
      this.initMonth();
    },
    nextDayFun: function () {
      this.currentDate = Calendar.addDay(this.currentDate, 1);
      this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
      this.monthDate = this.currentDate;
      this.initWeek();
      this.initMonth();
    },
    showToday() {
      return Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
    },
    today: function (type) {
      //1:今日;other:操作の当日
      this.currentDate = new Date();
      this.monthDate = this.currentDate;
      if (type === 1) {
        this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
      }
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
        if (
          schedules[i].all_day === 1 ||
          (schedules[i].repeat_type == 0 &&
            schedules[i].st_span != schedules[i].ed_span)
        ) {
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
          let startTime = "00:00";
          let finishTime = "23:59";
          // 終日ではない場合
          if (schedule.all_day !== 1) {
            startTime = Calendar.dateFormat(schedule.st_datetime, "hh:mm");
            finishTime = Calendar.dateFormat(schedule.ed_datetime, "hh:mm");
          }
          info.startTime = startTime;
          info.finishTime = finishTime;

          // 前月のデータ
          let firstDayOfCalendar = Calendar.getFirstDayOfWeek(
            Calendar.getFirstDayOfMonth(this.currentDate)
          );
          if (
            info.stDate <= firstDayOfCalendar &&
            info.edDate >= firstDayOfCalendar
          ) {
            info.day = firstDayOfCalendar;
          }

          let dayDiff = Calendar.dateDiff(
            schedule.st_datetime,
            schedule.ed_datetime
          );
          if (schedule.schedule_subs && schedule.schedule_subs.length > 0) {
            this.addSubsToScheduleMap(info, schedule.schedule_subs);
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
      return obj1.startTime < obj2.startTime
        ? -1
        : obj1.startTime > obj2.startTime
        ? 1
        : 0;
    },
    curr(date) {
      this.currentDate = new Date(date);
      this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
      this.monthDate = this.currentDate;
      this.initWeek();
      this.initMonth();
    },
    createAllDay(date, id) {
      //#5175 Question No.1,set createDate as a date string
      this.$router.push({
        path: "/schedule/create",
        query: {
          createDate: Calendar.dateFormat(date, "yyyy-MM-dd"),
          id: id,
          pageName: "scheduleDay"
        },
      })
    },
    //六曜
    sixWeekDay: function (date) {
      let dt = Calendar.dateFormat(date);
      if (this.japaneseDate && this.japaneseDate[dt]) {
        return this.japaneseDate[dt].six_weekday;
      }
      return "";
    },
    getHoliday(date) {
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
      let loading = this.$loading({
        lock: true,
        background: "rgba(0, 0, 0, 0.7)",
      });
      let errMessage = this.commonMessage.error.scheduleList;

      axios
        .post("/api/indexToday", {
          stDate: this.todayDate,
          // edDate: this.lastYear + '/' + this.lastMonth + '/' + this.nextDay,
        })
        .then((res) => {
          this.japaneseDate = res.data.japaneseDate;
          this.processScheduleResult(res.data.schedule, res.data.userId);
          this.scheduleAllDay = res.data.scheduleAllDay;
          this.schResorted = res.data.sorted;
          if (!this.mineId) {
            this.mineId = res.data.userId;
            this.loginUserId = res.data.userId;
          }
          this.userArr = res.data.userArr;
          this.maxHeight = this.getMaxHeight();
          loading.close();
        })
        .catch((error) => {
          loading.close();
          this.$alert(errMessage, { showClose: false });
        });
    },

    getColor(type) {
      let color = "#e5e5e5";
      if (type === 1) {
        color = "#ca6ebc";
      } else if (type === 5) {
        color = "#8fc31f";
      } else if (type === 2) {
        color = "#7ecef4";
      } else if (type === 4) {
        color = "#f8b551";
      } else if (type === 3) {
        color = "#097c25";
      } else if (type === 6) {
        color = "#ff5e3c";
      } else if (type === 0) {
        color = "#e5e5e5";
      }
      return color;
    },

    // 空チェック
    isEmpty: function (obj) {
      return typeof obj == "undefined" || obj == null || obj == "";
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
        arr.push(
          (hours < 10 ? "0" + hours : hours) +
            ":" +
            (minutes < 10 ? "0" + minutes : minutes)
        );
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
      this.currentDate = new Date(data);
      this.monthDate = this.currentDate;
      this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");

      this.initWeek();
      this.initMonth();
    },
  },
  computed: {
    firstDayOfWeek: function () {
      return Calendar.getFirstDayOfWeek(this.currentDate);
    },
    year: function () {
      return Calendar.getYear(this.firstDayOfWeek);
    },
    month: function () {
      return Calendar.getMonth(this.monthDate);
    },
    weekMonth: function () {
      return Calendar.getMonth(this.firstDayOfWeek);
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
    nextDay: function () {
      return Calendar.addDay(this.firstDayOfWeek, 6).getDate();
    },
  },
  created() {
    if (this.$route.query.createDate) {
      this.currentDate = new Date(this.$route.query.createDate);
      this.todayDate = Calendar.dateFormat(this.currentDate, "yyyy/MM/dd");
      this.monthDate = new Date(this.$route.query.createDate);
    }
    this.dayTimes = this.getHmTimeList(0);
    this.initMonth();
    this.fetch();
    this.getmsg();
  },
  watch: {
    currentDate: function () {
      this.emitCurrentDate = Calendar.dateFormat(
        this.currentDate,
        "yyyy-MM-dd"
      );
    },
  },
};
</script>

<template>
  <div>
    <div v-if="schResorted['cols_nums'] >= 1"
         v-for="item in schResorted['sch_items_sorted'][0]">
      <ScheduleDetailModel v-if="!item.all_day" :col="col" :topY="topY" :day="day" :schedule="item" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item)">
        <div :style="{height:(item.span_rate)+'%',top:(item.escape_rate)+'%',backgroundColor:getColor(item.type),color:getColorWord(item.type)}"
             class="schdeule-week-common schedule-one schedule-border" @mouseenter="beTop" @mouseleave="unbeTop" @click="touchstart"
        >
        <span>
           <p :style="{color:getColorWord(item.type)}">{{item.startTime}}-{{item.finishTime}}</p>
          <p :style="{color:getColorWord(item.type)}">{{item.subject}}</p>
        </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 2"
         v-for="item1 in schResorted['sch_items_sorted'][1]">
      <ScheduleDetailModel v-if="!item1.all_day" :col="col" :topY="topY" :day="day" :schedule="item1" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item1)">
        <div :style="{height:(item1.span_rate)+'%',top:(item1.escape_rate)+'%',backgroundColor:getColor(item1.type),color:getColorWord(item1.type),width:(85.5)+'%',left:(4.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item1.type)}">{{item1.startTime}}-{{item1.finishTime}}</p>
          <p :style="{color:getColorWord(item1.type)}">{{item1.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 3"
         v-for="item2 in schResorted['sch_items_sorted'][2]">
      <ScheduleDetailModel v-if="!item2.all_day" :col="col" :topY="topY" :day="day" :schedule="item2" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item2)">
        <div :style="{height:(item2.span_rate)+'%',top:(item2.escape_rate)+'%',backgroundColor:getColor(item2.type),color:getColorWord(item2.type),width:(81)+'%',left:(9)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item2.type)}">{{item2.startTime}}-{{item2.finishTime}}</p>
          <p :style="{color:getColorWord(item2.type)}">{{item2.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 4"
         v-for="item3 in schResorted['sch_items_sorted'][3]">
      <ScheduleDetailModel  v-if="!item3.all_day" :col="col" :topY="topY" :day="day" :schedule="item3" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item3)">
        <div :style="{height:(item3.span_rate)+'%',top:(item3.escape_rate)+'%',backgroundColor:getColor(item3.type),color:getColorWord(item3.type),width:(76.5)+'%',left:(13.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item3.type)}">{{item3.startTime}}-{{item3.finishTime}}</p>
          <p :style="{color:getColorWord(item3.type)}">{{item3.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 5"
         v-for="item4 in schResorted['sch_items_sorted'][4]">
      <ScheduleDetailModel v-if="!item4.all_day" :col="col" :topY="topY" :day="day" :schedule="item4" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item4)">
        <div :style="{height:(item4.span_rate)+'%',top:(item4.escape_rate)+'%',backgroundColor:getColor(item4.type),color:getColorWord(item4.type),width:(72)+'%',left:(18)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span >
              <p :style="{color:getColorWord(item4.type)}">{{item4.startTime}}-{{item4.finishTime}}</p>
          <p :style="{color:getColorWord(item4.type)}">{{item4.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 6"
         v-for="item5 in schResorted['sch_items_sorted'][5]">
      <ScheduleDetailModel  v-if="!item5.all_day" :col="col" :topY="topY" :day="day" :schedule="item5" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item5)">
        <div :style="{height:(item5.span_rate)+'%',top:(item5.escape_rate)+'%',backgroundColor:getColor(item5.type),color:getColorWord(item5.type),width:(67.5)+'%',left:(22.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item5.type)}">{{item5.startTime}}-{{item5.finishTime}}</p>
          <p :style="{color:getColorWord(item5.type)}">{{item5.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 7"
         v-for="item6 in schResorted['sch_items_sorted'][6]">
      <ScheduleDetailModel v-if="!item6.all_day"  :col="col" :topY="topY" :day="day" :schedule="item6" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item6)">
        <div :style="{height:(item6.span_rate)+'%',top:(item6.escape_rate)+'%',backgroundColor:getColor(item6.type),color:getColorWord(item6.type),width:(63)+'%',left:(27)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item6.type)}">{{item6.startTime}}-{{item6.finishTime}}</p>
          <p :style="{color:getColorWord(item6.type)}">{{item6.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 8"
         v-for="item7 in schResorted['sch_items_sorted'][7]">
      <ScheduleDetailModel v-if="!item7.all_day"  :col="col" :topY="topY" :day="day" :schedule="item7" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item7)">
        <div :style="{height:(item7.span_rate)+'%',top:(item7.escape_rate)+'%',backgroundColor:getColor(item7.type),color:getColorWord(item7.type),width:(58.5)+'%',left:(31.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item7.type)}">{{item7.startTime}}-{{item7.finishTime}}</p>
          <p :style="{color:getColorWord(item7.type)}">{{item7.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 9"
         v-for="item8 in schResorted['sch_items_sorted'][8]">
      <ScheduleDetailModel v-if="!item8.all_day"  :col="col" :topY="topY" :day="day" :schedule="item8" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item8)">
        <div :style="{height:(item8.span_rate)+'%',top:(item8.escape_rate)+'%',backgroundColor:getColor(item8.type),color:getColorWord(item8.type),width:(54)+'%',left:(36)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item8.type)}">{{item8.startTime}}-{{item8.finishTime}}</p>
          <p :style="{color:getColorWord(item8.type)}">{{item8.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 10"
         v-for="item9 in schResorted['sch_items_sorted'][9]">
      <ScheduleDetailModel v-if="!item9.all_day"  :col="col" :topY="topY" :day="day" :schedule="item9" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item9)">
        <div :style="{height:(item9.span_rate)+'%',top:(item9.escape_rate)+'%',backgroundColor:getColor(item9.type),color:getColorWord(item9.type),width:(49.5)+'%',left:(40.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item9.type)}">{{item9.startTime}}-{{item9.finishTime}}</p>
          <p :style="{color:getColorWord(item9.type)}">{{item9.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 11"
         v-for="item10 in schResorted['sch_items_sorted'][10]">
      <ScheduleDetailModel v-if="!item10.all_day"  :col="col" :topY="topY" :day="day" :schedule="item10" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item10)">
        <div :style="{height:(item10.span_rate)+'%',top:(item10.escape_rate)+'%',backgroundColor:getColor(item10.type),color:getColorWord(item10.type),width:(45)+'%',left:(45)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item10.type)}">{{item10.startTime}}-{{item10.finishTime}}</p>
          <p :style="{color:getColorWord(item10.type)}">{{item10.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 12"
         v-for="item11 in schResorted['sch_items_sorted'][11]">
      <ScheduleDetailModel v-if="!item11.all_day"  :col="col" :topY="topY" :day="day" :schedule="item11" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item11)">
        <div :style="{height:(item11.span_rate)+'%',top:(item11.escape_rate)+'%',backgroundColor:getColor(item11.type),color:getColorWord(item11.type),width:(40.5)+'%',left:(49.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item11.type)}">{{item11.startTime}}-{{item11.finishTime}}</p>
          <p :style="{color:getColorWord(item11.type)}">{{item11.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 13"
         v-for="item12 in schResorted['sch_items_sorted'][12]">
      <ScheduleDetailModel v-if="!item12.all_day"  :col="col" :topY="topY" :day="day" :schedule="item12" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item12)">
        <div :style="{height:(item12.span_rate)+'%',top:(item12.escape_rate)+'%',backgroundColor:getColor(item12.type),color:getColorWord(item12.type),width:(36)+'%',left:(54)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item12.type)}">{{item12.startTime}}-{{item12.finishTime}}</p>
          <p :style="{color:getColorWord(item12.type)}">{{item12.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 14"
         v-for="item13 in schResorted['sch_items_sorted'][13]">
      <ScheduleDetailModel v-if="!item13.all_day"  :col="col" :topY="topY" :day="day" :schedule="item13" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item13)">
        <div :style="{height:(item13.span_rate)+'%',top:(item13.escape_rate)+'%',backgroundColor:getColor(item13.type),color:getColorWord(item13.type),width:(31.5)+'%',left:(58.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item13.type)}">{{item13.startTime}}-{{item13.finishTime}}</p>
          <p :style="{color:getColorWord(item13.type)}">{{item13.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 15"
         v-for="item14 in schResorted['sch_items_sorted'][14]">
      <ScheduleDetailModel v-if="!item14.all_day"  :col="col" :topY="topY" :day="day" :schedule="item14" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item14)">
        <div :style="{height:(item14.span_rate)+'%',top:(item14.escape_rate)+'%',backgroundColor:getColor(item14.type),color:getColorWord(item14.type),width:(27)+'%',left:(63)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item14.type)}">{{item14.startTime}}-{{item14.finishTime}}</p>
          <p :style="{color:getColorWord(item14.type)}">{{item14.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 16"
         v-for="item15 in schResorted['sch_items_sorted'][15]">
      <ScheduleDetailModel v-if="!item15.all_day"  :col="col" :topY="topY" :day="day" :schedule="item15" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item15)">
        <div :style="{height:(item15.span_rate)+'%',top:(item15.escape_rate)+'%',backgroundColor:getColor(item15.type),color:getColorWord(item15.type),width:(22.5)+'%',left:(67.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item15.type)}">{{item15.startTime}}-{{item15.finishTime}}</p>
          <p :style="{color:getColorWord(item15.type)}">{{item15.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 17"
         v-for="item16 in schResorted['sch_items_sorted'][16]">
      <ScheduleDetailModel v-if="!item16.all_day"  :col="col" :topY="topY" :day="day" :schedule="item16" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item16)">
        <div :style="{height:(item16.span_rate)+'%',top:(item16.escape_rate)+'%',backgroundColor:getColor(item16.type),color:getColorWord(item16.type),width:(18)+'%',left:(72)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item16.type)}">{{item16.startTime}}-{{item16.finishTime}}</p>
          <p :style="{color:getColorWord(item16.type)}">{{item16.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 18"
         v-for="item17 in schResorted['sch_items_sorted'][17]">
      <ScheduleDetailModel v-if="!item17.all_day"  :col="col" :topY="topY" :day="day" :schedule="item17" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item17)">
        <div :style="{height:(item17.span_rate)+'%',top:(item17.escape_rate)+'%',backgroundColor:getColor(item17.type),color:getColorWord(item17.type),width:(13.5)+'%',left:(76.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item17.type)}">{{item17.startTime}}-{{item17.finishTime}}</p>
          <p :style="{color:getColorWord(item17.type)}">{{item17.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 19"
         v-for="item18 in schResorted['sch_items_sorted'][18]">
      <ScheduleDetailModel v-if="!item18.all_day"  :col="col" :topY="topY" :day="day" :schedule="item18" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item18)">
        <div :style="{height:(item18.span_rate)+'%',top:(item18.escape_rate)+'%',backgroundColor:getColor(item18.type),color:getColorWord(item18.type),width:(9)+'%',left:(81)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item18.type)}">{{item18.startTime}}-{{item18.finishTime}}</p>
          <p :style="{color:getColorWord(item18.type)}">{{item18.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

    <div v-if="schResorted['cols_nums'] >= 20"
         v-for="item19 in schResorted['sch_items_sorted'][19]">
      <ScheduleDetailModel v-if="!item19.all_day"  :col="col" :topY="topY" :day="day" :schedule="item19" @reload="reload" :pageName="pageName"
                           @showHandle="showHandle" @hideHandle="hideHandle" :editable="isEditable(item19)">
        <div :style="{height:(item19.span_rate)+'%',top:(item19.escape_rate)+'%',backgroundColor:getColor(item19.type),color:getColorWord(item19.type),width:(4.5)+'%',left:(85.5)+'%'}"
             class="schdeule-week-common schedule-two schedule-border" @mouseenter="beTop" @mouseleave="unbeTop"
             @click="touchstart">
        <span>
              <p :style="{color:getColorWord(item19.type)}">{{item19.startTime}}-{{item19.finishTime}}</p>
          <p :style="{color:getColorWord(item19.type)}">{{item19.subject}}</p>
            </span>
        </div>
      </ScheduleDetailModel>
    </div>

  </div>

</template>

<script>
  import ScheduleDetailModel from "../../components/schedule/ScheduleDetailModel";

  export default {
    components: {
      ScheduleDetailModel
    },
    props: {
      schResorted: {},
      day: {},
      col: 0,
      status: {
        Type: String,
      },
      loginUserId: 0,
      mineId: 0,
      pageName: ""
    },
    data() {
      return {
        topY: 0,
        pageName: 'scheduleWeek'
      }
    },
    methods: {
      //ポップアップウィンドウをイベント詳細を発生したとき
      showHandle() {
        this.$emit('showHandle');
      },
      //イベント詳細ウィンドウが閉じたときに発生します
      hideHandle() {
        this.$emit('hideHandle');
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
        if(type === 0){
          color="#0F0F0F";
        }else{
          color="#FFF";
        }
        return color;
      },
      reload() {
        this.$emit('reload');
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
      isEditable: function (schedule) {
        if (schedule.participantUsers) {
          for (let i in schedule.participantUsers) {
            if (schedule.participantUsers[i].user_id === this.loginUserId) {
              return true;
            }
          }
        }
        if (schedule.createUser.id===this.loginUserId){
          return true;
        }
        return false;
      },
    }
  }
</script>

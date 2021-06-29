<template>
  <!--container-->
  <div class="container clearfix schedule">
    <header>
      <h1>
        <router-link to="/schedule">スケジュール</router-link>
      </h1>
      <div class="title-wrap">
        <h2 style="margin-top: 8px">スケジュール（詳細）</h2>
      </div>
    </header>
    <!--schedule-wrapper-->
    <section class="schedule-wrapper" style="margin-top: 100px">
      <div class="schedule-add-wrap clearfix">
        <dl class="clearfix">
          <dt>{{scheduleName('date')}}</dt>
          <dd>{{schedule.st_datetime}}～{{schedule.ed_datetime}}</dd>
          <dt>{{scheduleName('subject')}}</dt>
          <dd>{{schedule.subject}}</dd>
          <dt>{{scheduleName('site')}}</dt>
          <dd>{{schedule.site}}</dd>
          <dt>{{scheduleName('comment')}}</dt>
          <dd>{{schedule.comment}}</dd>
          <dt>{{scheduleName('select')}}</dt>
          <dd><span style="margin-right: 30px" v-if="schedule.repeat_kbn!=='0'">定期的</span>{{schedule.st_span}}から{{schedule.ed_span}}まで({{planMsg}})
          </dd>
          <dt>{{scheduleName('participant')}}</dt>
          <dd>
            <span v-for="participant in participantsCheckArr" :key="participant.id">{{participant.name}}さん、</span>
          </dd>
          <dt>登録者</dt>
          <dd>xxx({{schedule.created_at}})</dd>
        </dl>

        <!--</el-form>-->
        <div class="clearfix"></div>
        <div class="button-wrap clearfix" style="margin-left: 200px">
          <div class="button-lower" style="margin-right: 100px"><a @click="back">{{scheduleName('cancel')}}</a></div>
          <div class="button-lower"><a href="javascript:void(0)" @click="update">{{scheduleName('update')}}</a></div>
          <div class="button-lower"><a href="javascript:void(0)" @click="delSchedule">{{scheduleName('delete')}}</a>
          </div>
        </div>
      </div>
    </section>
    <!--/dashubord-schedule-wrapper-->
  </div>
</template>

<script>
  import ScheduleLists from '../../mixins/ScheduleLists'
  import validation from '../../validations/schedule.js'
  import Messages from "../../mixins/Messages";

  export default {
    mixins: [validation, ScheduleLists, Messages],
    data() {
      return {
        schedule: {
          id: '',
          st_datetime: '',
          ed_datetime: '',
          type: '',
          open_range: '',
          subject: '',
          comment: '',
          repeat_kbn: '',
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
          created_at: '',
        },
        planMsg: '',
        participantsCheckArr: [],
      }
    },
    methods: {
      linkMsg(repeatKbn) {
        switch (repeatKbn) {
          case "0":
            this.planMsg = this.repeatKbnName("0");
            break;
          case "1":
            this.planMsg = this.repeatKbnName("1");
            break;
          case "2":
            this.planMsg = this.repeatKbnName("2");
            break;
          case "3":
            this.planMsg = this.repeatKbnName("3");
            break;
          case "4":
            this.planMsg = this.planMsg + this.repeatKbnName("4") + " ";
            if (this.schedule.week1 === '1') {
              this.planMsg = this.planMsg + this.weekName("1") + "、";
            }
            if (this.schedule.week2 === '1') {
              this.planMsg = this.planMsg + this.weekName("2") + "、";
            }
            if (this.schedule.week3 === '1') {
              this.planMsg = this.planMsg + this.weekName("3") + "、";
            }
            if (this.schedule.week4 === '1') {
              this.planMsg = this.planMsg + this.weekName("4") + "、";
            }
            if (this.schedule.week5 === '1') {
              this.planMsg = this.planMsg + this.weekName("5") + "、";
            }
            if (this.schedule.week6 === '1') {
              this.planMsg = this.planMsg + this.weekName("6") + "、";
            }
            if (this.schedule.week7 === '1') {
              this.planMsg = this.planMsg + this.weekName("7") + "、";
            }
            this.planMsg = this.planMsg.substring(0, this.planMsg.length - 1);
            break;
        }
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
            this.schedule.open_range = result.open_range;
            this.schedule.notify = result.notify;
            this.schedule.notify_min_ago = result.notify_min_ago;
            this.schedule.st_datetime = result.st_datetime;
            this.schedule.ed_datetime = result.ed_datetime;
            this.schedule.type = result.type;
            this.schedule.subject = result.subject;
            this.schedule.comment = result.comment;
            this.schedule.repeat_kbn = result.repeat_kbn;
            this.schedule.st_span = result.st_span;
            this.schedule.ed_span = result.ed_span;
            this.schedule.week1 = result.week1;
            this.schedule.week2 = result.week2;
            this.schedule.week3 = result.week3;
            this.schedule.week4 = result.week4;
            this.schedule.week5 = result.week5;
            this.schedule.week6 = result.week6;
            this.schedule.week7 = result.week7;
            this.schedule.created_at = result.created_at;
            this.linkMsg(result.repeat_kbn);
            this.showPlan = true;
            //participants
            for (let i = 0; i < result.schedule_participants.length; i++) {
              this.participantsCheckArr.push({
                id: result.schedule_participants[i].user_info.id,
                name: result.schedule_participants[i].user_info.name,
              });
            }
          } else {
            this.$alert(res.data.errors, {showClose: false});
          }
          loading.close();
        }).catch(err => {
          loading.close();
          this.$alert(err);
        })
      },
      update() {
        this.$router.push({path: "/schedule/edit", query: {id: this.$route.query.id}});
      },
      back() {
        this.$router.go(-2);
      },
      delSchedule() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.delete("/api/deleteScheduleById", {
          params: {
            id: this.$route.query.id,
          }
        }).then(res => {
          if (res.data.result === 1) {
            this.$alert(res.data.errors, {showClose: false});
          }
          loading.close();
          this.back();
        }).catch(err => {
          loading.close();
          this.$alert(err);
        });
      },
    },
    mounted() {
      this.scheduleFetch(this.$route.query.id);
    },
  }
</script>
<style scoped>
  .inputWidth {
    width: 500px;
  }

  .timeWidth {
    width: 75px;
  }

  .dayWidth {
    width: 140px;
  }
</style>
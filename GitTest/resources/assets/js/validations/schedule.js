import common from './common'
import Messages from "../mixins/Messages";

export default {
  mixins: [common,Messages],
  data() {
      let errorFlag = true;
      let participant = (rule, value, callback) => {
                  let errMsg = this.commonMessage.error.system;
                  axios.post('/api/VerifyParticipant', {
                          participantUsers: this.participantsCheckArr,
                  }).then((res) => {
                      if (!res.data.params) {
                          errorFlag = false;
                          callback(new Error(this.PARTICIPANT_MESSAGE));
                      } else {
                          errorFlag = true;
                          callback();
                      }
                  }).catch((err) => {
                      this.$alert(errMsg, {showClose: false});
                  });
      };
      let endDate = (rule, value, callback) => {
        if (this.schedule.stDay === this.schedule.edDay &&
            new Date(this.testTime + ' '+ this.stTime).getTime() >
            new Date(this.testTime + ' '+ this.edTime).getTime()){
          callback(new Error(this.DATE_ERROR_MESSAGE));
        }else{
          callback();
        }
      };
    let trimSpace = (rule, value, callback) => {
      value = value.trim();
      if (value !== "") {
        callback()
      }else{
        callback(new Error(this.CREATE_SCHEDULE_ONLY_SPACE));
      }
    };
    return {
      scheduleRules: {
        subject: [
          this.max(90),
          this.required(['change', 'blur']),
          this.customValidate(trimSpace),
        ],
        stDay: [
          this.required(['change', 'blur']),
        ],
        edDay: [
          this.required(['change', 'blur']),
          this.customValidate(endDate),
        ],
        ed_span: [
          this.required(['change', 'blur']),
        ],
        comment: [
          this.max(500),
        ],
        location: [
          this.max(100),
        ],
        participant:[
          this.customValidate(participant),
        ],
      },
    }
  },
  methods: {}
}

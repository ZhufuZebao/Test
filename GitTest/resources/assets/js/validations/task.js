import common from './common'

export default {
  mixins: [common],
  data() {
    return {
      rules: {
        limit_date: [
          this.required(),
        ],
        time_date: [
          this.required(),
        ],
        participantsCheckArr: [
          this.required(),
        ],
        note: [
          this.required(),
        ]
      }
    }
  }
}
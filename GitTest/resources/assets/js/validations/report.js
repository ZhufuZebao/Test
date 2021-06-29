import common from './common'

export default {
  mixins: [common],
  data() {
    return {
      rules: {
        report_date: [
          this.required()
        ],
        report_date_ed: [
          this.required()
        ],
        report_user_name: [
          this.required(),
          this.max(50)
        ],
        project_name: [
          this.required(),
          this.max(50),
        ],
        location: [
          this.required(),
          this.max(50),
        ],
        type: [
          this.required(),
        ]
      },
      imageRules: {
        report_date: [
          this.required()
        ],
        work_place: [
          // this.required()
        ],
        weather: [
          // this.required()
        ],
        comment: [
          // this.required()
        ],
      },
    }
  },
  methods: {
  }
}

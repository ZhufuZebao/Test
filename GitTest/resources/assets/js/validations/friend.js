import common from './common'

export default {
  mixins: [common],
  data() {
    return {
      rules: {
        email: [
          this.email(),
          this.max(191),
        ],
      }
    }
  }
}
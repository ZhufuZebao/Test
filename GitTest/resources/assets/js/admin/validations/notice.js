import common from '../../validations/common'
import Messages from "../mixins/Messages";
//
export default {
  mixins: [common, Messages],
  data() {
    return {
      rules: {
        title: [
          this.required(['change', 'blur']),
          this.max(50),
        ],
        st_date: [
          this.required(['change', 'blur']),
        ],
        ed_date: [
          this.required(['change', 'blur']),
        ],
      }
    }
  }
}

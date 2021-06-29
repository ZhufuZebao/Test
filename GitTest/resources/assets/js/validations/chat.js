import common from './common'
import Messages from '../mixins/Messages'

export default {
  mixins: [common, Messages],
  data() {
    //グループ名 空文字 validation check
    let isSpace = (rule, value, callback) => {
      let regu = "^[ 　]+$";
      let re = new RegExp(regu);
      if (re.test(value)) {
        callback(new Error(this.CHAT_GROUP_NAME_SPACE_MESSAGE));
      } else {
        //
        callback();
      }
      callback();
    };
    return {
      chatGroup: {
        inputPopover:[
          this.max(191),
          this.required(['blur']),
          this.customValidate(isSpace),
        ],
      }
    }
  }
}

const REQUIRED_MESSAGE = '必須項目です';
const LENGTH_MAX_MESSAGE = '{0}字以内で入力してください';
const LENGTH_MIN_MESSAGE = '{0}字以上で入力してください';
const EMAIL_MESSAGE = '正しいメールアドレスを入力してください';
const EMAIL_OTHER_ENTERPRISE_MESSAGE = '他の会社に存在しました';
const EMAIL_DIFFERENT_MESSAGE = 'メールアドレスが一致しません';
const NUMBER_MESSAGE = '半角数字を入力してください';
const PASSWORD_MESSAGE = '半角数字、A-Z、a-z、-を入力してください';
const PROJECT_NO_MESSAGE = '半角数字、A-Z、a-z、-を入力してください';
const DATE_MESSAGE = '日付を入力してください';
const PASSWORD_DIFFERENT_MESSAGE = 'パスワードが一致しません';
const PASSWORD_SPACE_MESSAGE = 'パスワードにはフレームなどの空文字は含まれません';
const CHAT_GROUP_NAME_SPACE_MESSAGE = 'グループ名にはフレームなどの空文字は含まれません';
const FILE_SIZE_MESSAGE = '{0}ファイルは{1}MB以下のファイルにしてください';
const IMAGE_SIZE_MESSAGE = '画像ファイルは{0}MB以下のファイルにしてください';
const IMAGE_TYPE_MESSAGE = '画像はjpeg, pngタイプのファイルにしてください';
const EMAIL_UNIQUE_MESSAGE = 'メールアドレスが存在しました';
const ZIP_CHECK_MESSAGE = '正しい郵便番号を入力してください';
const PASSWORD_ERROR_MESSAGE = '正しいパスワードを入力してください';
const CODE_DIFFERENT_MESSAGE = '確認コードが一致しません';
const TEL_TOO_LONG_MESSAGE = '電話番号は15文字以下にしてください';
const PARTICIPANT_MESSAGE = '協力会社を参加者に登録する場合は、自分を参加者に登録してください。';
const DATE_ERROR_MESSAGE = '終了日は開始日より過去にはできません';
const CREATE_SCHEDULE_ONLY_SPACE = 'スペースのみでの登録はできません';


export default {
  data() {
    return {
      REQUIRED_MESSAGE: REQUIRED_MESSAGE,
      LENGTH_MAX_MESSAGE: LENGTH_MAX_MESSAGE,
      LENGTH_MIN_MESSAGE: LENGTH_MIN_MESSAGE,
      EMAIL_MESSAGE: EMAIL_MESSAGE,
      EMAIL_DIFFERENT_MESSAGE: EMAIL_DIFFERENT_MESSAGE,
      NUMBER_MESSAGE: NUMBER_MESSAGE,
      PASSWORD_MESSAGE: PASSWORD_MESSAGE,
      PROJECT_NO_MESSAGE: PROJECT_NO_MESSAGE,
      DATE_MESSAGE: DATE_MESSAGE,
      PASSWORD_DIFFERENT_MESSAGE: PASSWORD_DIFFERENT_MESSAGE,
      EMAIL_UNIQUE_MESSAGE: EMAIL_UNIQUE_MESSAGE,
      ZIP_CHECK_MESSAGE: ZIP_CHECK_MESSAGE,
      PASSWORD_ERROR_MESSAGE: PASSWORD_ERROR_MESSAGE,
      CODE_DIFFERENT_MESSAGE: CODE_DIFFERENT_MESSAGE,
      TEL_TOO_LONG_MESSAGE: TEL_TOO_LONG_MESSAGE,
      PASSWORD_SPACE_MESSAGE: PASSWORD_SPACE_MESSAGE,
      CHAT_GROUP_NAME_SPACE_MESSAGE: CHAT_GROUP_NAME_SPACE_MESSAGE,
      EMAIL_OTHER_ENTERPRISE_MESSAGE: EMAIL_OTHER_ENTERPRISE_MESSAGE,
      PARTICIPANT_MESSAGE:PARTICIPANT_MESSAGE,
      DATE_ERROR_MESSAGE:DATE_ERROR_MESSAGE,
      CREATE_SCHEDULE_ONLY_SPACE:CREATE_SCHEDULE_ONLY_SPACE,
    }
  },
  methods: {
    required: function (trigger = 'blur', type = 'string', message = REQUIRED_MESSAGE) {
      return {required: true, message: message, trigger: trigger};
    },
    date: function (trigger = 'change', type = 'date', message = DATE_MESSAGE) {
      return {type: type, message: message, trigger: trigger};
    },
    number: function (trigger = 'blur', type = 'string', message = NUMBER_MESSAGE) {
      return {type: type, message: message, trigger: trigger, pattern: /^[0-9]+$/};
    },
    password: function (trigger = 'blur', type = 'string', message = PASSWORD_MESSAGE) {
      return {type: type, message: message, trigger: trigger, pattern: /^[0-9A-Za-z-]+$/};
    },
    projectNo: function (trigger = 'blur', type = 'string', message = PROJECT_NO_MESSAGE) {
      return {type: type, message: message, trigger: trigger, pattern: /^[0-9A-Za-z-]+$/};
    },
    max: function (maxlength, trigger = 'blur', type = 'string', message = LENGTH_MAX_MESSAGE) {
      return {max: maxlength, type: type, message: message.replace("{0}", maxlength), trigger: trigger};
    },
    min: function (minlength, trigger = 'blur', type = 'string', message = LENGTH_MIN_MESSAGE) {
      return {min: minlength, type: type, message: message.replace("{0}", minlength), trigger: trigger};
    },
    email: function (trigger = 'blur', type = 'string', message = EMAIL_MESSAGE) {
      return {type: type, message: message, trigger: trigger, pattern: /^([a-zA-Z0-9][a-zA-Z0-9_.+\-]*)@(([a-zA-Z0-9][a-zA-Z0-9_\-]+\.)+[a-zA-Z]{2,6})+$/};
    },
    imageType: function (file, message = IMAGE_TYPE_MESSAGE) {
      const isImage = file.raw.type === 'image/jpeg' || file.raw.type === 'image/png';
      if (!isImage) {
        return message;
      }
    },
    imageSize: function (file, size = 3, message = IMAGE_SIZE_MESSAGE) {
      const isLtSize = file.size / 1024 / 1024 < size;
      if (!isLtSize) {
        return message.replace("{0}", size);
      }
    },
    fileSize: function (file, size = 3, message = FILE_SIZE_MESSAGE) {
      const isLtSize = file.size / 1024 / 1024 < size;
      if (!isLtSize) {
        return message.replace("{0}", file.name).replace("{1}", size);
      }
    },
    customValidate: function (validator, trigger = 'blur') {
      return {
        validator: validator, trigger: trigger
      };
    },
    imageValidate: function (file) {
      let error = this.imageType(file);
      if (error) {
        return error;
      }
      error = this.imageSize(file, APP_IMAGE_SIZE_LIMIT);
      if (error) {
        return error;
      }
    },

  }
}

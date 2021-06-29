const _invites = [
  {"col": 'user_name', "name": '名前'},
  {"col": 'enterprise_name', "name": '組織名'},
  {"col": 'created_at', "name": '追加日'},
  {"col": 'email', "name": 'メールアドレス'},
  {"col": 'do', "name": ''}
];

const _invitesMail = [
  {"col": 'email', "name": 'メールアドレス'},
  {"col": 'message', "name": 'メーセジ'}
];

const _invitesCooperatorRegister = [
  {"col": 'user_name', "name": 'ユーザー名'},
  {"col": 'first_name', "name": '名'},
  {"col": 'last_name', "name": '姓'},
  {"col": 'email', "name": 'メールアドレス'},
  {"col": 'password', "name": 'パスワード'},
  {"col": 'password_again', "name": 'パスワード再入力'},
  {"col": 'cooperator_name', "name": '会社名'},
  {"col": 'zip', "name": '郵便番号'},
  {"col": 'pref', "name": '都道府県'},
  {"col": 'town', "name": '市区町村'},
  {"col": 'street', "name": '番地'},
  {"col": 'house', "name": '建物名'},
  {"col": 'tel', "name": '電話番号'},
];

const _search = [
  {"col": 'all', "name": 'すべて'},
  {"col": 'customers_id', "name": '施主ID'},
  {"col": 'customers_name', "name": '施主名'},
  {"col": 'offices_name', "name": '事業所名'},
  {"col": 'offices_tel', "name": '電話番号'},
  {"col": 'offices_fax', "name": 'Fax'},
  {"col": 'people_name', "name": '担当者名'},
];

const _contextMsg=[
  {"col": 'customerCreateErr', "name": '登録中にエラーが発生しました'},
  {"col": 'customerUpdateErr', "name": '変更中にエラーが発生しました'},

];

export default {
  data() {
    return {
      invitesCols: _invites,
      invitesMailCols: _invitesMail,
      invitesCooperatorRegisterCols: _invitesCooperatorRegister,
      searchCols: _search,
      contextMsg: _contextMsg,
    }
  },
  methods: {
    invitesName (col) {
      return this.invitesCols.find(key => key.col === col).name;
    },
    invitesMailName (col) {
      return this.invitesMailCols.find(key => key.col === col).name;
    },
    invitesCooperatorRegisterName (col) {
      return this.invitesCooperatorRegisterCols.find(key => key.col === col).name;
    },
    contextMsgName (col) {
      return this.contextMsg.find(key => key.col === col).name;
    },
  }
}
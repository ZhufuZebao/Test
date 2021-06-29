const _friends = [
  {"col": 'user_name', "name": '職人名'},
  {"col": 'created_at', "name": '追加日'},
  {"col": 'email', "name": 'メールアドレス'},
  {"col": 'company_name', "name": '所属'},
  {"col": 'area1', "name": '対応可能エリア'}
];

const _friendsMail = [
  {"col": 'email', "name": 'メールアドレス'},
  {"col": 'message', "name": 'メーセジ'}
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
      friendsCols: _friends,
      friendsMailCols: _friendsMail,
      searchCols: _search,
      contextMsg: _contextMsg,
    }
  },
  methods: {
    friendsName (col) {
      return this.friendsCols.find(key => key.col === col).name;
    },
    friendsMailName (col) {
      return this.friendsMailCols.find(key => key.col === col).name;
    },
    contextMsgName (col) {
      return this.contextMsg.find(key => key.col === col).name;
    },
  }
}
const _friends = [
    {"col": 'email', "name": 'ID'},
    {"col": 'user_profiles', "name": '写真'},
    {"col": 'last_name', "name": '姓'},
    {"col": 'first_name', "name": '名'},
    {"col": 'ip', "name": 'IP'},
    {"col": 'last_date', "name": '最後ログイン時間'},
    {"col": 'creat_time', "name": '登録時間'},
    {"col": 'operate', "name": '操作'},
    {"col": 'block', "name": 'ブロック'},
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


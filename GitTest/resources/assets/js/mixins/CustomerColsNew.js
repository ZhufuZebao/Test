const _customers = [
  {"col": 'id', "name": '施主ID'},
  {"col": 'name', "name": '施主名'},
  {"col": 'phonetic', "name": '施主名カタカナ'},
  {"col": 'created_at', "name": '作成日時'},
  {"col": 'updated_at', "name": '更新日時'},
  {"col": 'user_id', "name": '作成者'}
];

const _offices = [
  {"col": 'id', "name": '事業所ID'},
  {"col": 'customer_id', "name": '施主ID'},
  {"col": 'name', "name": '事業所名'},
  {"col": 'zip', "name": '郵便番号'},
  {"col": 'pref', "name": '都道府県'},
  {"col": 'town', "name": '区市町村'},
  {"col": 'street', "name": '番地'},
  {"col": 'house', "name": '建物名'},
  {"col": 'tel', "name": '電話番号'},
  {"col": 'fax', "name": 'FAX'},
  {"col": 'created_at', "name": '作成日時'},
  {"col": 'updated_at', "name": '更新日時'},
  {"col": 'user_id', "name": '作成者'}
];

const _billings = [
  {"col": 'id', "name": '請求先ID'},
  {"col": 'customer_office_id', "name": '事業所ID'},
  {"col": 'name', "name": '事業所'},
  {"col": 'zip', "name": '郵便番号'},
  {"col": 'zip_form', "name": '郵便番号 ※半角数字「-」なし'},
  {"col": 'pref', "name": '都道府県'},
  {"col": 'town', "name": '区市町村'},
  {"col": 'street', "name": '番地'},
  {"col": 'house', "name": '建物名'},
  {"col": 'tel', "name": '電話番号'},
  {"col": 'fax', "name": 'FAX'},
  {"col": 'dept', "name": '部署'},
  {"col": 'people_name', "name": '担当者'},
  {"col": 'email', "name": 'メールアドレス'},
  {"col": 'created_at', "name": '作成日時'},
  {"col": 'updated_at', "name": '更新日時'},
  {"col": 'user_id', "name": '作成者'},
  {"col": 'checkBoxMsg', "name": '請求先は施主情報と同じ'},
  {"col": 'address', "name": '案件場所'}
];

const _people = [
  {"col": 'id', "name": '担当者ID'},
  {"col": 'customer_office_id', "name": '事業所ID'},
  {"col": 'name', "name": '担当者氏名'},
  {"col": 'position', "name": '役職'},
  {"col": 'dept', "name": '部署'},
  {"col": 'email', "name": 'メールアドレス'},
  {"col": 'tel', "name": '電話番号'},
  {"col": 'role', "name": '担当区分'},
  {"col": 'created_at', "name": '作成日時'},
  {"col": 'updated_at', "name": '更新日時'},
  {"col": 'user_id', "name": '作成者'}
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
      customersCols: _customers,
      officesCols: _offices,
      billingsCols: _billings,
      peopleCols: _people,
      searchCols: _search,
      contextMsg: _contextMsg,
    }
  },
  methods: {
    customersName (col) {
      return this.customersCols.find(key => key.col === col).name;
    },
    officesName (col) {
      return this.officesCols.find(key => key.col === col).name;
    },
    peopleName (col) {
      return this.peopleCols.find(key => key.col === col).name;
    },
    billingsName (col) {
      return this.billingsCols.find(key => key.col === col).name;
    },
    contextMsgName (col) {
      return this.contextMsg.find(key => key.col === col).name;
    },
  }
}

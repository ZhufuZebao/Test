const _customers = [
  { "col": 'id', "name": '施主ID' },
  { "col": 'name', "name": '施主名' },
  { "col": 'phonetic', "name": 'フリガナ' },
  { "col": 'created_at', "name": '作成日時' },
  { "col": 'updated_at', "name": '更新日時' },
  { "col": 'user_id', "name": '作成者' }
]

const _offices = [
  { "col": 'id', "name": '事業所ID' },
  { "col": 'customer_id', "name": '施主ID' },
  { "col": 'name', "name": '事業所名' },
  { "col": 'zip', "name": '郵便番号' },
  { "col": 'pref', "name": '都道府県' },
  { "col": 'town', "name": '市区町村' },
  { "col": 'street', "name": '番地' },
  { "col": 'house', "name": '建物名' },
  { "col": 'tel', "name": '電話番号' },
  { "col": 'fax', "name": 'FAX' },
  { "col": 'created_at', "name": '作成日時' },
  { "col": 'updated_at', "name": '更新日時' },
  { "col": 'user_id', "name": '作成者' }
]

const _billings = [
  { "col": 'id', "name": '請求先ID' },
  { "col": 'customer_office_id', "name": '事業所ID' },
  { "col": 'name', "name": '請求先名' },
  { "col": 'zip', "name": '郵便番号' },
  { "col": 'pref', "name": '都道府県' },
  { "col": 'town', "name": '市区町村' },
  { "col": 'street', "name": '番地' },
  { "col": 'house', "name": '建物名' },
  { "col": 'tel', "name": '電話番号' },
  { "col": 'fax', "name": 'FAX' },
  { "col": 'people_name', "name": '担当者名' },
  { "col": 'position', "name": '役職' },
  { "col": 'dept', "name": '部署' },
  { "col": 'email', "name": 'メールアドレス' },
  { "col": 'created_at', "name": '作成日時' },
  { "col": 'updated_at', "name": '更新日時' },
  { "col": 'user_id', "name": '作成者' }
]

const _people = [
  { "col": 'id', "name": '担当者ID' },
  { "col": 'customer_office_id', "name": '事業所ID' },
  { "col": 'name', "name": '担当者名' },
  { "col": 'position', "name": '役職' },
  { "col": 'dept', "name": '部署' },
  { "col": 'email', "name": 'メールアドレス' },
  { "col": 'role', "name": '担当区分' },
  { "col": 'created_at', "name": '作成日時' },
  { "col": 'updated_at', "name": '更新日時' },
  { "col": 'user_id', "name": '作成者' }
]

const _search = [
  { "col": 'all', "name": 'すべて' },
  { "col": 'customers_id', "name": '施主ID' },
  { "col": 'customers_name', "name": '施主名' },
  { "col": 'offices_name', "name": '事業所名' },
  { "col": 'offices_tel', "name": '電話番号' },
  { "col": 'offices_fax', "name": 'Fax' },
  { "col": 'people_name', "name": '担当者名' },
]

export default {
  data () {
    return {
      customersCols: _customers,
      officesCols: _offices,
      billingsCols: _billings,
      peopleCols: _people,
      searchCols: _search
    }
  }
}
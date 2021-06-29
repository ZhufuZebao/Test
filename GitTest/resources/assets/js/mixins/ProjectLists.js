const _tradesType = [
  {"name": '電気', "id": '1'},
  {"name": 'ガス', "id": '2'},
  {"name": '水道', "id": '3'},
  {"name": '空調', "id": '4'},
  {"name": 'その他', "id": '5'},
];

const _constructionType = [
  {"name": '新装', "id": '1'},
  {"name": '改築', "id": '2'},
];

const _progressStatus = [
  {"name": '受注前', "id": '1'},
  {"name": '着工前', "id": '2'},
  {"name": '先行', "id": '3'},
  {"name": '順調', "id": '4'},
  {"name": '多少の遅れあり', "id": '5'},
  {"name": '大幅な遅れ', "id": '6'},
  {"name": '竣工間近', "id": '7'},
  {"name": '竣工', "id": '8'},
  {"name": '失注', "id": '9'},
];

const _project = [
  {"col": 'baseMsg', "name": '案件基本情報'},
  {"col": 'place_name', "name": '案件名'},
  //#2790 remove construction_name in project
  // {"col": 'construction_name', "name": '工事件名'},
  {"col": 'project_no', "name": '案件No'},
  {"col": 'zip', "name": '郵便番号'},
  {"col": 'pref', "name": '都道府県'},
  {"col": 'town', "name": '区市町村'},
  {"col": 'street', "name": '番地'},
  {"col": 'house', "name": '建物名'},
  {"col": 'telOut', "name": '電話番号'},
  {"col": 'telIn', "name": '内線'},
  {"col": 'fax', "name": 'FAX'},
  {"col": 'customer_id', "name": '施主情報'},
  {"col": 'customerLogin', "name": '施主情報登録'},
  {"col": 'customerAdd', "name": '施主情報追加    ＋'},
  {"col": 'localeChief', "name": '現場担当者'},
  {"col": 'localeChiefAdd', "name": '担当者追加    ＋'},
  {"col": 'subject_image', "name": '案件画像'},
  {"col": 'subject_imageLogin', "name": '画像を登録'},
  {"col": 'progress_status', "name": '進捗状況'},
  {"col": 'progress_special_content', "name": '進捗特記事項'},
  {"col": 'building_type', "name": '建物用途'},
  {"col": 'workDate', "name": '工事期間'},
  {"col": 'st_date', "name": '着工日'},
  {"col": 'ed_date', "name": '竣工日'},
  {"col": 'open_date', "name": 'オープン'},
  {"col": 'mngMsg', "name": '管理会社・契約時不動産仲介業者  情報'},
  {"col": 'mng_company', "name": '管理会社'},
  {"col": 'mng_company_name', "name": '会社名'},
  {"col": 'mng_company_address', "name": '住所'},
  {"col": 'mng_company_tel', "name": '電話'},
  {"col": 'mng_company_chief', "name": '担当者'},
  {"col": 'mng_company_chief_position', "name": '担当役職'},
  {"col": 'realtorMsg', "name": '契約時不動産仲介業者'},
  {"col": 'realtor', "name": '不動産屋'},
  {"col": 'realtor_name', "name": '不動産名'},
  {"col": 'realtor_address', "name": '住所'},
  {"col": 'realtor_tel', "name": '電話'},
  {"col": 'realtor_chief', "name": '担当者'},
  {"col": 'realtor_chief_position', "name": '担当役職'},
  {"col": 'areaMsg', "name": '物件規模'},
  {"col": 'site_area', "name": '敷地面積'},
  {"col": 'floor_area', "name": '建物面積'},
  {"col": 'floor_numbers', "name": '階数'},
  {"col": 'construction_company', "name": '工事会社'},
  {"col": 'construction_special_content', "name": '工事に伴う特記事項'},
  {"col": 'securityMsg', "name": '安全管理情報'},
  {"col": 'security_management_tel', "name": '緊急連絡先電話'},
  {"col": 'security_management_chief', "name": '現場責任者'},
  {"col": 'security_management_deputy', "name": '現場副責任者'},
  {"col": 'tradesChief', "name": '工種別責任者'},
  {"col": 'tradesChiefAdd', "name": '工種別責任者追加'},
  {"col": 'fire_station_name', "name": '管轄消防署名'},
  {"col": 'fire_station_chief', "name": '管轄消防署担当者'},
  {"col": 'fire_station_tel', "name": '管轄消防署電話'},
  {"col": 'police_station_name', "name": '管轄警察署名'},
  {"col": 'police_station_chief', "name": '管轄警察署担当者'},
  {"col": 'police_station_tel', "name": '管轄警察署電話'},
  {"col": 'hospital', "name": '最寄病院'},
  {"col": 'hospitalAdd', "name": '最寄病院追加'},
  {"col": 'construction_type', "name": '新装・改築'},
  {"col": 'construction', "name": '工事内容'},
];

const _hospital = [
  {"col": 'name', "name": '最寄病院名'},
  {"col": 'tel', "name": '最寄病院電話番号'},
  {"col": 'labelName', "name": '最寄病院'},
];

const _localeChief = [
  {"col": 'name', "name": '担当者氏名'},
  {"col": 'position', "name": '役職'},
  {"col": 'tel', "name": '携帯電話'},
  {"col": 'mail', "name": 'メールアドレス'},
  {"col": 'localeChiefAdd', "name": '担当者追加'},
  {"col": 'peopleName', "name": '担当者'},
];

const _localeCustomer = [
  {"col": 'customer_name', "name": '施主名'},
  {"col": 'tel', "name": '電話番号'},
  {"col": 'name', "name": '担当者名'},
  {"col": 'email', "name": 'メールアドレス'},
];

const _tradesChief = [
  {"col": 'trades_type', "name": '工種別'},
  {"col": 'trades_type_detail', "name": '具体的な工種名'},
  {"col": 'company', "name": '工種別会社名'},
  {"col": 'name', "name": '工種別責任者'},
  {"col": 'tel', "name": '連絡先電話番号'},
];

export default {

  data() {
    return {
      tradesType: _tradesType,
      constructionType: _constructionType,
      progressStatus: _progressStatus,
      projectCols: _project,
      hospitalCols: _hospital,
      localeChiefCols: _localeChief,
      localeCustomerCols: _localeCustomer,
      tradesChiefCols: _tradesChief,
    }
  },
  methods: {
    tradesTypeName(id) {
      return this.tradesType.find(key => key.id === id).name;
    },
    constructionTypeName(id) {
      return this.constructionType.find(key => key.id === id).name;
    },
    progressStatusName(id) {
      return this.progressStatus.find(key => key.id === id).name;
    },
    projectName (col) {
      return this.projectCols.find(key => key.col === col).name;
    },
    hospitalName (col) {
      return this.hospitalCols.find(key => key.col === col).name;
    },
    localeChiefName (col) {
      return this.localeChiefCols.find(key => key.col === col).name;
    },
    localeCustomerName (col) {
      return this.localeCustomerCols.find(key => key.col === col).name;
    },
    tradesChiefName (col) {
      return this.tradesChiefCols.find(key => key.col === col).name;
    },
  }
}

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
];

const _company = [
    {"col": 'baseMsg', "name": '会社基本情報'},
    {"col": 'type', "name": '種別'},
    {"col": 'name', "name": '名前'},
    {"col": 'phonetic', "name": '名前カタカナ'},
    {"col": 'zip', "name": '郵便番号'},
    {"col": 'pref', "name": '都道府県'},
    {"col": 'town', "name": '区市町村'},
    {"col": 'street', "name": '番地'},
    {"col": 'house', "name": '建物名'},
    {"col": 'tel', "name": '電話番号'},
    {"col": 'fax', "name": 'FAX'},
    {"col": 'customer_id', "name": '施主情報'},
    {"col": 'customerLogin', "name": '施主情報登録'},
    {"col": 'customerAdd', "name": '施主情報追加    ＋'},
    {"col": 'localeChief', "name": '担当者'},
    {"col": 'localeChiefAdd', "name": '担当者追加    ＋'},
    {"col": 'remarks', "name": '備考'},
    {"col": 'address', "name": '住所'},
    {"col": 'updated_at', "name": '更新日'},
];

const _hospital = [
    {"col": 'name', "name": '最寄病院名'},
    {"col": 'tel', "name": '最寄病院電話番号'},
    {"col": 'labelName', "name": '最寄病院'},
];

const _localeChief = [
    {"col": 'name', "name": '担当者氏名'},
    {"col": 'position', "name": '部署'},
    {"col": 'dept', "name": '役職'},
    {"col": 'tel', "name": '電話番号'},
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
            companyCols: _company,
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
        companyName (col) {
            return this.companyCols.find(key => key.col === col).name;
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

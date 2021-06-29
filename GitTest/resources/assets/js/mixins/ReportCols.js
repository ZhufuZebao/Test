const _report = [
  {"col": 'id', "name": '簡易レポートID'},
  {"col": 'name', "name": '案件名'},
  {"col": 'file_date', "name": '作成日'},
  {"col": 'user_name', "name": '報告者名'},
  {"col": 'location', "name": '現場名'},
  {"col": 'updated_at', "name": '更新日'},
  {"col": 'updated_by', "name": '更新者名'},
  {"col": 'report_date', "name": '作業日時'},
  {"col": 'type', "name": 'レポートタイプ'},
  {"col": 'typeItem1', "name": 'フォーマル'},
  {"col": 'typeItem2', "name": 'カジュアル'},
  {"col": 'imagePicker', "name": 'レポート画像'}
];

export default {
  data() {
    return {
      reportCols: _report
    }
  },
  methods: {
    reportName(col) {
      return this.reportCols.find(key => key.col === col).name;
    }
  }
}
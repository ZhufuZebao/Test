const _schedule = [
  {"col": 'date', "name": '日時'},
  {"col": 'allDay', "name": '終日'},
  {"col": 'subject', "name": '予定タイトル'},
  {"col": 'location', "name": '場所'},
  {"col": 'comment', "name": '内容'},
  {"col": 'select', "name": '重要な予定'},
  {"col": 'participant', "name": '参加者'},
  {"col": 'important', "name": '重要な予定としてマーク'},
  {"col": 'plan', "name": '定期的な予定'},
  {"col": 'other', "name": '他のアカウントを選択'},
  {"col": 'cancel', "name": 'キャンセル'},
  {"col": 'add', "name": '保存する'},
  {"col": 'topAdd', "name": '新規追加'},
  {"col": 'update', "name": '詳細'},
  {"col": 'delete', "name": '削除'},
  {"col": 'delPlan', "name": '×解除'},
  {"col": 'scheduleMsg', "name": 'スケジュール'},
  {"col": 'repeat', "name": '繰り返し'},
  {"col": 'noParticipantError', "name": '参加者を選択してください'},
];

const _hour = [
  {"name": '0時', "id": '00'},
  {"name": '1時', "id": '01'},
  {"name": '2時', "id": '02'},
  {"name": '3時', "id": '03'},
  {"name": '4時', "id": '04'},
  {"name": '5時', "id": '05'},
  {"name": '6時', "id": '06'},
  {"name": '7時', "id": '07'},
  {"name": '8時', "id": '08'},
  {"name": '9時', "id": '09'},
  {"name": '10時', "id": '10'},
  {"name": '11時', "id": '11'},
  {"name": '12時', "id": '12'},
  {"name": '13時', "id": '13'},
  {"name": '14時', "id": '14'},
  {"name": '15時', "id": '15'},
  {"name": '16時', "id": '16'},
  {"name": '17時', "id": '17'},
  {"name": '18時', "id": '18'},
  {"name": '19時', "id": '19'},
  {"name": '20時', "id": '20'},
  {"name": '21時', "id": '21'},
  {"name": '22時', "id": '22'},
  {"name": '23時', "id": '23'},
];

const _minute = [
  {"name": '0分', "id": '00'},
  {"name": '5分', "id": '05'},
  {"name": '10分', "id": '10'},
  {"name": '15分', "id": '15'},
  {"name": '20分', "id": '20'},
  {"name": '25分', "id": '25'},
  {"name": '30分', "id": '30'},
  {"name": '35分', "id": '35'},
  {"name": '40分', "id": '40'},
  {"name": '45分', "id": '45'},
  {"name": '50分', "id": '50'},
  {"name": '55分', "id": '55'},
];

const _plan = [
  {"col": 'repeatKbn', "name": '間隔の設定'},
  {"col": 'interval', "name": '終了日時設定'},
  {"col": 'week', "name": '繰り返し曜日'},
  {"col": 'stSpan', "name": '開始日'},
  {"col": 'times', "name": '繰り返し回数'},
  {"col": 'edSpan', "name": '終了'},
  {"col": 'date', "name": '日付'},
];

const _repeatType = [
  {"name": 'なし', "id": '0'},
  {"name": '毎日', "id": '1'},
  {"name": '毎週', "id": '2'},
  // {"name": '毎週 なん曜日', "id": '3'},
  {"name": '毎月', "id": '4'},
  // {"name": 'カスタマイズ', "id": '5'},
];

const _repeatKbn = [
  {"name": '1日', "id": '0'},
  {"name": '毎日', "id": '1'},
  {"name": '毎月', "id": '3'},
  {"name": '曜日指定', "id": '4'},
];

const _intervalType = [
  {"name": '終了日時を設定', "id": '1'},
  {"name": '繰り返し回数を設定', "id": '2'},
];

const _repeatInterval = [
  {"name": '週毎', "id": '1'},
  {"name": '月毎', "id": '2'},
];

const _repeatWeekType = [
  {"name": '1週間毎', "id": '1'},
  {"name": '2週間毎', "id": '2'},
  {"name": '3週間毎', "id": '3'},
  {"name": '4週間毎', "id": '4'},
  {"name": '5週間毎', "id": '5'},
  {"name": '6週間毎', "id": '6'},
  {"name": '7週間毎', "id": '7'},
  {"name": '8週間毎', "id": '8'},
  {"name": '9週間毎', "id": '9'},
  {"name": '10週間毎', "id": '10'},
  {"name": '11週間毎', "id": '11'},
  {"name": '12週間毎', "id": '12'},
  {"name": '13週間毎', "id": '13'},
  {"name": '14週間毎', "id": '14'},
  {"name": '15週間毎', "id": '15'},
];

const _repeatMonthType = [
  {"name": '1ヶ月毎', "id": '1'},
  {"name": '2ヶ月毎', "id": '2'},
  {"name": '3ヶ月毎', "id": '3'},
  {"name": '4ヶ月毎', "id": '4'},
  {"name": '5ヶ月毎', "id": '5'},
  {"name": '6ヶ月毎', "id": '6'},
  {"name": '7ヶ月毎', "id": '7'},
  {"name": '8ヶ月毎', "id": '8'},
  {"name": '9ヶ月毎', "id": '9'},
  {"name": '10ヶ月毎', "id": '10'},
  {"name": '11ヶ月毎', "id": '11'},
  {"name": '12ヶ月毎', "id": '12'},
];

const _week = [
  {"name": '日', "id": '1', "isChecked": false},
  {"name": '月', "id": '2', "isChecked": false},
  {"name": '火', "id": '3', "isChecked": false},
  {"name": '水', "id": '4', "isChecked": false},
  {"name": '木', "id": '5', "isChecked": false},
  {"name": '金', "id": '6', "isChecked": false},
  {"name": '土', "id": '7', "isChecked": false},
];

const _type = [
  {"name": 'TODO', "id": '1', "className": "todo"},
  {"name": '予定', "id": '2', "className": "plan"},
  {"name": '会議', "id": '3', "className": "meeting"},
  {"name": '往訪', "id": '4', "className": "visit"},
  {"name": '作業', "id": '5', "className": "work"},
  {"name": '休み', "id": '6', "className": "holiday"},
  {"name": '重要', "id": '7', "className": ""},
];

export default {

  data() {
    return {
      scheduleCols: _schedule,
      hourCols: _hour,
      minuteCols: _minute,
      planCols: _plan,
      repeatKbnCols: _repeatKbn,
      weekCols: _week,
      typeCols: _type,
      repeatTypeCols: _repeatType,
      intervalTypeCols: _intervalType,
      repeatIntervalCols: _repeatInterval,
      repeatWeekTypeCols: _repeatWeekType,
      repeatMonthTypeCols: _repeatMonthType,
    }
  },
  methods: {
    scheduleName(col) {
      return this.scheduleCols.find(key => key.col === col).name;
    },
    hourName(id) {
      return this.hourCols.find(key => key.id === id).name;
    },
    planName(col) {
      return this.planCols.find(key => key.col === col).name;
    },
    weekName(id) {
      return this.weekCols.find(key => key.id === id).name;
    },
    repeatKbnName(id) {
      return this.repeatKbnCols.find(key => key.id === id).name;
    },
    typeName(id) {
      return this.typeCols.find(key => key.id === id).name;
    },
    typeClassName(id) {
      return this.typeCols.find(key => key.id === id).className;
    },
    repeatTypeName(id) {
      return this.repeatTypeCols.find(key => key.id === id).name;
    },
    intervalTypeName(id) {
      return this.intervalTypeCols.find(key => key.id === id).name;
    },
  }
}

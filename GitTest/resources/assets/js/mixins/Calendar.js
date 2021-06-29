export default {
  // 日数
  dateDiff(firstDate, secondDate) {
    const first = new Date(this.dateFormat(firstDate));
    const second = new Date(this.dateFormat(secondDate));
    const diff = Math.abs(first.getTime() - second.getTime());
    return parseInt(diff / (1000 * 60 * 60 * 24));
  },
  // 指定月の日数
  getDaysInOneMonth(date) {
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const d = new Date(year, month, 0);
    return d.getDate();
  },
  // 初日の位置
  getDayInWeek(date) {
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const dateFirstOne = new Date(year + '/' + month + '/1');
    return this.sundayStart ?
        dateFirstOne.getDay() === 0 ? 7 : dateFirstOne.getDay() :
        dateFirstOne.getDay() === 0 ? 6 : dateFirstOne.getDay() - 1;
  },
  // 前月また来月を取得
  getOtherMonth(date, str = 'next') {
    const timeArray = this.dateFormat(date).split('/');
    const year = timeArray[0];
    const month = timeArray[1];
    const day = timeArray[2];
    let year2 = year;
    let month2;
    if (str === 'next') {
      month2 = parseInt(month) + 1;
      if (month2 === 13) {
        year2 = parseInt(year2) + 1;
        month2 = 1;
      }
    } else {
      month2 = parseInt(month) - 1;
      if (month2 === 0) {
        year2 = parseInt(year2) - 1;
        month2 = 12;
      }
    }
    let day2 = day;
    const days2 = new Date(year2, month2, 0).getDate();
    if (day2 > days2) {
      day2 = days2;
    }
    if (month2 < 10) {
      month2 = '0' + month2;
    }
    if (day2 < 10) {
      day2 = '0' + day2;
    }
    const t2 = year2 + '/' + month2 + '/' + day2;
    return new Date(t2);
  },
  // 前月の最後の何日
  getLeftArr(date) {
    const arr = [];
    const leftNum = this.getDayInWeek(date);
    const preDate = this.getOtherMonth(date, 'before');
    const num = this.getDaysInOneMonth(preDate) - leftNum + 1;
    for (let i = 0; i < leftNum; i++) {
      const nowTime = preDate.getFullYear() + '/' + (preDate.getMonth() + 1) + '/' + (num + i);
      arr.push({
        day: num + i,
        date: nowTime,
        isToday: false,
        type: 'preMonth',
      });
    }
    return arr;
  },
  // 来月の最初の何日
  getRightArr(date) {
    const arr = [];
    const nextDate = this.getOtherMonth(date, 'next');
    const leftLength = this.getDaysInOneMonth(date) + this.getDayInWeek(date);
    const _length = 7 - leftLength % 7;
    for (let i = 0; i < _length; i++) {
      const nowTime = nextDate.getFullYear() + '/' + (nextDate.getMonth() + 1) + '/' + (i + 1);
      arr.push({
        day: i + 1,
        date: nowTime,
        isToday: false,
        type: 'nextMonth',
      });
    }
    return arr;
  },
  // 日付フォーマット
  dateFormat(date, fmt = 'yyyy/MM/dd') {
    date = typeof date === 'string' ? new Date(date.replace(/\-/g, '/')) : date;
    const o = {
      "M+": date.getMonth() + 1,               //月
      "d+": date.getDate(),                    //日
      "h+": date.getHours(),                   //時
      "m+": date.getMinutes(),                 //分
      "s+": date.getSeconds(),                 //秒
      "q+": Math.floor((date.getMonth() + 3) / 3), //Q
      "S": date.getMilliseconds()              //ミリ秒
    };
    if (/(y+)/.test(fmt)) {
      fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    for (let k in o) {
      if (new RegExp("(" + k + ")").test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
      }
    }
    return fmt;
  },
  getYear(date) {
    return date instanceof Date ? date.getFullYear() : date;
  },
  getMonth(date) {
    return date instanceof Date ? date.getMonth() + 1 : date;
  },
  // 該当月のリストを取得
  getMonthList(date = new Date()) {
    const arr = [];
    const num = this.getDaysInOneMonth(date);
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const today = this.dateFormat(new Date(), 'yyyy/M/d');

    for (let i = 0; i < num; i++) {
      const nowTime = year + '/' + month + '/' + (i + 1);
      arr.push({
        day: i + 1,
        date: nowTime,
        isToday: today === nowTime,
        type: 'currentMonth',
      });
    }
    return arr;
  },
  // 該当月のリストを取得（前月と来月も含める）
  getMonthListWithOther(date = new Date()) {
    return [...this.getLeftArr(date), ...this.getMonthList(date), ...this.getRightArr(date)];
  },
  // 該当月のリストを取得
  getMonthListWithShift(date = new Date()) {
    const arr = [];
    const leftNum = this.getDayInWeek(date);
    for (let i = 0; i < leftNum; i++) {
      arr.push({
        day: '',
        date: '',
        isToday: false,
        type: 'blank',
      });
    }
    return [...arr, ...this.getMonthList(date)];
  },
  // ディフォルト：月曜日から
  sundayStart: true,
  weekNames: ['月', '火', '水', '木', '金', '土', '日'],
  getWeekName(week) {
    return this.weekNames[week];
  },
  getDayWeekName(date) {
    date = typeof date === 'string' ? new Date(date) : date;
    let day = date.getDay() || 7;
    return this.weekNames[day - 1];
  },
  addDay(date, add = 1) {
    date = typeof date === 'string' ? new Date(date) : date;
    return new Date(date.getFullYear(), date.getMonth(), date.getDate() + add);
  },
  // 該当週の初日を取得
  getFirstDayOfWeek(date) {
    date = typeof date === 'string' ? new Date(date) : date;
    let day = this.sundayStart ? date.getDay() + 1 : date.getDay() || 7;
    return new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1 - day);
  },
  // 該当週の末日を取得
  getLastDayOfWeek(date) {
    return this.addDay(this.getFirstDayOfWeek(date), 6);
  },
  // 該当週の初日を取得
  getFirstDayOfMonth(date) {
    return new Date(date.getFullYear(), date.getMonth(), 1);
  },
  // 該当週のリストを取得
  getWeekList(date = new Date(), str = 'current') {
    const arr = [];
    let firstDayOfWeek;
    if (str === 'next') {
      firstDayOfWeek = this.getFirstDayOfWeek(this.addDay(date, 7));
    } else if (str === 'before') {
      firstDayOfWeek = this.getFirstDayOfWeek(this.addDay(date, -7));
    } else {
      firstDayOfWeek = this.getFirstDayOfWeek(date);
    }
    const year = firstDayOfWeek.getFullYear();
    const month = firstDayOfWeek.getMonth();
    const today = this.dateFormat(new Date(), 'yyyy/M/d');

    for (let i = 0; i < 7; i++) {
      const day = firstDayOfWeek.getDate() + i;
      const nowDate = new Date(year, month, day);
      const nowTime = this.dateFormat(nowDate, 'yyyy/M/d');
      arr.push({
        day: nowDate.getDate(),
        week: i + 1,
        date: nowTime,
        isToday: today === nowTime,
        type: 'week',
      });
    }
    return arr;
  },
  getDayTimeList(startHour, endHour = 24, step = 30) {
    const arr = [];
    let startTime = new Date();
    startTime.setHours(startHour, 0, 0);
    let endTime = new Date();
    if (endHour === 24) {
      endTime.setHours(23, 59);
    } else {
      endTime.setHours(endHour, 0);
    }
    while (startTime < endTime) {
      let minutes = startTime.getMinutes();
      arr.push(startTime.getHours() + ":" + (minutes < 10 ? "0" + minutes : minutes));
      startTime.setMinutes(minutes + step);
    }
    return arr;
  }
};
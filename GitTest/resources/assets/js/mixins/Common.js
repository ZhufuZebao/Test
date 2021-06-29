import SocketIO from 'socket.io-client';

export default {
  initSocket(){
    const socketUri = process.env.MIX_SOCKETIO_SERVER;
    const attempts = process.env.MIX_SOCKETIO_ATTEMPTS;
    if (socketUri) {
      window.socket = SocketIO(socketUri, {
        'reconnection': true,
        'reconnectionDelay': 1000,
        'reconnectionDelayMax': 5000,
        'reconnectionAttempts': attempts ? attempts : 5
      });
      // let errMessage='Soket接続に失敗しました';
      // window.socket.on('reconnect_failed', function(data){
      //   alert(errMessage);
      // });
    }
  },
  //15分間隔で期間を取得する
  getOptions(startTime, endTime, options) {
    let timeMap = {};
    let numToTimeMap = [];
    let j = 0;
    for (let i = 0; i < 60; i++) {
      if (i % options === 0) {
        if (i < 10) {
          i = '0' + i;
        } else {
          i = '' + i
        }
        numToTimeMap.push(i);
        timeMap.i = j;
        j++;
      }
    }
    let [startHours, startMin] = startTime.split(':');
    let [endHours, endMin] = endTime.split(':');
    let len = endHours - startHours;
    let res = [];
    for (let i = 0; i < len + 1; i++) {
      let num = (i === 0 ? timeMap[startMin] : 0);
      let inLen = (i === len) ? timeMap[endMin] + 1 : (60/options);

      if (!num){
        num = 0;
      }
      if (!inLen){
        inLen = 0;
      }
      for (let q = num; q < inLen; q++) {
        let hours = parseInt(startHours) + parseInt(i);
        if (hours < 10) {
          hours = '0' + hours;
        } else {
          hours = '' + hours
        }
        let item = `${hours}:${numToTimeMap[q]}`;
        res.push(item)
      }
    }
    return res
  }
}

import {Notification} from "element-ui"

//set task status default value
window.taskStatus = false;

//the user is chatting
window.chatGroupNow = 0;
window.chatGroupNowContact = 0;

//received new message
window.messageNewGroup = 0;
window.messageNewProGroup = 0;

// Retrieve Firebase Messaging object.
let firebaseConfig = {
  apiKey: process.env.MIX_API_KEY,
  authDomain: process.env.MIX_AUTH_DOMAIN,
  databaseURL: process.env.MIX_DATA_BASEURL,
  projectId: process.env.MIX_PROJECT_ID,
  storageBucket: process.env.MIX_STORAGE_BUCKET,
  messagingSenderId: process.env.MIX_MESSAGING_SENDERID,
  appId: process.env.MIX_APP_ID
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
//check
if (firebase.messaging.isSupported()){
  const urlPath = process.env.MIX_APP_DIR;

  navigator.serviceWorker.register(urlPath + "firebase-messaging-sw.js", {scope: urlPath + "firebase-cloud-messaging-push-scope"}).then(function (registration) {
    const messaging = firebase.messaging();
    // Add the public key generated from the console here.
    messaging.useServiceWorker(registration);
    messaging.usePublicVapidKey(process.env.MIX_VAPID_KEY);
    messaging.getToken().then((currentToken) => {
      if (currentToken) {
        updateToken(currentToken);
      } else {
        // Show permission request.
        console.log('No Instance ID token available. Request permission to generate one.');
      }
    }).catch((err) => {
      console.log('An error occurred while retrieving token. ', err);
    });
    messaging.onMessage((payload) => {
      let title = '';
      let body = '';
      let click_action = 'javascript:void(0)';
      if (payload.notification){
        title = payload.notification.title;
        body = payload.notification.body;
        if(payload.notification.click_action){
          click_action = payload.notification.click_action;
        }
      }
      let data = payload.data;
      let urlNow = window.location.href;
      //when page on chat,hide the message's notification
      // which linked to the group the user is chatting now
      if(data.call_status !== 'leave'&&data.call_status !== 'video_leave'){
        if (urlNow.indexOf('chat') > 0) {
          //chat
          if (data.call_status === 'message') {
            if (window.chatGroupNow !== parseInt(data.group_id) && window.chatGroupNowContact !== parseInt(data.group_id)) {
              showModal(title, body, click_action);
              if(data.group_type){
                window.messageNewProGroup = parseInt(data.group_id);
              }else{
                window.messageNewGroup = parseInt(data.group_id);
              }
            }
          } else if(data.call_status === 'reject') {
            window.videoChat = data;
            window.rejectFlag = 'reject';
            showModal(title, body,click_action);
          } else if(data.call_status === 'video_reject') {
            window.videoChat = data;
            window.rejectFlag = 'video_reject';
            showModal(title, body,click_action);
          } else {
            showModal(title, body,click_action);
          }

        } else {
          showModal(title, body,click_action);
        }
        if (data.call_status === 'join' || data.call_status === 'video_join') {
          clickPop(data, body);
        }

      }
    });
    // Callback fired if Instance ID token is updated.
    messaging.onTokenRefresh(() => {
      messaging.getToken().then((refreshedToken) => {
        console.log('Token refreshed.');
      }).catch((err) => {
        console.log('Unable to retrieve refreshed token ', err);
      });
    });
  }).catch(function (err) {
    // registration failed :(
    console.log('ServiceWorker registration failed: ', err);
  });
}

function updateToken(token) {
  //save token to DB
  axios.post('/api/firebaseTokenUpdate', {
    id: token,
  }).then((res) => {
    //if there are some other operations,code will be placed here
  }).catch(action => {
    console.log('Instance ID token can not be stored.');
  });
}

function showModal(title, body, click_action, onClick = 0) {
  if (onClick === 'click'){
    Notification.success({
      'title': title,
      'message': body,
      'type': 'info',
      'position': 'bottom-right',
      'showClose': false,
      'duration': 30000,
    });
  } else {
    Notification.success({
      dangerouslyUseHTMLString: true,
      title: title,
      message: '<a href=' + click_action + '>' + body + '</a>',
      type: 'info',
      position: 'bottom-right',
      showClose: true,
      duration: 4500,
    });
  }
}

function clickPop(data,title) {
  if(confirm(title)){
    router2Chat(data);
  }
  else{
    routerCancel(data);
  }

  // Popconfirm.trigger="click";
  // Popconfirm.title = 'title';
  // Popconfirm.confirmButtonText = 'yes';
  // Popconfirm.cancelButtonText = 'no';
  //
  // Popconfirm.onConfirm = router2Chat;
  // Popconfirm.onCancel = routerCancel;

}

function router2Chat(data) {
  data.callInFalg = true;
  window.videoChat = data;
  if (data.group_type){
    window.location.href = window.BASE_URL + '#/chat?proId='+data.group_id;
  }else{
    window.location.href = window.BASE_URL + '#/chat';
  }

}

function routerCancel(data) {
  let status = '';
  if (data.call_status === 'join') {
    status = 'reject';
  }
  if (data.call_status === 'video_join') {
    status = 'video_reject';
  }

  $.ajax({
    url: window.BASE_URL+"api/pushChatCall",
    data:{
      group_id:data.group_id,
      call_status:status
    },
    async: false,
    type:"POST",
    success:function(re){

    },
    error: function () {
      console.warn('network error');
    }
  });
}

function createTable(localDb) {
  return new Promise(function (resolve, reject) {
    localDb.onupgradeneeded = function (event) {
      var db = event.target.result;
      db.onerror = function(e) {
        reject(e);
      };
      try {
        createStore(db, 'msg', 'add_time');
      } catch (e) {
        reject(e);
      }
    };
  });
}

function createStore(db, tableName, keyPath, index) {
  if (db.objectStoreNames.contains(tableName)) {
    db.deleteObjectStore(tableName);
  }
  var store = db.createObjectStore(tableName, {keyPath: keyPath});
  if (index) {
    index.forEach(element => store.createIndex(element, element, {unique: false}));
  }
}

function readAll(db) {
  return new Promise(function (resolve, reject) {
    var objectStore = db.transaction('msg').objectStore('msg');
    var data = objectStore.getAll(null);
    data.onsuccess = function () {
      resolve(data)
    }
    data.onerror = function () {
      reject(data.errors());
    }
  });
}

function clear(db,tableName) {
  return new Promise(function (resolve, reject) {
    var request = db.transaction([tableName], 'readwrite')
        .objectStore(tableName)
        .clear();
    request.onsuccess = function (event) {
      resolve();
    };
  });
}

indexedDB = window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
var localDb = indexedDB.open('site', 2);
createTable(localDb).then(function() {},
    function (error) {});

document.addEventListener('visibilitychange',function(){
  var localDb = indexedDB.open('site', 2);
  var db = '';
  if(document.visibilityState=='visible') {
    localDb.onsuccess = function (event) {
      db = localDb.result;
      readAll(db).then(function(data){
        if (data.result.length) {
          setTimeout(function (){
            clear(db,'msg');
          },1000);
          var item = data.result[data.result.length-1];
          if (item.call_status === 'join' || item.call_status === 'video_join') {
            clickPop(item, item.title);
          }
        }
      })
    };
  }else if(document.visibilityState=='hidden'){
  }
});
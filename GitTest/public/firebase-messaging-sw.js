// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here, other Firebase libraries
// are not available in the service worker.
importScripts('https://www.gstatic.com/firebasejs/7.24.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.24.0/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
  apiKey: "AIzaSyDy7-xRFD1VDC4RLvLJMeSKoyhRqR8Cn_s",
  authDomain: "site-conit-develop.firebaseapp.com",
  databaseURL: "https://site-conit-develop.firebaseio.com",
  projectId: "site-conit-develop",
  storageBucket: "site-conit-develop.appspot.com",
  messagingSenderId: "800992197959",
  appId: "1:800992197959:web:75cc259c4e035151f96062"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
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
  // Customize notification here
  const notificationTitle = title;
  const notificationOptions = {
    body: body,
    icon: '/images/logo2.png'
  };

  // self.registration.showNotification(notificationTitle,
  //     notificationOptions);

  let data = payload.data;

  data.title=body
  var localDb = indexedDB.open('site', 2);
  localDb.onsuccess = function (event) {
    db = localDb.result;
    addData(db,data)
  };

});

function addData(db,data) {
  data.add_time = new Date().getTime();
  var request = db.transaction(['msg'], 'readwrite')
      .objectStore('msg')
      .add(data);

  request.onsuccess = function (event) {
  };

  request.onerror = function (event) {
  }
}




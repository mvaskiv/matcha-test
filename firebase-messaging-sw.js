importScripts("https://www.gstatic.com/firebasejs/4.12.0/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/4.12.0/firebase-messaging.js");
var config = {
    apiKey: "AIzaSyD6jBTtHzY5vtNLdQFbFmXGffoySY24Bxg",
    authDomain: "matcha-212014.firebaseapp.com",
    databaseURL: "https://matcha-212014.firebaseio.com",
    projectId: "matcha-212014",
    storageBucket: "matcha-212014.appspot.com",
    messagingSenderId: "38522168959"
};

firebase.initializeApp(config);
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(payload => {
   const title = payload.notification.title;
   console.log('payload', payload.notification.icon);
   const options = {
      body: payload.notification.body,
      icon: payload.notification.icon
   }
   return self.registration.showNotification(title, options);
})

self.addEventListener("notificationclick", function(event) {
    const clickedNotification = event.notification;
    clickedNotification.close();
    const promiseChain = clients
        .matchAll({
            type: "window",
            includeUncontrolled: true
         })
        .then(windowClients => {
            let matchingClient = null;
            for (let i = 0; i < windowClients.length; i++) {
                const windowClient = windowClients[i];
                if (windowClient.url === feClickAction) {
                    matchingClient = windowClient;
                    break;
                }
            }
            if (matchingClient) {
                return matchingClient.focus();
            } else {
                return clients.openWindow(feClickAction);
            }
        });
        event.waitUntil(promiseChain);
 });

// AAAACPgZpn8:APA91bEdY0-4wDndQuF4sS2bjA2FE-7R6prGvHlp8xgxeWv2U_WPOOnhhT2LKQ3I4zuF9RA4M5VGw44Z2wnENN4LFo-I8rocQ_F0Z2Z_1hlvGP-eox2XiEzMef0SNEfNCIYPbYRIVObvjhe0cdwyIojQL4SXDWjEPQfPhFfb8Mxfc:APA91bELeSTfCR5JfdMFPPy6kFmIx4qiMInDeLpLBeobGl28LWkkNfL2gvRwjm7r2J2POwWZsCLWoSMQyIX7TvC_oKwA8FeiU33QJzKsjJ0Vt2O_mCawoPtD_JybO8iFmSPsAN0hEtLo

// fPhFfb8Mxfc:fPhFfb8Mxfc:APA91bELeSTfCR5JfdMFPPy6kFmIx4qiMInDeLpLBeobGl28LWkkNfL2gvRwjm7r2J2POwWZsCLWoSMQyIX7TvC_oKwA8FeiU33QJzKsjJ0Vt2O_mCawoPtD_JybO8iFmSPsAN0hEtLo

// curl -X POST --header "Authorization: key=AAAACPgZpn8:APA91bETKxCnDptt0ej0AHyX9fARyBzRf5jlkaOJvWkE9xfisPJ0906GLmfLszuDXaLTF-0Go9fM30YtLRW7MJ-X7H5x6L2wGjM15AHlZLTn-iQvKy0WI7_h1dqSxC-OiqpgfqzgCoCCucCjwdozH3xFOr49dDaJ-g" --header "Content-Type: application/json" -d "{\"to\":\"fPhFfb8Mxfc:APA91bELeSTfCR5JfdMFPPy6kFmIx4qiMInDeLpLBeobGl28LWkkNfL2gvRwjm7r2J2POwWZsCLWoSMQyIX7TvC_oKwA8FeiU33QJzKsjJ0Vt2O_mCawoPtD_JybO8iFmSPsAN0hEtLo\",\"priority\":\"high\",\"notification\":{\"body\": \"FOO BAR BLA BLA\"}}" "https://fcm.googleapis.com/fcm/send"
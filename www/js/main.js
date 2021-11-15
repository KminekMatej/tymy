$(document).ready(function () {
    reTitle();

    const pushNotificationSuported = isPushNotificationSupported();
    if (pushNotificationSuported) {
        registerServiceWorker();
        requestPushPermission();
    }

});

function reTitle() {
    numTitle = $("NAV[data-title]").attr("data-title");
    cnt = (document.title.match(/\|/g) || []).length;
    if (cnt == 1) {
        if (numTitle > 0)
            document.title = numTitle + " | " + document.title;
    } else if (numTitle === "0") {
        document.title = document.title.replace(new RegExp(/\d+ \| /), "");
    } else
        document.title = document.title.replace(new RegExp(/\d+/), numTitle);
}

function btnRotate(btn, disable) {
    if (btn.length > 0) {
        if (disable) {
            btn.find("svg[data-fa-i2svg]").addClass("fa-spin");
            btn.prop("disabled", true);
            btn.attr("disabled", "disabled");
        } else {
            btn.find("svg[data-fa-i2svg]").removeClass("fa-spin");
            btn.prop("disabled", false);
            btn.removeAttr("disabled");
        }
    }
    return true;
}


/**
 * checks if Push notification and service workers are supported by your browser
 */
function isPushNotificationSupported() {
    return "serviceWorker" in navigator && "PushManager" in window;
}

/**
 * 
 */
function registerServiceWorker() {
    return navigator.serviceWorker.register('/service-worker.js')
            .then(function (registration) {
                console.log('Service worker successfully registered with scope ' + registration.scope);
                return registration;
            })
            .catch(function (err) {
                console.error('Unable to register service worker.', err);
            });
}

function requestPushPermission() {
    return new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    }).then(function (permissionResult) {
        if (permissionResult !== 'granted') {
            throw new Error('We weren\'t granted permission.');
        }
    });
}


/**
 * shows a notification
 */
/*function sendNotification() {
    const img = "/images/jason-leung-HM6TMmevbZQ-unsplash.jpg";
    const text = "Take a look at this brand new t-shirt!";
    const title = "New Product Available";
    const options = {
        body: text,
        icon: "/images/jason-leung-HM6TMmevbZQ-unsplash.jpg",
        vibrate: [200, 100, 200],
        tag: "new-product",
        image: img,
        badge: "https://spyna.it/icons/android-icon-192x192.png",
        actions: [{action: "Detail", title: "View", icon: "https://via.placeholder.com/128/ff0000"}]
    };
    navigator.serviceWorker.ready.then(function (serviceWorker) {
        serviceWorker.showNotification(title, options);
    });
}*/
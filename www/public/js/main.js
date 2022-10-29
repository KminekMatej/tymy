$(document).ready(function () {
    reTitle();

    const pushNotificationSuported = isPushNotificationSupported();
    if (pushNotificationSuported) {
        registerServiceWorker();
        requestPushPermission();
    }

    $('#fileupload').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('<p></p>').text(file.name).appendTo(document.body);
            });
            if (data.result.snippets) {
                $.nette.ext('snippets').updateSnippets(data.result.snippets);
            }
        }
    });

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
    return navigator.serviceWorker.register('/public/service-worker.js')
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
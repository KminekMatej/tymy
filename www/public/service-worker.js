// add event listener to subscribe and send subscription to server
self.addEventListener('activate', subscribe);
// and listen to incomming push notifications
self.addEventListener('push', processPush);
// ... and listen to the click
//self.addEventListener('notificationclick', pnNotificationClick);

async function subscribe(event) {
    try {
        var opt = {
            applicationServerKey: base64UrlToUint8Array('BNFwVh2n9jBpGDMx3Kiz47ldm8GrSP74Ff4VL54v6AoPg01BnT2TtqdEXBm6M7C8Nx3PoqqPie7kESKgm8jkAog'),
            userVisibleOnly: true
        };

        self.registration.pushManager.subscribe(opt)
                .then((sub) => {
                    // subscription succeeded - send to server
                    saveSubscription(sub)
                            .then((response) => {
                                console.log(response);
                            }).catch((e) => {
                        // registration failed
                        console.log('SaveSubscription failed with: ' + e);
                    });
                }, ).catch((e) => {
            // registration failed
            console.log('Subscription failed with: ' + e);
        });

    } catch (e) {
        console.log('Error subscribing notifications: ' + e);
    }
}

async function saveSubscription(subscription) {
    // stringify object to post as body with HTTP-request
    var fetchdata = {
        method: 'post',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(subscription),
    };
    // we're using fetch() to post the data to the server
    var response = await fetch('/api/push-notification', fetchdata);
    return response.json();
}

async function processPush(event) {
    if (event.data) {
        var payload = event.data.json();
        const options = {
            body: payload.message,
            icon: "/images/logo/tymy_icon_black_round_256.png",
            vibrate: [200, 100, 200],
            image: payload.image,
            badge: payload.badge, //badge support is not done yet - in the future may hold number of unread posts / events etc.
            actions: [{action: "Detail", title: "View", icon: "/images/logo/tymy_icon_black_round_256.png"}]
        };

        event.waitUntil(self.registration.showNotification(payload.title, options));
    }
}

function base64UrlToUint8Array(base64UrlData) {
    const padding = '='.repeat((4 - base64UrlData.length % 4) % 4);
    const base64 = (base64UrlData + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = atob(base64);
    const buffer = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        buffer[i] = rawData.charCodeAt(i);
    }

    return buffer;
}
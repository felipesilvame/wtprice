'use strict';

const swReady = navigator.serviceWorker.ready;


function initSW() {
    if (!"serviceWorker" in navigator) {
        //service worker isn't supported
        return;
    }

    //don't use it here if you use service worker
    //for other stuff.
    if (!"PushManager" in window) {
        //push isn't supported
        return;
    }

    //register the service worker
    navigator.serviceWorker.register('/sw.js')
        .then(() => {
            //console.log('serviceWorker installed!')
            initPush();
        })
        .catch((err) => {
            console.log(err)
        });
}

function initPush() {
    if (!swReady) {
        return;
    }

    new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    })
        .then((permissionResult) => {
	    //console.log(permissionResult);
            if (permissionResult !== 'granted') {
                throw new Error('We weren\'t granted permission.');
            }
            subscribeUser();
        });
}

/**
 * Subscribe the user to push
 */
function subscribeUser() {
    //console.log('subscribing...');
    swReady.then((registration) => {
            const subscribeOptions = {
                userVisibleOnly: true,
                // change application serverkey with the generataed one
                applicationServerKey: urlBase64ToUint8Array(
                    window.Laravel.vapidPublicKey
                )
            };
	    //console.log(`vapidpublic key is: ${window.Laravel.vapidPublicKey}`);
            return registration.pushManager.subscribe(subscribeOptions);
        })
        .then((pushSubscription) => {
            //console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
            storePushSubscription(pushSubscription);
        });
}

/**
 * send PushSubscription to server with AJAX.
 * @param {object} pushSubscription
 */
function storePushSubscription(pushSubscription) {
    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
    var data = {
      sub: pushSubscription,
      hashid: window.Laravel.hashid
    }
    fetch('/push', {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        }
    })
        .then((res) => {
            return res.json();
        })
        .then((res) => {
            console.log(res)
        })
        .catch((err) => {
            console.log(err)
        });
}

/**
 * urlBase64ToUint8Array
 *
 * @param {string} base64String a public vapid key
 */
function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class MoorlCountdownPlugin extends Plugin {
    static options = {
        locale: document.documentElement.lang,
        label: {
            days: "Days",
            hours: "Hours",
            minutes: "Minutes",
            seconds: "Seconds"
        },
        intervalTimeout: 1000,
        from: 'now',
        actionUrl: null,
        debug: false
    };

    init() {
        if (this.options.actionUrl) {

        }

        const actionUrl = this.options.actionUrl;
        const debug = this.options.debug;
        const cdItems = this.buildContainer();
        const from = new Date(this.options.from);
        const zeroPad = (num, places) => String(num).padStart(places, '0');
        const client = new HttpClient(window.accessKey, window.contextToken);

        let x = setInterval(function () {
            let now = new Date();
            let diff = Math.floor((from.getTime() - now.getTime()) / 1000);

            if (diff < 1) {
                clearInterval(x);

                setTimeout(() => {
                    if (actionUrl) {
                        client.get(actionUrl, (response) => {
                            response = JSON.parse(response);
                            if (response.url) {
                                window.location.href = response.url;
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        window.location.reload();
                    }
                }, 5000);

                diff = 0;
            }

            let days = Math.trunc(diff / (60 * 60 * 24));
            let hours = Math.trunc((diff % (60 * 60 * 24)) / (60 * 60));
            let minutes = Math.trunc((diff % (60 * 60)) / (60));
            let seconds = Math.trunc((diff % (60)));

            cdItems[0].innerText = zeroPad(days, 2);
            cdItems[1].innerText = zeroPad(hours, 2);
            cdItems[2].innerText = zeroPad(minutes, 2);
            cdItems[3].innerText = zeroPad(seconds, 2);
        }, this.options.intervalTimeout);
    }

    buildContainer() {
        const cdItems = [];

        for (let item of ['days','hours','minutes','seconds']) {
            const itemDiv = document.createElement("div");
            const labelDiv = document.createElement("div");
            const timeDiv = document.createElement("div");

            labelDiv.classList.add('moorl-countdown-label');
            labelDiv.innerText =  this.options.label[item];
            timeDiv.classList.add('moorl-countdown-time');
            timeDiv.innerText = "--";

            itemDiv.appendChild(labelDiv);
            itemDiv.appendChild(timeDiv);

            cdItems.push(timeDiv);
            this.el.appendChild(itemDiv);
        }

        return cdItems;
    }
}

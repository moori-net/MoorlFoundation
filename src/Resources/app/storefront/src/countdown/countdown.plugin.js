import Plugin from 'src/plugin-system/plugin.class';

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
        actionUrl: null
    };

    init() {
        if (this.options.actionUrl) {
            console.log(this.options.actionUrl);
        }

        const actionUrl = this.options.actionUrl;
        const cdItems = this.buildContainer();
        const from = new Date(this.options.from);
        const zeroPad = (num, places) => String(num).padStart(places, '0');

        let x = setInterval(function () {
            let now = new Date();
            let diff = Math.floor((from.getTime() - now.getTime()) / 1000);

            if (actionUrl && diff < 1) {
                location.href = actionUrl;
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

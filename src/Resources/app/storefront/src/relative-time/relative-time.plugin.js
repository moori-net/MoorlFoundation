const Plugin = window.PluginBaseClass;
import HttpClient from 'src/service/http-client.service';

export default class MoorlRelativeTimePlugin extends Plugin {
    static options = {
        locale: document.documentElement.lang,
        intervalTimeout: 1000,
        from: 'now',
        actionUrl: null,
    };

    init() {
        const actionUrl = this.options.actionUrl;
        const time = new Intl.RelativeTimeFormat(this.options.locale);
        const from = new Date(this.options.from);
        const el = this.el;
        const client = new HttpClient(window.accessKey, window.contextToken);

        if (!el.dataset.bsToggle) {
            el.innerText = '---';
        }

        let relTime = '';

        let x = setInterval(function () {
            let now = new Date();
            let diff = Math.floor((from.getTime() - now.getTime()) / 1000);

            if (diff < 1 && actionUrl) {
                clearInterval(x);

                setTimeout(() => {
                    client.get(actionUrl, (response) => {
                        response = JSON.parse(response);
                        if (response.url) {
                            window.location.href = response.url;
                        } else {
                            window.location.reload();
                        }
                    });
                }, 5000);

                diff = 0;
            }

            let days = Math.trunc(diff / (60 * 60 * 24));
            let hours = Math.trunc((diff % (60 * 60 * 24)) / (60 * 60));
            let minutes = Math.trunc((diff % (60 * 60)) / 60);
            let seconds = Math.trunc(diff % 60);

            if (days !== 0) {
                relTime = time.format(days, 'day');
            } else if (hours !== 0) {
                relTime = time.format(hours, 'hour');
            } else if (minutes !== 0) {
                relTime = time.format(minutes, 'minute');
            } else if (seconds !== 0) {
                relTime = time.format(seconds, 'second');
            }

            if (el.dataset.bsToggle) {
                el.dataset.bsOriginalTitle = relTime;
            } else {
                el.innerText = relTime;
            }
        }, this.options.intervalTimeout);
    }
}

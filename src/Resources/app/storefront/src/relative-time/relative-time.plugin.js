import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlRelativeTimePlugin extends Plugin {

    static options = {
        locale: document.documentElement.lang,
        intervalTimeout: 1000,
        from: 'now'
    };

    init() {
        const time = new Intl.RelativeTimeFormat(this.options.locale);
        const from = new Date(this.options.from);
        const el = this.el;

        el.innerText = '---';

        let relTime = '';

        let x = setInterval(function () {
            let now = new Date();
            let diff = Math.floor((from.getTime() - now.getTime()) / 1000);

            let days = Math.trunc(diff / (60 * 60 * 24));
            let hours = Math.trunc((diff % (60 * 60 * 24)) / (60 * 60));
            let minutes = Math.trunc((diff % (60 * 60)) / (60));
            let seconds = Math.trunc((diff % (60)));

            if (days !== 0) {
                relTime = time.format(days, 'day')
            } else if (hours !== 0) {
                relTime = time.format(hours, 'hour')
            } else if (minutes !== 0) {
                relTime = time.format(minutes, 'minute')
            } else if (seconds !== 0) {
                relTime = time.format(seconds, 'second')
            }

            if (el.dataset.originalTitle) {
                el.dataset.originalTitle = relTime;
            } else {
                el.innerText = relTime;
            }
        }, this.options.intervalTimeout);
    }
}

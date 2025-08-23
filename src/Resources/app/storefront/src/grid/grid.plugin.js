const Plugin = window.PluginBaseClass;

export default class MoorlGridPlugin extends Plugin {
    static options = {
        offsetTop: 0,
        isSticky: false,
    };

    init() {
        if (!this.options.isSticky) {
            return;
        }

        /*this.el.classList.add('sticky-top');*/

        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        window.addEventListener('scroll', (event) => {
            that._onScroll();
        });
    }

    _onScroll() {
        let scrollTop =
            document.documentElement.scrollTop || document.body.scrollTop || 0;
        let tocNavTop =
            this.el.getBoundingClientRect().top +
            this.el.ownerDocument.defaultView.pageYOffset;

        if (scrollTop < tocNavTop) {
            this.el.style.paddingTop = '0';
        } else {
            this.el.style.paddingTop = this.options.offsetTop;
        }
    }
}

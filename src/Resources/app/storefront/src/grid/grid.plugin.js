const Plugin = window.PluginBaseClass;

export default class MoorlGridPlugin extends Plugin {
    static options = {
        offsetTop: window.moorlOffsetTop ?? 30,
        isSticky: false,
    };

    init() {
        if (!this.options.isSticky) {
            return;
        }

        this._setCmsSectionOverflowVisible();
        this._registerEvents();
    }

    _setCmsSectionOverflowVisible() {
        const cmsSection = this.el.closest('.cms-section');

        if (cmsSection) {
            cmsSection.style.overflow = 'visible';
        }
    }

    _registerEvents() {
        window.addEventListener('scroll', () => {
            this._onScroll();
        });
    }

    _onScroll() {
        const parentHeight = this.el.parentElement.getBoundingClientRect().height;
        const elementHeight = this.el.getBoundingClientRect().height;

        if (parentHeight <= elementHeight) {
            this.el.style.paddingTop = '0';
            return;
        }

        const scrollTop =
            document.documentElement.scrollTop || document.body.scrollTop || 0;

        const tocNavTop =
            this.el.getBoundingClientRect().top +
            this.el.ownerDocument.defaultView.scrollY;

        if (scrollTop < tocNavTop) {
            this.el.style.paddingTop = '0';
        } else {
            this.el.style.paddingTop = `${this.options.offsetTop}px`;
        }
    }
}

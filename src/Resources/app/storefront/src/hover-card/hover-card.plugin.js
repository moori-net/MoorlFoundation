const Plugin = window.PluginBaseClass;

export default class MoorlHoverCardPlugin extends Plugin {
    static options = {
        url: null,
        toggle: false,
        title: '',
        placement: 'top',
        animation: false,
    };

    init() {
        this._popover = new bootstrap.Popover(this.el, {
            html: true,
            sanitize: false,
            animation: this.options.animation,
            content: '',
            title: this.options.title,
            placement: this.options.placement,
        });
        this._loaded = false;
        this._scheduled = null;
        this._isMouseOverPopover = false;
        this._registerEvents();
    }

    _registerEvents() {
        if (this.options.toggle) {
            this.el.addEventListener('click', (event) => {
                event.preventDefault();
                this._togglePopover();
            });
            return;
        }
        this.el.addEventListener('mouseenter', () => this._showPopover());
        this.el.addEventListener('mouseleave', () =>
            this._scheduleHidePopover()
        );
    }

    _togglePopover() {
        if (this._loaded) {
            this._popover.toggle();
            window.PluginManager.initializePlugins();
        } else {
            fetch(this.options.url)
                .then((response) => response.text())
                .then((body) => {
                    this._popover.setContent({ '.popover-body': body });
                    this._popover.show();
                    window.PluginManager.initializePlugins();
                    this._loaded = true;
                });
        }
    }

    _showPopover() {
        if (this._scheduled) {
            clearTimeout(this._scheduled);
            this._scheduled = null;
            return;
        }

        if (this._loaded) {
            this._popover.show();
            this._attachPopoverEvents();
        } else {
            fetch(this.options.url)
                .then((response) => response.text())
                .then((body) => {
                    this._popover.setContent({ '.popover-body': body });
                    this._popover.show();
                    this._loaded = true;
                    this._attachPopoverEvents();
                });
        }
    }

    _scheduleHidePopover() {
        if (this._scheduled) return;

        this._scheduled = setTimeout(() => {
            if (!this._isMouseOverPopover) {
                this._popover.hide();
                this._scheduled = null;
            }
        }, 200);
    }

    _attachPopoverEvents() {
        window.PluginManager.initializePlugins();

        setTimeout(() => {
            const popoverElement = this._popover.tip;
            if (!popoverElement) return;

            popoverElement.addEventListener('mouseenter', () => {
                clearTimeout(this._scheduled);
                this._scheduled = null;
                this._isMouseOverPopover = true;
            });

            popoverElement.addEventListener('mouseleave', () => {
                this._isMouseOverPopover = false;
                this._scheduleHidePopover();
            });
        }, 50);
    }
}

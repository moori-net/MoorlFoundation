import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlHoverCardPlugin extends Plugin {
    init() {
        this._popover = new bootstrap.Popover(this.el, {
            html: true,
            sanitize: false,
            content: "",
            placement: 'top'
        });
        this._loaded = false;
        this._scheduled = null;
        this._isMouseOverPopover = false;
        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('mouseenter', () => this._showPopover());
        this.el.addEventListener('mouseleave', () => this._scheduleHidePopover());
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
            fetch(this.el.dataset.moorlHoverCard)
                .then(response => response.text())
                .then(body => {
                    this._popover.setContent({'.popover-body': body});
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

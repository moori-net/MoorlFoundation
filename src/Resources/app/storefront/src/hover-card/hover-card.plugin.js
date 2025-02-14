import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlHoverCardPlugin extends Plugin {
    init() {
        this._registerEvents();
        this._popover = new bootstrap.Popover(this.el, {
            html: true,
            sanitize: false,
            content: "",
            placement: 'top'
        });
    }

    _registerEvents() {
        let timer;

        this.el.addEventListener('mouseenter', () => {
            fetch(this.el.dataset.moorlHoverCard)
                .then(body => body.text())
                .then(body => {
                    this._popover.content = body;
                    this._popover.show();

                    this._popover.element.addEventListener('mouseenter', () => {
                        clearTimeout(timer);
                    });

                    this._popover.element.addEventListener('mouseleave', () => {
                        this._popover.hide();
                    });
                });
        });

        this.el.addEventListener('mouseleave', () => {
            timer = setTimeout(() => {
                this._popover.hide();
            }, 600)
        });
    }
}

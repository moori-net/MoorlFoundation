import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class MoorlFoundation extends Plugin {
    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._registerModalEvents();
        this.callback = null;

        let config = document.getElementById("moorlFoundationAnimateConfig");

        if (!config) {
            return; // disabled
        }

        try {
            config = JSON.parse(config.innerText);
        } catch (e) {
            console.log(e);
            return;
        }

        this.animateInit(config.animateIn, 'data-animate-in');
        this.animateInit(config.animateOut, 'data-animate-out');
        this.animateInit(config.hover, 'data-animate-hover');

        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        $("[data-animate-in],[data-animate-out]").each(function () {
            let isVisible = $(this).isOverBottom();
            if (!isVisible) {
                $(this).addClass("moorl-foundation-hide");
            }
        });

        $("[data-animate-hover]").on("mouseover", function () {
            let ready = !$(this).hasClass("animated");
            if (ready) {
                that.mouseOver(this);
            }
        });

        window.addEventListener("scroll", function () {
            that.animate();
        }, false);
    }

    _registerModalEvents() {
        const that = this;

        jQuery('body').on('click', '[data-moorl-foundation-modal]', function () {
            let url = this.dataset.moorlFoundationModal;

            that._client.get(url, (response) => {
                that._openModal(response, null);
            });
        });

        window.moorlFoundationModal = function (url, callback) {
            that._client.get(url, (response) => {
                that._openModal(response, callback);
            });
        }

        jQuery('body').on('hidden.bs.modal', function () {
            jQuery('.modal video').trigger('pause');
        });
    }

    _openModal(response, callback) {
        jQuery('#moorlFoundationModal').html(response).modal('show');

        if (typeof callback == 'function') {
            callback();
        }
    }

    animateInit(str, attr) {
        if (str.trim().length == 0) {
            return;
        }
        let lines = str.split(/;/g);
        if (!lines || typeof lines != 'object') {
            console.log("MoorlFoundation warning: Misconfiguration at animation settings");
            console.log(lines);
            console.log(str);
            console.log(attr);
            return;
        }
        lines.forEach(function (line) {
            let config = line.split(/\|/g);
            if (typeof config[1] == 'string') {
                $(config[0]).attr(attr, config[1]);
            } else {
                console.log("MoorlFoundation warning: Misconfiguration at animation settings");
                console.log(config);
                console.log(str);
                console.log(attr);
            }
        });
    };

    animateIn(el) {
        let isVisible = $(el).isOverBottom();
        if (isVisible) {
            $(el).addClass("animated").addClass(el.dataset.animateIn).removeClass("moorl-foundation-hide");
            setTimeout(function () {
                $(el).removeClass(el.dataset.animateIn).removeClass("animated");
            }, 1000);
        }
    };

    animateOut(el) {
        const that = this;
        let isVisible = $(el).isOverBottom();
        if (!isVisible) {
            $(el).addClass("animated").addClass(el.dataset.animateOut);
            setTimeout(function () {
                $(el).removeClass(el.dataset.animateOut).addClass("moorl-foundation-hide").removeClass("animated");
                that.animate();
            }, 1000);
        }
    };

    mouseOver(el) {
        console.log(el);
        $(el).addClass("animated").addClass(el.dataset.animateHover);
        setTimeout(function () {
            $(el).removeClass(el.dataset.animateHover).removeClass("animated");
        }, 1000);
    };

    animate() {
        const that = this;
        $("[data-animate-in]").each(function () {
            let ready = ($(this).hasClass("moorl-foundation-hide") && !$(this).hasClass("animated"));
            if (ready) {
                that.animateIn(this);
            }
        });
        $("[data-animate-out]").each(function () {
            let ready = (!$(this).hasClass("moorl-foundation-hide") && !$(this).hasClass("animated"));
            if (ready) {
                that.animateOut(this);
            }
        });
    };
}

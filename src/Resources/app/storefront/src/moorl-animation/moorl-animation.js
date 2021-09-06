import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlAnimation extends Plugin {
    init() {
        /*this.elements = document.querySelectorAll('[data-moorl-animation]');*/
        this.config = JSON.parse(this.el.dataset.moorlAnimation);

        this.activeAnimation = null;
        this.animateInit();
        this.animate();
        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        window.addEventListener("scroll", function () {
            that.animate();
        }, false);

        this.el.addEventListener("mouseenter",  () => {
            if (that.activeAnimation) {
                return;
            }

            if (that.config.animateHover && that.config.animateHover.type && that.config.animateHover.type !== 'none') {
                that.animateHover();
            }
        });

        this.el.addEventListener('animationstart', () => {
            if (that.activeAnimation === 'in') {
                that.el.classList.remove('moorl-animation-hidden');
            }
        });

        this.el.addEventListener('animationend', () => {
            if (that.activeAnimation === 'out') {
                that.el.classList.add('moorl-animation-hidden');
            }

            that.el.style = {};
            that.activeAnimation = null;

            that.animate();
        });
    }

    _ms(number) {
        return number.toString() + "ms";
    }

    _visible(rule) {
        const el = this.el;

        if (rule === 'isLoaded') {
            return true;
        }

        if (rule === 'isOverBottom') {
            return $(el).isOverBottom();
        }

        if (rule === 'isInViewport') {
            return $(el).isInViewport();
        }
    }

    animateHover() {
        const config = this.config.animateHover;

        if (this.el.classList.contains('moorl-animation-hidden')) {
            return;
        }

        if (this._visible(config.rule)) {
            this.activeAnimation = 'hover';
            this.el.style.animation = config.type;
            this.el.style.zIndex = 9000;
            this.el.style.animationDelay = this._ms(config.timeout);
            this.el.style.animationDuration = this._ms(config.speed);
        }
    };

    animateIn() {
        const config = this.config.animateIn;

        if (!this.el.classList.contains('moorl-animation-hidden')) {
            return;
        }

        if (this._visible(config.rule)) {
            this.activeAnimation = 'in';
            this.el.style.animation = config.type;
            this.el.style.zIndex = 9000;
            this.el.style.animationDelay = this._ms(config.timeout);
            this.el.style.animationDuration = this._ms(config.speed);
        }
    };

    animateOut() {
        const config = this.config.animateOut;

        if (this.el.classList.contains('moorl-animation-hidden')) {
            return;
        }

        if (!this._visible(config.rule)) {
            this.activeAnimation = 'out';
            this.el.style.animation = config.type;
            this.el.style.animationDelay = this._ms(config.timeout);
            this.el.style.animationDuration = this._ms(config.speed);
        }
    };

    animateInit() {
        const config = this.config;

        if (config.animateIn && config.animateIn.type && config.animateIn.type !== 'none') {
            if (!this._visible(config.animateIn.rule)) {
                this.el.classList.add('moorl-animation-hidden');
            } else if (config.animateIn.rule === 'isLoaded') {
                this.el.classList.add('moorl-animation-hidden');
            }
        }
    };

    animate() {
        if (this.activeAnimation) {
            return;
        }

        if (this.config.animateIn && this.config.animateIn.type && this.config.animateIn.type !== 'none') {
            this.animateIn();
        }

        if (this.config.animateOut && this.config.animateOut.type && this.config.animateOut.type !== 'none') {
            this.animateOut();
        }
    };
}

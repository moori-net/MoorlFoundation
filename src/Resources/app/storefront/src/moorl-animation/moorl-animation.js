import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlAnimation extends Plugin {
    init() {
        if (!this.el.dataset.moorlAnimation) {
            this.config = this.options;
        } else {
            this.config = JSON.parse(this.el.dataset.moorlAnimation);
        }

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

            if (that.config.hover && that.config.hover.active) {
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

        if (Feature.isActive('v6.5.0.0')) {
            return;
        }

        if (rule === 'isOverBottom') {
            return $(el).isOverBottom();
        }

        if (rule === 'isInViewport') {
            return $(el).isInViewport();
        }
    }

    animateHover() {
        const config = this.config.hover;

        if (this.el.classList.contains('moorl-animation-hidden')) {
            return;
        }

        if (this._visible(config.condition)) {
            this.activeAnimation = 'hover';
            this.el.style.animation = config.name;
            this.el.style.zIndex = 9000;
            this.el.style.animationDelay = this._ms(config.delay);
            this.el.style.animationDuration = this._ms(config.duration);
        }
    };

    animateIn() {
        const config = this.config.in;

        if (!this.el.classList.contains('moorl-animation-hidden')) {
            return;
        }

        if (this._visible(config.condition)) {
            this.activeAnimation = 'in';
            this.el.style.animation = config.name;
            this.el.style.zIndex = 9000;
            this.el.style.animationDelay = this._ms(config.delay);
            this.el.style.animationDuration = this._ms(config.duration);
        }
    };

    animateOut() {
        const config = this.config.out;

        if (this.el.classList.contains('moorl-animation-hidden')) {
            return;
        }

        if (!this._visible(config.condition)) {
            this.activeAnimation = 'out';
            this.el.style.animation = config.name;
            this.el.style.animationDelay = this._ms(config.delay);
            this.el.style.animationDuration = this._ms(config.duration);
        }
    };

    animateInit() {
        const config = this.config;

        if (config.in && config.in.active) {
            if (!this._visible(config.in.condition)) {
                this.el.classList.add('moorl-animation-hidden');
            } else if (config.in.condition === 'isLoaded') {
                this.el.classList.add('moorl-animation-hidden');
            }
        }
    };

    animate() {
        if (this.activeAnimation) {
            return;
        }

        if (this.config.in && this.config.in.active) {
            this.animateIn();
        }

        if (this.config.out && this.config.out.active) {
            this.animateOut();
        }
    };
}

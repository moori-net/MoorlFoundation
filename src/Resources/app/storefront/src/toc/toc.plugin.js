import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlTocPlugin extends Plugin {
    static options = {
        offsetTop: 0
    };

    init() {
        //this._progressBar = document.querySelector('.moorl-toc-progressbar');
        //this._progress = this._progressBar.querySelector('.progress');
        //this._headerElement = document.querySelector('.header-main');
        //this._navElement = document.querySelector('.nav-main');
        this._cmsPageElement = document.querySelector('.cms-page');
        this._headingElements = this._cmsPageElement.querySelectorAll('h2,h3,h5,h5,h6');
        this._selectedTocLink = window.location.hash;
        this._selectedHeadline = null;

        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        window.addEventListener('scroll', event => {
            that._onScroll();
        });
    }

    _onScroll() {
        let scrollTop = document.documentElement.scrollTop || document.body.scrollTop || 0;
        let headline = this._currentSection();
        let tocNavTop = this.el.getBoundingClientRect().top +
            this.el.ownerDocument.defaultView.pageYOffset

        if (headline !== this._selectedTocLink) {
            this._selectedTocLink = headline;
            this._selectTocLink(this._selectedTocLink)
        }

        if (scrollTop < tocNavTop) {
            this.el.style.paddingTop = 0;
        } else {
            this.el.style.paddingTop = this.options.offsetTop + 'px';
        }
    }

    _currentSection() {
        let navHeight = this.options.offsetTop;

        this._headingElements.forEach((el) => {
            if (el.id !== '') {
                if (this._selectedTocLink === '') {
                    this._selectedHeadline = el
                    this._selectedTocLink = ' '
                }
                let top = el.getBoundingClientRect().top;
                if (top <= navHeight + 50) {
                    this._selectedHeadline = el
                }
            }
        });

        if (this._selectedHeadline !== null) {
            return this._selectedHeadline.id;
        }

        return this._selectedTocLink;
    }

    _selectTocLink(anchor) {
        if (anchor.trim() === '') {
            return
        }

        const activeElements = this.el.querySelectorAll('.active');
        activeElements.forEach((el) => {
            el.classList.remove("active");
        });

        const parentElements = this._getParents(this.el.querySelector('a[href="#' + anchor + '"]'), 'li');
        parentElements.forEach((el) => {
            el.classList.add("active");
        });
    }

    _getParents(elem, selector) {
        let parents = [];

        for (; elem && elem !== document; elem = elem.parentNode) {
            if (selector) {
                if (elem.matches(selector)) {
                    parents.push(elem);
                }
                continue;
            }
            parents.push(elem);
        }

        return parents;
    }
}

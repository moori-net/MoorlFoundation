const Plugin = window.PluginBaseClass;

export default class MoorlTocPlugin extends Plugin {
    static options = {
        offsetTop: window.moorlOffsetTop ?? 50
    };

    init() {
        this._cmsPageElement = document.querySelector('.cms-page');
        this._headingElements =
            this._cmsPageElement.querySelectorAll('h2,h3,h5,h5,h6');
        this._selectedTocLink = window.location.hash;
        this._selectedHeadline = null;

        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        window.addEventListener('scroll', (event) => {
            that._onScroll();
        });
    }

    _onScroll() {
        let headline = this._currentSection();
        if (headline !== this._selectedTocLink) {
            this._selectedTocLink = headline;
            this._selectTocLink(this._selectedTocLink);
        }
    }

    _currentSection() {
        this._headingElements.forEach((el) => {
            if (el.id !== '') {
                if (this._selectedTocLink === '') {
                    this._selectedHeadline = el;
                    this._selectedTocLink = ' ';
                }
                let top = el.getBoundingClientRect().top;
                if (top <= this.options.offsetTop) {
                    this._selectedHeadline = el;
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
            return;
        }

        const activeElements = this.el.querySelectorAll('.active');
        activeElements.forEach((el) => {
            el.classList.remove('active');
        });

        const parentElements = this._getParents(
            this.el.querySelector('a[href="#' + anchor + '"]'),
            'li'
        );
        parentElements.forEach((el) => {
            el.classList.add('active');
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

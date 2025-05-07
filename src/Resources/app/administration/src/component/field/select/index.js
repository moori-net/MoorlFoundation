import template from './index.html.twig';

import animateCss from './sets/animate-css.json';
import bsButton from './sets/bs-button.json';
import bsColorScheme from './sets/bs-color-scheme.json';
import displayMode from './sets/display-mode.json';
import flexHorizontalAlign from './sets/flex-horizontal-align.json';
import flexVerticalAlign from './sets/flex-vertical-align.json';
import fontAwesomeIcon from './sets/font-awesome-icon.json';
import itemLayout from './sets/item-layout.json';
import listingLayout from './sets/listing-layout.json';
import listingSource from './sets/listing-source.json';
import cssJustifyContent from './sets/css-justify-content.json';
import navigationArrows from './sets/navigation-arrows.json';
import navigationDots from './sets/navigation-dots.json';
import sliderMode from './sets/slider-mode.json';
import textHorizontalAlign from './sets/text-horizontal-align.json';
import textVerticalAlign from './sets/text-vertical-align.json';
import domElementState from './sets/dom-element-state.json';
import svgShape from './sets/svg-shape.json';
import cssSizeUnit from './sets/css-size-unit.json';
import iconType from './sets/icon-type.json';
import operator from './sets/operator.json';
import cssFlexWrap from './sets/css-flex-wrap.json';
import cssFlexDirection from './sets/css-flex-direction.json';
import cssObjectFit from './sets/css-object-fit.json';
import cssDisplay from './sets/css-display.json';
import timeZone from './sets/time-zone.json';
import visibility from './sets/visibility.json';
import stockType from './sets/stock-type.json';
import countdownType from './sets/countdown-type.json';
import interval from './sets/interval.json';

import bsGridWidth from './sets/bs-grid-width';
import bsGridOrder from './sets/bs-grid-order';
import bsGridColumns from './sets/bs-grid-columns';

const sets = {
    animateCss,
    bsButton,
    bsColorScheme,
    displayMode,
    flexHorizontalAlign,
    flexVerticalAlign,
    fontAwesomeIcon,
    itemLayout,
    listingLayout,
    navigationArrows,
    navigationDots,
    sliderMode,
    textHorizontalAlign,
    textVerticalAlign,
    listingSource,
    cssJustifyContent,
    domElementState,
    svgShape,
    cssSizeUnit,
    iconType,
    operator,
    cssFlexWrap,
    cssFlexDirection,
    cssObjectFit,
    cssDisplay,
    timeZone,
    visibility,
    stockType,
    countdownType,
    interval,
    bsGridWidth,
    bsGridOrder,
    bsGridColumns,
};

const defaults = {
    animateIn: { set: 'animateCss' },
    animateOut: { set: 'animateCss' },
    animateHover: { set: 'animateCss' },
    animateCss: {
        variant: 'small',
        translated: false,
        showClearableButton: true,
    },
    operator: {
        variant: 'small',
        translated: false,
    },
    bsGridWidth: {
        variant: 'small',
        translated: false,
    },
    bsGridOrder: {
        variant: 'small',
        translated: false,
    },
    cssDisplay: {
        showClearableButton: true,
    },
    cssFlexDirection: {
        showClearableButton: true,
    },
    cssJustifyContent: {
        showClearableButton: true,
    },
    cssFlexWrap: {
        showClearableButton: true,
    },
    flexHorizontalAlign: {
        showClearableButton: true,
    },
    flexVerticalAlign: {
        showClearableButton: true,
    },
    textHorizontalAlign: {
        showClearableButton: true,
    },
    textVerticalAlign: {
        showClearableButton: true,
    },
};

Shopware.Component.register('moorl-select-field', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: [String, Array],
            required: false,
            default: undefined,
        },
        multiple: {
            type: Boolean,
            required: false,
            default: false,
        },
        variant: {
            type: String,
            required: false,
            default: undefined,
        },
        label: {
            type: String,
            required: false,
            default: undefined,
        },
        placeholder: {
            type: String,
            required: false,
            default: undefined,
        },
        helpText: {
            type: String,
            required: false,
            default: undefined,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
        showClearableButton: {
            type: Boolean,
            required: false,
            default: undefined,
        },
        set: {
            type: String,
            required: true,
            default: 'customSet',
        },
        filter: {
            type: Array,
            required: false,
            default: [],
        },
        customSet: {
            type: Array,
            required: false,
            default: [],
        },
        snippetPath: {
            type: String,
            required: false,
            default: undefined,
        },
        valueProperty: {
            type: String,
            required: true,
            default: 'value',
        },
        labelProperty: {
            type: String,
            required: true,
            default: 'label',
        },
    },

    computed: {
        vBind() {
            return {
                label: this.currentLabel,
                helpText: this.helpText,
                placeholder: this.currentPlaceholder,
                showClearableButton: this.currentShowClearableButton,
                options: this.options,
                disabled: this.disabled,
                isLoading: this.isLoading,
            };
        },

        componentName() {
            return this.multiple ? 'sw-multi-select' : 'sw-single-select';
        },

        currentValue: {
            get() {
                return this.value;
            },
            set(newValue) {
                this.$emit('update:value', newValue ?? null);
            },
        },

        setOptions() {
            if (this.set === 'customSet') {
                return this.customSet;
            }

            if (sets[this.currentSet] === undefined) {
                return [
                    { value: null, label: `set ${this.currentSet} not found` },
                ];
            }

            return sets[this.currentSet];
        },

        currentSet() {
            if (
                defaults[this.set] === undefined ||
                defaults[this.set].set === undefined
            ) {
                return this.set;
            }

            return defaults[this.set].set;
        },

        currentVariant() {
            if (this.variant) {
                return this.variant;
            }

            if (
                defaults[this.currentSet] === undefined ||
                defaults[this.currentSet].variant === undefined
            ) {
                return 'normal';
            }

            return defaults[this.currentSet].variant;
        },

        currentShowClearableButton() {
            if (this.showClearableButton !== undefined) {
                return this.showClearableButton;
            }

            if (
                defaults[this.currentSet] === undefined ||
                defaults[this.currentSet].showClearableButton === undefined
            ) {
                return false;
            }

            return defaults[this.currentSet].showClearableButton;
        },

        translated() {
            if (this.snippetPath) {
                return true;
            }

            if (
                defaults[this.currentSet] === undefined ||
                defaults[this.currentSet].translated === undefined
            ) {
                return true;
            }

            return defaults[this.currentSet].translated;
        },

        currentLabel() {
            if (this.label) {
                return this.label;
            }

            if (this.currentVariant === 'small') {
                return undefined;
            }

            return this.$tc(`moorl-select.label.${this.set}`);
        },

        currentPlaceholder() {
            if (this.placeholder) {
                return this.placeholder;
            }

            if (this.currentVariant === 'small') {
                return this.$tc(`moorl-select.label.${this.set}`);
            }

            return this.$tc('moorl-select.label.none');
        },

        options() {
            const options = [];

            this.setOptions.forEach((option) => {
                let value = null;
                let label = null;

                if (typeof option !== 'object') {
                    value = option;
                    label = this.getLabel(option);
                } else if (
                    option[this.valueProperty] !== undefined &&
                    option.translated !== undefined &&
                    option.translated[this.labelProperty] !== undefined
                ) {
                    value = option[this.valueProperty];
                    label = option.translated[this.labelProperty];
                } else if (
                    option[this.valueProperty] !== undefined &&
                    option[this.labelProperty] !== undefined
                ) {
                    value = option[this.valueProperty];
                    label = this.translated
                        ? this.$tc(option[this.labelProperty])
                        : this.getLabel(option[this.labelProperty]);
                }

                if (this.filter.length && this.filter.indexOf(value) === -1) {
                    return;
                }

                options.push({ value, label });
            });

            return options;
        },
    },

    methods: {
        getLabel(value) {
            if (this.snippetPath) {
                return this.$tc(`${this.snippetPath}.${value}`);
            }

            return value;
        },
    },
});

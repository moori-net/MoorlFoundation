const {Component} = Shopware;

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
import listingJustifyContent from './sets/listing-justify-content.json';
import navigationArrows from './sets/navigation-arrows.json';
import navigationDots from './sets/navigation-dots.json';
import sliderMode from './sets/slider-mode.json';
import textHorizontalAlign from './sets/text-horizontal-align.json';
import textVerticalAlign from './sets/text-vertical-align.json';
import domElementState from './sets/dom-element-state.json';
import svgShape from './sets/svg-shape.json';
import cssSizeUnit from './sets/css-size-unit.json';
import iconType from './sets/icon-type.json';

import bsGridWith from './sets/bs-grid-width';
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
    listingJustifyContent,
    domElementState,
    svgShape,
    cssSizeUnit,
    iconType,
    bsGridWith,
    bsGridOrder,
    bsGridColumns
};

Component.register('moorl-select', {
    template,

    emits: [
        'update:value',
        'update:config'
    ],

    props: {
        value: {
            type: String,
            required: false,
            default: undefined
        },
        config: {
            type: Object,
            required: false,
            default: null
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
            default: false,
        },
        set: {
            type: String,
            required: true,
            default: 'customSet'
        },
        filter: {
            type: Array,
            required: false,
            default: []
        },
        customSet: {
            type: Array,
            required: false,
            default: []
        },
        snippetPath: {
            type: String,
            required: false,
            default: undefined
        },
        valueProperty: {
            type: String,
            required: true,
            default: 'value'
        },
        labelProperty: {
            type: String,
            required: true,
            default: 'label'
        },
    },

    computed: {
        vBind() {
            return {
                label: this.currentLabel,
                helpText: this.helpText,
                placeholder: this.currentPlaceholder,
                showClearableButton: this.showClearableButton,
                options: this.options,
                disabled: this.disabled,
                isLoading: this.isLoading
            }
        },

        translated() {
            return [
                'animateIn',
                'animateOut',
                'animateHover',
                'bsGridWidth',
                'bsGridOrder'
            ].indexOf(this.set) === -1;
        },

        currentSet() {
            return ['animateIn', 'animateOut', 'animateHover'].indexOf(this.set) === -1 ? this.set : 'animateCss';
        },

        options() {
            const options = [];

            if (typeof sets[this.currentSet] === undefined) {
                return [{value: null, label: `set ${this.currentSet} not found`}];
            }

            sets[this.currentSet].forEach((option) => {
                let value = null;
                let label = null;

                if (typeof option === "string") {
                    value = option;
                    label = this.getLabel(option);
                } else if (
                    typeof option[this.valueProperty] === "string" &&
                    typeof option.translated[this.labelProperty] === "string"
                ) {
                    value = option[this.valueProperty];
                    label = option.translated[this.labelProperty];
                } else if (
                    typeof option[this.valueProperty] === "string" &&
                    typeof option[this.labelProperty] === "string"
                ) {
                    value = option[this.valueProperty];
                    label = this.translated
                        ? this.$tc(option[this.labelProperty])
                        : this.getLabel(option[this.labelProperty]);
                }

                if (this.filter.length && this.filter.indexOf(value) === -1) {
                    return;
                }

                options.push({value, label});
            });

            return options;
        },

        currentLabel() {
            if (this.label) {
                return this.label;
            }

            if (['textHorizontalAlign', 'flexHorizontalAlign'].indexOf(this.set) !== -1) {
                return this.label ?? this.$tc('moorl-select.label.horizontalAlign');
            }

            if (['textVerticalAlign', 'flexVerticalAlign'].indexOf(this.set) !== -1) {
                return this.label ?? this.$tc('moorl-select.label.verticalAlign');
            }

            return this.$tc(`moorl-select.label.${this.set}`);
        },

        currentPlaceholder() {
            return this.placeholder ?? this.showClearableButton ? this.$tc('moorl-select.label.none') : this.currentLabel;
        },

        currentValue: {
            get() {
                return this.value ?? this.config.value;
            },
            set(newValue) {
                if (this.value) {
                    this.$emit('update:value', newValue);
                } else {
                    const newConfig = Object.assign({}, this.config);
                    newConfig.value = newValue;
                    this.$emit('update:config', newConfig);
                }
            }
        }
    },

    created() {
        if (this.customSet.length) {
            sets.customSet = this.customSet;
        }
    },

    methods: {
        getLabel(value) {
            if (this.snippetPath) {
                return this.$tc(`${this.snippetPath}.${value}`);
            }

            return value;
        },
    }
});

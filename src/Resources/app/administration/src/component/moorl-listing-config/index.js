const {Component} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-listing-config', {
    template,

    props: {
        value: {
            type: Object,
            required: false,
            default: {
                listingLayout: {
                    value: 'grid'
                },
                itemLayout: {
                    value: 'overlay'
                },
                itemLayoutTemplate: {
                    value: '@Storefront/storefront/component/product/card/box.html.twig'
                },
                displayMode: {
                    value: 'cover'
                },
                textAlign: {
                    value: 'left'
                },
                gapSize: {
                    value: '20px'
                },
                itemWidth: {
                    value: '300px'
                },
                itemHeight: {
                    value: '400px'
                },
                itemPadding: {
                    value: '0px'
                },
                itemBackgroundColor: {
                    value: null
                },
                itemHasBorder: {
                    value: false
                },
                contentPadding: {
                    value: '20px'
                },
                contentBackgroundColor: {
                    value: null
                },
                contentColor: {
                    value: null
                },
                hasButton: {
                    value: true
                },
                buttonClass: {
                    value: 'btn btn-primary'
                },
                buttonLabel: {
                    value: 'Click here!'
                },
                urlNewTab: {
                    value: true
                },
                speed: {
                    value: 1000
                },
                autoplayTimeout: {
                    value: 3000
                },
                autoplay: {
                    value: true
                },
                autoplayHoverPause: {
                    value: true
                },
                animateIn: {
                    value: null
                },
                animateOut: {
                    value: null
                },
                mode: {
                    value: 'carousel'
                },
                navigationArrows: {
                    value: 'outside'
                },
                navigationDots: {
                    value: null
                },
                mouseDrag: {
                    value: false
                }
            }
        }
    },

    data() {
        return {
            currentValue: null,
            snippetPrefix: 'moorl-listing-config.',
        };
    },

    watch: {
        value: {
            handler(value){
                this.$emit('change', this.value);
                console.log(this.value);
            },
            deep: true
        }
    },

    created() {
        this.currentValue = JSON.parse(JSON.stringify(this.value));
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        },

        elementOptions() {
            const options = {
                listingLayout: [
                    {value: 'grid', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.grid'},
                    {value: 'list', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.list'},
                    {value: 'standard', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.standard'},
                    {value: 'slider', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.slider'}
                ],
                itemLayout: [
                    {value: 'overlay', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.overlay'},
                    {value: 'image-or-title', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.image-or-title'},
                    {value: 'image-content', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.image-content'},
                    {value: 'content-image', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.content-image'},
                    {value: 'avatar', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.avatar'},
                    {value: 'standard', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.standard'},
                    {value: 'custom', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.custom'}
                ],
                displayMode: [
                    {value: 'cover', label: 'sw-cms.elements.moorl-foundation-listing.displayMode.cover'},
                    {value: 'contain', label: 'sw-cms.elements.moorl-foundation-listing.displayMode.contain'},
                    {value: 'standard', label: 'sw-cms.elements.moorl-foundation-listing.displayMode.standard'}
                ],
                textAlign: [
                    {value: 'left', label: 'sw-cms.elements.moorl-foundation-listing.textAlign.left'},
                    {value: 'center', label: 'sw-cms.elements.moorl-foundation-listing.textAlign.center'},
                    {value: 'right', label: 'sw-cms.elements.moorl-foundation-listing.textAlign.right'}
                ],
                mode: [
                    {value: 'carousel', label: 'sw-cms.elements.moorl-foundation-listing.mode.carousel'},
                    {value: 'gallery', label: 'sw-cms.elements.moorl-foundation-listing.mode.gallery'}
                ],
                navigationArrows: [
                    {value: null, label: 'sw-cms.elements.moorl-foundation-listing.none'},
                    {value: 'outside', label: 'sw-cms.elements.moorl-foundation-listing.navigationArrows.outside'},
                    {value: 'inside', label: 'sw-cms.elements.moorl-foundation-listing.navigationArrows.inside'}
                ],
                navigationDots: [
                    {value: null, label: 'sw-cms.elements.moorl-foundation-listing.none'},
                    {value: 'outside', label: 'sw-cms.elements.moorl-foundation-listing.navigationDots.outside'},
                    {value: 'inside', label: 'sw-cms.elements.moorl-foundation-listing.navigationDots.inside'}
                ]
            };

            return options;
        }
    },

    methods: {}
});

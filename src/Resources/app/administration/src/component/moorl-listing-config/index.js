import template from './index.html.twig';
import './index.scss';
import defaultValue from './default.json';

const {Component} = Shopware;
const {cloneDeep} = Shopware.Utils.object;

Component.register('moorl-listing-config', {
    template,

    props: {
        value: {
            type: Object,
            required: false,
            default: {}
        }
    },

    data() {
        return {
            isLoading: true
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
        this.value = Object.assign(
            cloneDeep(defaultValue),
            this.value
        );

        this.isLoading = false;
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
                    {value: 'slider', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.slider'},
                    {value: 'search-suggest', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.search-suggest'},
                ],
                listingJustifyContent: [
                    {value: 'normal', label: 'sw-cms.elements.moorl-foundation-listing.listingJustifyContent.normal'},
                    {value: 'flex-start', label: 'sw-cms.elements.moorl-foundation-listing.listingJustifyContent.flex-start'},
                    {value: 'flex-end', label: 'sw-cms.elements.moorl-foundation-listing.listingJustifyContent.flex-end'},
                    {value: 'center', label: 'sw-cms.elements.moorl-foundation-listing.listingJustifyContent.center'},
                    {value: 'space-between', label: 'sw-cms.elements.moorl-foundation-listing.listingJustifyContent.space-between'},
                    {value: 'space-around', label: 'sw-cms.elements.moorl-foundation-listing.listingJustifyContent.space-around'},
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

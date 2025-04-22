import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-cms-slot-card', {
    template,

    inject: [
        'cmsDataResolverService'
    ],

    mixins: [
        Shopware.Mixin.getByName('cms-element'),
        Shopware.Mixin.getByName('cms-state')
    ],

    props: {
        item: {
            type: Object,
            required: true,
        },
    },

    created() {
        this.cmsPageState.setCurrentPageType("page");

        this.cmsDataResolverService.resolve({sections: [{blocks: [{slots: [this.item]}]}]}).then(() => {
            this.initElementConfig(this.item.type);
            this.initElementData(this.item.type);
        }).catch((exception) => {
            console.error(exception);
        });
    }
});

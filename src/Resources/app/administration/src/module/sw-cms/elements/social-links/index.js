import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-social-links', {
    template,
    mixins: [Shopware.Mixin.getByName('cms-element')],
    computed: {
        links() {
            const links = this.element.config.socialLinks?.value;
            return Array.isArray(links) ? links : [];
        },
    },
    created() {
        this.initElementConfig('moorl-social-links');
        this.initElementData('moorl-social-links');
    },
});

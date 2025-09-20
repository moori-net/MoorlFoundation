import template from './sw-data-grid.html.twig';
import './sw-data-grid.scss';

Shopware.Component.override('sw-data-grid', {
    template,

    methods: {
        getRowClasses(item, itemIndex) {
            const rowClasses = this.$super('getRowClasses', item, itemIndex);

            if (item.direction) {
                rowClasses.push(`is--direction-${item.direction}`);
            }

            return rowClasses;
        },
    }
});

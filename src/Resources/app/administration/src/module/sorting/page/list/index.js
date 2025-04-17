Shopware.Component.extend('moorl-sorting-list', 'moorl-abstract-page-list', {
    data() {
        return {
            entity: 'moorl_sorting',
            properties: [
                'active',
                'priority',
                'entity',
                'label'
            ],
            sortBy: 'entity',
        };
    }
});

Shopware.Component.extend('moorl-client-list', 'moorl-abstract-page-list', {
    data() {
        return {
            entity: 'moorl_client',
            properties: [
                'active',
                'name',
                'type'
            ]
        };
    }
});

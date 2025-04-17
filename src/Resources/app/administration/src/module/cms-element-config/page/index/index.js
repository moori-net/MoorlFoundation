Shopware.Component.extend('moorl-cms-element-config-index', 'moorl-abstract-page-list', {
    data() {
        return {
            entity: 'moorl_cms_element_config',
            properties: [
                'name',
                'type'
            ]
        };
    }
});

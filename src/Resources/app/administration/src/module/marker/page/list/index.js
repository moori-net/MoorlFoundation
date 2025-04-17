Shopware.Component.extend('moorl-marker-list', 'moorl-abstract-page-list', {
    data() {
        return {
            entity: 'moorl_marker',
            properties: [
                'name',
                'type',
                'className'
            ],
            mediaProperty: 'marker',
            pluginName: 'MoorlFoundation',
            demoName: 'marker'
        };
    }
});

const downloadList = {
    downloads: {
        tab: 'general',
        card: 'general',
        entity: 'media',
        componentName: 'sw-entity-multi-id-select',
        attributes: {
            labelProperty: 'filename',
        },
        cmsMappingField: true,
        cols: 12
    },
    emptyText: {
        tab: 'general',
        card: 'general',
        value: 'No downloads available',
    },
    layout: {
        tab: 'general',
        card: 'general',
        value: 'default',
        componentName: 'moorl-select-field',
        attributes: {
            customSet: ['default', 'minimal'],
            snippetPath: 'moorl-foundation.field'
        }
    }
};

export default downloadList;

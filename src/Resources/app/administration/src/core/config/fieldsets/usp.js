const usp = {
    iconActive: {
        value: true,
        tab: 'general',
        card: 'general',
        cols: 12,
    },
    iconClass: {
        value: 'fab|shopware',
        tab: 'general',
        card: 'general',
        cols: 12,
        attributes: {
            placeholder: 'fab|shopware'
        },
        conditions: [{ property: 'iconActive', value: true }],
        cmsMappingField: true
    },
    title: {
        value: 'USP',
        tab: 'general',
        card: 'general',
        cols: 12,
        cmsMappingField: true
    },
    text: {
        value: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr',
        tab: 'general',
        card: 'general',
        cols: 12,
        cmsMappingField: true
    },
    alignment: {
        value: 'left',
        tab: 'general',
        card: 'general',
        cols: 12,
        componentName: 'moorl-select-field',
        attributes: {
            set: 'textHorizontalAlign',
            filter: ['left', 'center'],
        },
    },
    iconColor: {
        value: '#000000',
        tab: 'general',
        card: 'general',
        type: 'color',
    },
    headlineColor: {
        value: '#000000',
        tab: 'general',
        card: 'general',
        type: 'color',
    },
    subHeadlineColor: {
        value: '#000000',
        tab: 'general',
        card: 'general',
        type: 'color',
    },
};

export default usp;

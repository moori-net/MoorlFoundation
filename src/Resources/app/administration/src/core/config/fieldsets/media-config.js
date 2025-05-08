const mediaConfig = {
    cookieConsent: {
        value: true,
        conditions: [{property: 'type', value: 'media', operator: '!='}],
    },
    disablePointerEvents: {
        value: true,
        conditions: [{property: 'type', value: 'media', operator: '!='}],
    },
    video: {
        type: 'json_list',
        value: ['autoplay', 'muted', 'controls'],
        componentName: 'moorl-select-field',
        attributes: {
            multiple: true,
            customSet: [
                'autoplay',
                'loop',
                'muted',
                'controls'
            ]
        },
        conditions: [{property: 'type', value: 'media', operator: 'eq'}],
    },
    preload: {
        value: 'none',
        componentName: 'moorl-select-field',
        attributes: {
            customSet: [
                'none',
                'metadata',
                'auto',
            ]
        },
        conditions: [{property: 'type', value: 'media', operator: 'eq'}],
    }
};

export default mediaConfig;

const mediaConfig = {
    cookieConsent: {
        value: true
    },
    disablePointerEvents: {
        value: true
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
        }
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
        }
    }
};

export default mediaConfig;

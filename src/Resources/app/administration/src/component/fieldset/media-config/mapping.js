const mapping = {
    cookieConsent: {
        value: true
    },
    disablePointerEvents: {
        value: true
    },
    autoPlay: {
        value: true
    },
    autoPause: {
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
    }
};

export default mapping;

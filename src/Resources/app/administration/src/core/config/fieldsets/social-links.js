const socialLinks = {
    eyebrow: {
        value: '',
        tab: 'general',
        card: 'content',
        cols: 12,
    },
    name: {
        value: '',
        tab: 'general',
        card: 'content',
        cols: 12,
    },
    socialLinks: {
        value: [],
        type: 'json_list',
        tab: 'general',
        card: 'contact',
        cols: 12,
        componentName: 'moorl-list-field',
        attributes: {
            min: 0,
            titleAttribute: 'label',
            mapping: {
                label: { value: '', type: 'string', cols: 6 },
                username: { value: '', type: 'string', cols: 6 },
                url: { value: '', type: 'string', cols: 12, componentName: 'mt-url-field' },
            },
        },
    },
};

export default socialLinks;

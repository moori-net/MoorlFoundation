const accordion = {
    name: {
        tab: 'general',
        card: 'content',
        cols: 12,
    },
    entries: {
        value: [],
        tab: 'general',
        card: 'content',
        cols: 12,
        mapping: {
            name: {
                value: 'New entry',
                cols: 8,
            },
            content: {
                value: 'Entry content...',
                type: 'code',
                cols: 12,
            },
            open: {
                value: false,
                type: 'boolean',
                cols: 12,
            },
        }
    },
    verticalAlign: {
        value: 'start',
        tab: 'general',
        card: 'layout',
        componentName: 'moorl-select-field',
        attributes: {
            set: 'flexVerticalAlign'
        }
    },
    autoClose: {
        value: false,
        tab: 'general',
        card: 'layout',
    }
};

export default accordion;

const accordion = {
    name: {
        tab: 'general',
        card: 'general',
        cols: 12,
    },
    verticalAlign: {
        value: 'start',
        tab: 'general',
        card: 'general',
        componentName: 'moorl-select-field',
        attributes: {
            set: 'flexVerticalAlign'
        }
    },
    autoClose: {
        value: false,
        tab: 'general',
        card: 'general',
    },
    entries: {
        value: [],
        tab: 'general',
        card: 'content',
        cols: 12,
        mapping: {
            name: {
                value: 'New entry',
                cols: 12,
            },
            content: {
                value: 'Entry content...',
                type: 'code',
                cols: 12,
            }
        }
    },
};

export default accordion;
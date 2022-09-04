import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-opening-hours-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            timezoneOptions: []
        };
    },

    created() {
        this.loadTimezones();
    },

    methods: {
        loadTimezones() {
            return Shopware.Service('timezoneService').loadTimezones()
                .then((result) => {
                    this.timezoneOptions.push({
                        label: 'UTC',
                        value: 'UTC',
                    });

                    const loadedTimezoneOptions = result.map(timezone => ({
                        label: timezone,
                        value: timezone,
                    }));

                    this.timezoneOptions.push(...loadedTimezoneOptions);
                });
        }
    }
});

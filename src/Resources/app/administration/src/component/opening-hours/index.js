import template from './index.html.twig';
import './index.scss';

const {Component} = Shopware;

Component.register('moorl-opening-hours', {
    template,

    emits: [
        'update:value'
    ],

    props: ['value'],

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
    },

    created() {
        if (!this.value || this.value === true) {
            this.$emit('update:value', [
                {day: 'monday', info: null, times: [{from: '08:00', until: '12:00'}, {from: '14:00', until: '18:00'}]},
                {day: 'tuesday', info: null, times: [{from: '08:00', until: '12:00'}, {from: '14:00', until: '18:00'}]},
                {day: 'wednesday', info: null, times: [{from: '08:00', until: '12:00'}, {from: '14:00', until: '18:00'}]},
                {day: 'thursday', info: null, times: [{from: '08:00', until: '12:00'}, {from: '14:00', until: '18:00'}]},
                {day: 'friday', info: null, times: [{from: '08:00', until: '12:00'}, {from: '14:00', until: '18:00'}]},
                {day: 'saturday', info: null, times: []},
                {day: 'sunday', info: null, times: []}
            ]);
        }
    },

    methods: {
        removeTimes(index) {
            this.value[index].times.pop();
        },

        addTimes(index) {
            this.value[index].times.push({from: null, until: null});
        }
    }
});

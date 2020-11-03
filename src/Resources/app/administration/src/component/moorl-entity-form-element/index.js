import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('moorl-entity-form-element', {
    template,

    props: {
        column: {
            type: Object,
            required: true
        },
        value: {
            type: Object,
            required: true
        },
    },

    created() {
        console.log(this);
    }
});

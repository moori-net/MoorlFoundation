import template from './sw-cms-block-config.html.twig';

const {Component} = Shopware;

Component.override('sw-cms-block-config', {
    template,

    created() {
        if (!this.block.customFields) {
            this.$set(this.block, 'customFields', {});
        }

        if (!this.block.customFields.moorl_block_behaviour) {
            this.$set(this.block.customFields, 'moorl_block_behaviour', {});
        }
    }
});

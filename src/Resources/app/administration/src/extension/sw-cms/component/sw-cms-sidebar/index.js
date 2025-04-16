const {Component} = Shopware;

import template from './sw-cms-sidebar.html.twig';

Component.override('sw-cms-sidebar', {
    template,

    methods: {
        isSectionGrid(section) {
            return (section.type === 'moorl-grid');
        },

        isSectionGridInitialized(section) {
            if (!section.customFields) {
                return false;
            }
            if (!section.customFields.moorl_section_column_count) {
                return false;
            }
            if (!section.customFields.moorl_section_grid_config) {
                return false;
            }
            if (!section.customFields.moorl_section_grid_config[0]?.value?.xl?.width) {
                return false;
            }
            return true;
        },

        sectionGridConfig(section) {
            return section.customFields.moorl_section_grid_config;
        },

        sectionColumnCount(section) {
            return parseInt(section.customFields.moorl_section_column_count, 10);
        },

        getSectionGridContentBlocks(sectionBlocks, gridCol) {
            const sectionPosition = 'moorl_grid_' + (gridCol - 1);

            return sectionBlocks.filter(
                (block) => this.blockTypeExists(block.type) && block.sectionPosition === sectionPosition
            );
        },

        getSectionGridDragData(block, sectionIndex, gridCol) {
            return {
                delay: 300,
                dragGroup: 'cms-navigator',
                data: {block, sectionIndex, gridCol},
                validDragCls: null,
                onDragEnter: this.onBlockDragSort,
                onDrop: this.onBlockDragStop,
            };
        },

        getSectionGridDropData(block, sectionIndex, gridCol) {
            block.sectionPosition = 'moorl_grid_' + (gridCol - 1);

            return {
                dragGroup: 'cms-navigator',
                data: {block, sectionIndex, gridCol},
                onDrop: this.onBlockDropAbort,
            };
        }
    },
});

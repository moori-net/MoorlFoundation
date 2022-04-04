const {Component} = Shopware;
const {cloneDeep} = Shopware.Utils.object;
const types = Shopware.Utils.types;

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
            return parseInt(section.customFields.moorl_section_column_count);
        },

        onBlockStageDrop(dragData, dropData) {
            return this.$super('onBlockStageDrop', dragData, dropData);


            if (!dropData || !dragData.block || dropData.dropIndex < 0 || !dropData.section) {
                return;
            }

            if (dropData.section.type !== 'moorl-grid') {
                return this.$super('onBlockStageDrop', dragData, dropData);
            }

            const section = dropData.section;
            const blockConfig = this.cmsBlocks[dragData.block.name];
            const newBlock = this.blockRepository.create();

            newBlock.type = dragData.block.name;
            newBlock.position = dropData.dropIndex;
            newBlock.sectionPosition = dropData.sectionPosition;
            newBlock.sectionId = section.id;

            Object.assign(
                newBlock,
                cloneDeep(this.blockConfigDefaults),
                cloneDeep(blockConfig.defaultConfig || {}),
            );

            Object.keys(blockConfig.slots).forEach((slotName) => {
                const slotConfig = blockConfig.slots[slotName];
                const element = this.slotRepository.create();
                element.blockId = newBlock.id;
                element.slot = slotName;

                if (typeof slotConfig === 'string') {
                    element.type = slotConfig;
                } else if (types.isPlainObject(slotConfig)) {
                    element.type = slotConfig.type;

                    if (slotConfig.default && types.isPlainObject(slotConfig.default)) {
                        Object.assign(element, cloneDeep(slotConfig.default));
                    }
                }

                newBlock.slots.add(element);
            });

            this.page.sections[section.position].blocks.splice(dropData.dropIndex, 0, newBlock);

            this.$emit('block-stage-drop');
            this.$emit('current-block-change', section.id, newBlock);
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

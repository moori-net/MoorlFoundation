import template from './sw-cms-section.html.twig';
import moorlGridDefault from './moorl-grid.default.json';
import './sw-cms-section.scss';
/*import './sw-cms-section-config';*/
import './sw-cms-section-actions';

const {cloneDeep} = Shopware.Utils.object;

Shopware.Component.override('sw-cms-section', {
    template,

    computed: {
        isSectionGrid() {
            return (this.section.type === 'moorl-grid');
        },

        isSectionGridInitialized() {
            if (!this.section.customFields) {
                return false;
            }
            if (!this.section.customFields.moorl_section_column_count) {
                return false;
            }
            if (!this.section.customFields.moorl_section_grid_config) {
                return false;
            }
            if (!this.section?.customFields?.moorl_section_grid_config[0]?.value?.xl?.width) {
                return false;
            }
            return true;
        },

        sectionGridConfig() {
            return this.section.customFields.moorl_section_grid_config;
        },

        sectionColumnCount() {
            return parseInt(this.section.customFields.moorl_section_column_count, 10);
        },

        sectionSpacing() {
            return parseInt(this.section.customFields.moorl_section_spacing, 10);
        },

        sectionSpacingStyle() {
            return "--section-spacing: " + (this.sectionSpacing / 2) + "px;";
        }
    },

    methods: {
        createdComponent() {
            /* https://github.com/shopware/platform/issues/2989 */
            this.initSectionGrid();
            this.$super('createdComponent');
        },

        initSectionGrid() {
            if (!this.isSectionGrid) {
                return;
            }

            if (!this.isSectionGridInitialized) {
                Object.assign(
                    this.section,
                    cloneDeep(moorlGridDefault)
                );
            }
        },

        isGridContentEmpty(index) {
            return this.sectionGridBlocks(index).length === 0;
        },

        sectionGridBlocks(gridCol) {
            const sectionPosition = 'moorl_grid_' + gridCol;

            return this.section.blocks.filter(
                (block) => this.blockTypeExists(block.type) && block.sectionPosition === sectionPosition
            );
        },

        getSectionGridStyle(index) {
            const configValue = this.sectionGridConfig[index].value;
            let rows = configValue.xl?.width || 3;
            let rowWidth = (100 / 12).toFixed(2);
            if (this.cmsPageState.currentCmsDeviceView === 'tablet-landscape') {
                rows = configValue.md?.width || 3;
            }
            if (this.cmsPageState.currentCmsDeviceView === 'mobile') {
                rows = configValue.xs?.width || 3;
            }
            let width = parseInt(rows, 10) * rowWidth;

            return {
                flex: "0 0 " + width + "%",
                maxWidth: width + "%",
                justifyContent: this.sectionGridConfig[index].justifyContent,
                alignItems: this.sectionGridConfig[index].alignItems,
            }
        },

        getDropGridData(index, gridCol = 0) {
            const sectionPosition = 'moorl_grid_' + gridCol;

            return {
                dropIndex: index,
                section: this.section,
                sectionPosition,
                gridCol
            };
        },
    }
})

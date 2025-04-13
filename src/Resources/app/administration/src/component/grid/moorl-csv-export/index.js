import template from './index.html.twig';
import './index.scss';
import Papa from 'papaparse';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('moorl-csv-export', {
    template,

    inject: [
        'repositoryFactory',
        'context',
        'mediaService'
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        entity: {
            type: String,
            required: true
        },
        onCloseModal: {
            type: Function,
            required: true,
            default: null
        },
        criteria: {
            type: Object,
            required: false,
            default() {
                return new Criteria();
            }
        },
        selectedItems: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        },
        defaultItem: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        }
    },

    data() {
        return {
            step: 1,
            options: {
                localTable: true
            },
            items: [],
            exportItems: [],
            tags: null,
            rowCount: 0,
            rowsDone: 0,
            rowsLeft: 0,
            rowsSkipped: 0,
            rowsNew: 0,
            errorCount: 0,
            data: null,
            properties: {},
            columns: null,
            showExportModal: true,
            statusMessage: null,
            total: 0,
            page: 1,
            limit: 50
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create(this.entity);
        },
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
        tagRepository() {
            return this.repositoryFactory.create('tag');
        },
    },

    created() {
        this.createdComponent();
    },

    mounted() {},

    watch: {},

    methods: {
        createdComponent() {
            this.initExportColumns();
        },

        async getItems() {
            const criteria = this.criteria;

            criteria.setLimit(this.limit);
            criteria.setPage(this.page);

            await this.repository.search(criteria, Shopware.Context.api).then(async (items) => {
                //

                this.items = [...this.items, ...items];
                this.total = items.total;
                if (this.total > this.items.length) {
                    this.page++;

                    await this.getItems();
                }
            }).catch(() => {
                this.isLoading = false;
            });
        },

        sanitizeItems() {
            do {
                let item = this.items.shift();

                this.exportItems.push(this.sanitizeItem(item));
            } while (this.items.length > 0);

            //
        },

        sanitizeItem(item) {
            //

            const exportItem = {};

            for (let column of this.columns) {
                if (column.relation === 'many_to_many' || column.relation === 'one_to_many') {
                    if (item[column.property] && item[column.property].length > 0) {
                        /*

                        */

                        if (column.entity === 'tag') {
                            exportItem[column.property] = item[column.property].map((item) => {
                                return item.name
                            }).join("|");
                        } else {
                            exportItem[column.property] = item[column.property].map((item) => {
                                return item.id
                            }).join("|");
                        }
                    } else {
                        exportItem[column.property] = null;
                    }
                } else if (column.relation === 'one_to_one' || column.relation === 'many_to_one') {
                    exportItem[column.localField] = item[column.localField];

                    if (item[column.property]) {
                        if (column.entity === 'media' && item[column.property].url) {
                            exportItem[column.property] = item[column.property].url;
                        }
                    }
                } else if (column.type === 'json_object') {
                    exportItem[column.property] = JSON.stringify(item[column.property]);
                } else {
                    exportItem[column.property] = item[column.property];
                }
            }

            //

            return exportItem;
        },

        onCancel() {
            this.onCloseModal();
        },

        initExportCriteria() {
            this.criteria.setTotalCountMode(1);

            for (let column of this.columns) {
                if (column.relation) {
                    this.criteria.addAssociation(column.property);
                }
            }

            if (this.selectedItems) {
                let ids = Object.keys(this.selectedItems);

                if (ids.length > 0) {
                    this.criteria.addFilter(Criteria.equalsAny('id', ids));
                }
            }

            if (this.options.localTable && this.defaultItem) {
                for (const [field, value] of Object.entries(this.defaultItem)) {
                    this.criteria.addFilter(Criteria.equals(field, value));
                }
            }
        },

        initExportColumns() {
            let columns = [];
            let properties = Shopware.EntityDefinition.get(this.entity).properties

            for (const [property, column] of Object.entries(properties)) {
                if (!column.flags.moorl_edit_field && !column.flags.primary_key) {
                    continue;
                }

                if (column.localField) {
                    if (properties[column.localField].flags.required) {
                        column.flags.required = true;
                    }
                }

                column.property = property;
                column.label = this.$tc(`moorl-foundation.properties.${property}`);
                columns.push(column);
            }

            //

            this.columns = columns;
        },

        async onClickExport() {
            this.initExportCriteria();

            this.step = 2;

            await this.getItems();

            this.sanitizeItems();

            //
            //

            let csv = Papa.unparse(this.exportItems, {delimiter: ";"});
            const blob = new Blob([csv]);

            if (window.navigator.msSaveOrOpenBlob) {
                window.navigator.msSaveBlob(blob, this.entity + ".csv");
            } else {
                let a = window.document.createElement("a");
                a.href = window.URL.createObjectURL(blob, {type: "text/plain"});
                a.download = this.entity + ".csv";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }

            this.step = 3;
        }
    }
});

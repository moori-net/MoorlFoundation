import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

const Papa = require('papaparse');

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
            type: Array,
            required: false,
            default: []
        },
        defaultItem: {
            type: Object,
            required: false,
            default: {}
        }
    },

    data() {
        return {
            step: 1,
            options: {},
            items: null,
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
            this.initExportCriteria();
        },

        async getItems() {
            await this.repository.search(this.criteria, Shopware.Context.api).then((items) => {
                console.log(items);

                this.total = items.total;
                this.items = items;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        sanitizeItems() {
            do {
                let item = this.items.shift();

                this.exportItems.push(this.sanitizeItem(item));
            } while (this.items.length > 0);

            console.log("sanitizeItems", this.exportItems);
        },

        sanitizeItem(item) {
            console.log("sanitizeItem", item);

            const exportItem = {};

            for (let column of this.columns) {
                if (column.relation === 'many_to_many' || column.relation === 'one_to_many') {
                    if (item[column.property] && item[column.property].length > 0) {
                        if (column.entity === 'tag') {
                            exportItem[column.property] = item[column.property].map((item) => {
                                return item.name
                            }).join("|");
                        } else {
                            exportItem[column.property] = item[column.property].keys().join("|");
                        }
                    }
                } else if (column.relation === 'one_to_one' || column.relation === 'many_to_one') {
                    exportItem[column.localField] = item[column.localField];

                    if (item[column.property]) {
                        if (column.entity === 'media' && item[column.property].url) {
                            exportItem[column.property] = item[column.property].url;
                        }
                    }
                } else {
                    exportItem[column.property] = item[column.property];
                }
            }

            console.log("--- sanitizeItem", exportItem);

            return exportItem;
        },

        onCancel() {
            this.onCloseModal();
        },

        initExportCriteria() {
            for (let column of this.columns) {
                if (column.relation) {
                    this.criteria.addAssociation(column.property);
                }
            }

            if (this.selectedItems.length > 0) {
                this.criteria.addFilter(Criteria.equalsAny('id', this.selectedItems.keys()));
            }
        },

        initExportColumns() {
            let columns = [];
            let properties = Shopware.EntityDefinition.get(this.entity).properties

            for (const [property, column] of Object.entries(properties)) {
                if (!column.flags.moorl_edit_field && !column.flags.primary_key) {
                    continue;
                }
                if (Object.keys(this.defaultItem).indexOf(property) !== -1) {
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

            this.columns = columns;
        },

        async onClickExport() {
            this.step = 2;
            await this.getItems();

            this.sanitizeItems();

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
        },

        initSelectedItem() {
            this.selectedItem = Object.assign({}, this.defaultItem);

            for (let column of this.columns) {
                if (column.relation === 'many_to_many' || column.relation === 'one_to_many') {
                    let repository = this.repositoryFactory.create(column.entity);

                    this.selectedItem[column.property] = new Shopware.Data.EntityCollection(
                        repository.route,
                        repository.entityName,
                        Shopware.Context.api
                    );
                }
            }
        },

        async _sanitizeItem(item) {
            console.log("sanitizeItem() ", item);

            const that = this;

            const newItem = {};
            const isBool = /^\s*(true|1|on|yes|ja|an|si|x|check)\s*$/i; // boolean check
            const isUuid = /^[a-f0-9]{32}$/i; // uuid check

            for (const [newProperty, property] of Object.entries(this.mapping)) {
                const currentValue = item[property];

                if (currentValue) {
                    const column = this.columns.find(column => {
                        return (column.property === newProperty || column.localField === newProperty);
                    });

                    if (!column) {
                        return this.onError(newProperty + " - import validation error: unknown column");
                    }

                    // database exports have uppercase ids
                    const currentUuid = (typeof currentValue === 'string' && isUuid.test(currentValue)) ? currentValue.toLowerCase() : null;

                    switch (column.type) {
                        case 'association':
                            if (column.relation === 'one_to_one' || column.relation === 'many_to_one') {
                                if (column.entity === 'media') {
                                    const newMediaItem = this.mediaRepository.create(Shopware.Context.api);
                                    const mediaUrl = new URL(currentValue);
                                    const file = mediaUrl.pathname.split('/').pop().split('.');

                                    if (file.length === 1) {
                                        newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                    } else {
                                        newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                        newMediaItem.fileExtension = file.pop();
                                    }
                                    newMediaItem.mediaFolderId = this.options.mediaFolderId;
                                    let mediaId = await this.getMediaIdByFileName(newMediaItem.fileName);

                                    if (mediaId) {
                                        newItem[column.localField] = mediaId;
                                    } else {
                                        newItem[column.localField] = newMediaItem.id;
                                        this.mediaRepository.save(newMediaItem, Shopware.Context.api).then(() => {
                                            this.mediaService.uploadMediaFromUrl(
                                                newMediaItem.id,
                                                mediaUrl,
                                                newMediaItem.fileExtension,
                                                newMediaItem.fileName
                                            );
                                        });
                                    }
                                } else if (!this.options.validateIds || await this.getItemById(column.entity, currentUuid)) {
                                    newItem[newProperty] = currentUuid;
                                } else {
                                    return this.onError(newProperty + " - import " + column.entity + " validation error: unknown ID (" + currentUuid + ")");
                                }
                            } else if (column.relation === 'many_to_many' || column.relation === 'one_to_many') {
                                let parts = currentValue.toLowerCase().split("|");

                                if (parts.length !== 0) {
                                    newItem[newProperty] = [];

                                    for (const uuid of parts) {
                                        const isValidId = (isUuid.test(uuid) && (!this.options.validateIds || await that.getItemById(column.entity, uuid)));

                                        if (isValidId) {
                                            newItem[newProperty].push({ id: uuid });
                                        } else if (column.entity === 'tag') {
                                            // search for tags, if not found create new
                                            let tagMatch = false;

                                            for (let tag of that.tags) {
                                                if (tag.name === uuid) {
                                                    newItem[newProperty].push(tag);
                                                    tagMatch = true;
                                                }
                                            }

                                            if (tagMatch === false) {
                                                const tag = that.tagRepository.create(Shopware.Context.api);
                                                tag.name = uuid;
                                                tag.id = Shopware.Utils.createId();
                                                await that.tagRepository.save(tag, Shopware.Context.api);
                                                that.tags.add(tag);
                                                newItem[newProperty].push(tag);
                                            }
                                        } else {
                                            return that.onError(newProperty + " - import validation error: unknown/invalid ID (" + uuid + ")");
                                        }
                                    }
                                }
                            }
                            break;
                        case 'boolean':
                            newItem[newProperty] = isBool.test(currentValue);
                            break;
                        case 'int':
                            newItem[newProperty] = parseInt(currentValue);
                            break;
                        case 'float':
                            newItem[newProperty] = parseFloat(currentValue);
                            break;
                        case 'uuid':
                            if (currentUuid) {
                                newItem[newProperty] = currentUuid;
                            } else {
                                return this.onError(newProperty + " - import validation error: unknown ID (" + currentUuid + ")");
                            }
                            break;
                        case 'date':
                            break;
                        default:
                            newItem[newProperty] = currentValue;
                    }
                }
            }

            return newItem;
        }
    }
});

import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

const Papa = require('papaparse');

Component.register('moorl-csv-import', {
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
        onFinishImport: {
            type: Function,
            required: true,
            default: null
        },
        defaultValues: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        }
    },

    data() {
        return {
            pause: false,
            step: 1,
            options: {
                overwrite: true,
                pause: true,
                mediaFolderId: null
            },
            rowCount: 0,
            rowsDone: 0,
            rowsLeft: 0,
            rowsSkipped: 0,
            rowsNew: 0,
            errorCount: 0,
            defaultValues: this.defaultValues,
            cDefaultValues: this.defaultValues,
            data: null,
            properties: {},
            mapping: {},
            columns: null,
            showImportModal: true,
            statusMessage: null
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create(this.entity);
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        }
    },

    created() {
        this.createdComponent();
    },

    mounted() {},

    watch: {},

    methods: {
        async getItemById(entity, id) {
            if (typeof id == 'undefined') {
                return null;
            }

            let item = null;

            if (typeof entity != 'undefined' && entity !== this.entity) {
                this.repositoryFactory.create('entity').get(id, Shopware.Context.api, new Criteria())
                    .then((entity) => {
                        item = entity;
                    });
            } else {
                this.repository
                    .get(id, Shopware.Context.api, new Criteria())
                    .then((entity) => {
                        item = entity;
                    });
            }

            return item ? item : null;
        },

        async getItemByUniqueProperties(item) {
            let multiFromImport = [];
            let multiFromDefaultValues = [];

            for (let column of this.columns) {
                if (column.flags.moorl_unique) {
                    multiFromImport.push(Criteria.equals(column.property, item[column.property]));
                }
            }

            for (const [property, value] of Object.entries(this.cDefaultValues)) {
                multiFromDefaultValues.push(Criteria.equals(property, value))
            }

            const criteria = new Criteria(1, 1);

            criteria.addFilter(Criteria.multi('AND', [
                Criteria.multi('OR', multiFromImport),
                Criteria.multi('AND', multiFromDefaultValues)
            ]));

            let entity = null;
            await this.repository.search(criteria, Shopware.Context.api).then((result) => {
                entity = result.first();
            });
            return entity;
        },

        async getMediaIdByFileName(filename) {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('fileName', filename));
            let media = null;
            await this.mediaRepository.search(criteria, Shopware.Context.api).then((result) => {
                media = result.first();
            });
            return media ? media.id : null;
        },

        createdComponent() {
            this.initEditColumns();
        },

        initEditColumns() {
            let columns = [];
            let properties = Shopware.EntityDefinition.get(this.entity).properties

            for (const [property, item] of Object.entries(properties)) {
                if (!item.flags.moorl_edit_field && !item.flags.primary_key) {
                    continue;
                }
                if (Object.keys(this.defaultValues).indexOf(property) !== -1) {
                    continue;
                }
                item.property = property;
                item.label = this.$tc(`moorl-foundation.properties.${property}`);
                columns.push(item);
            }

            console.log(columns);

            this.columns = columns;
        },

        getUniquePropertyLabels() {
            let elements = [];
            for (let column of this.columns) {
                if (column.flags.primary_key || column.flags.moorl_unique) {
                    elements.push(column.label);
                }
            }
            return elements.join(', ');
        },

        validateCsv() {
            const that = this;

            this.properties = Object.keys(this.data[0]);
            this.matches = 0;

            for (let column of this.columns) {
                let indexOf = (arr, q) => arr.findIndex(item => q.toLowerCase() === item.toLowerCase());
                let result = indexOf(that.properties, column.property);

                if (result != -1) {
                    that.mapping[column.property] = that.properties[result];
                    that.matches++;
                }
            }
        },

        onClickUpload() {
            this.$refs.fileInput.click();
        },

        onFileInputChange() {
            const that = this;
            Papa.parse(this.$refs.fileInput.files[0], {
                header: true,
                skipEmptyLines: true,
                complete: function (results, file) {
                    console.log("NOTICE: Parsing complete", results, file);
                    that.data = results.data;
                    that.validateCsv();
                    that.$refs.fileForm.reset();

                    that.rowCount = that.data.length;
                    that.rowsDone = 0;
                    that.step = 2;
                }
            });
        },

        onClickBack() {
            this.step--;
        },

        onClickPause() {
            this.pause = !this.pause;

            // resuming import
            if (!this.pause) {
                this.importCsvRow();
            }
        },

        mountedComponent() {},

        onClickImport() {
            this.createSystemNotificationSuccess({
                title: this.$t('moorl-foundation.import.importTitle'),
                message: this.$t('moorl-foundation.import.importText'),
            });

            this.step = 3;

            this.importCsvRow();
        },

        async prepareSaveItem(srcItem) {
            const item = Object.assign({}, this.defaultValues, srcItem)
            console.log("prepareSaveItem()", item);

            let entity = await this.getItemById(this.entity, item.id);

            if (!entity) {
                entity = await this.getItemByUniqueProperties(item);
            }

            if (!entity) {
                entity = this.repository.create(Shopware.Context.api);
                this.rowsNew++;
            } else {
                if (!this.options.overwrite) {
                    this.statusMessage = 'Error: (' + this.getUniquePropertyLabels() + ') is already in Database. Please chose overwrite and try again';
                    this.pause = true;
                    this.rowsSkipped++;
                    await this.importCsvRow();
                    return;
                }
            }

            item.id = entity.id;
            Object.assign(entity, item);

            this.saveItem(entity);
        },

        saveItem(item) {
            console.log("saveItem()", item);

            this.repository
                .save(item, Shopware.Context.api)
                .then(() => {
                    this.statusMessage = this.rowsDone + ' of ' + this.rowCount + ' done';
                    this.rowsDone++;
                    this.importCsvRow()
                }).catch((exception) => {
                    this.statusMessage = exception;
                    this.pause = true;
                    this.errorCount++;
                });
        },

        async importCsvRow() {
            if (this.pause && this.options.pause) {
                return;
            }

            this.pause = false;
            this.rowsLeft = this.data.length;

            if (this.rowsLeft < 1) {
                this.step = 4;
                this.onFinishImport();
            }

            let item = this.data.shift();
            item = await this.sanitizeItem(item);

            this.prepareSaveItem(item);
        },

        async sanitizeItem(item) {
            console.log("sanitizeItem() ", item);

            const that = this;
            let regex = /^\s*(true|1|on|yes|ja|an)\s*$/i; // For Type = boolean
            let newItem = {};

            for (const [newProperty, property] of Object.entries(this.mapping)) {
                if (typeof property == 'string') {
                    const column = this.columns.find(column => { return column.property === newProperty });

                    switch (column.type) {
                        case 'association':
                            if (column.relation === 'many_to_one') {
                                if (column.entity === 'media' && item[property].length > 0) {
                                    if (!that.mapping[column.localField] || that.mapping[column.localField].length !== 32) {
                                        const newMediaItem = that.mediaRepository.create(Shopware.Context.api);
                                        const mediaUrl = new URL(item[property]);
                                        const file = mediaUrl.pathname.split('/').pop().split('.');

                                        if (file.length === 1) {
                                            newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                        } else {
                                            newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                            newMediaItem.fileExtension = file.pop();
                                        }
                                        newMediaItem.mediaFolderId = this.options.mediaFolderId;
                                        let mediaId = await that.getMediaIdByFileName(newMediaItem.fileName);

                                        if (mediaId) {
                                            newItem[column.localField] = mediaId;
                                        } else {
                                            newItem[column.localField] = newMediaItem.id;
                                            that.mediaRepository.save(newMediaItem, Shopware.Context.api).then(() => {
                                                that.mediaService.uploadMediaFromUrl(
                                                    newMediaItem.id,
                                                    mediaUrl,
                                                    newMediaItem.fileExtension,
                                                    newMediaItem.fileName
                                                );
                                            });
                                        }
                                    }
                                }
                            } else if (column.relation === 'many_to_many') {
                                let parts = item[property].split("|");
                                if (parts[0].length === 32) {
                                    newItem[newProperty] = parts.map(function (id) {
                                        if (that.getItemById(column.entity, id)) {
                                            return {id: id};
                                        } else {
                                            that.statusMessage = newProperty + " - import validation error: unknown ID (" + id + ")";
                                            that.pause = true;
                                            return false;
                                        }
                                    });
                                } else if (parts[0].length > 0) {
                                    // TODO: Try to auto add new Entities by name
                                    newItem[newProperty] = [];
                                }
                            }
                            break;
                        case 'boolean':
                            if (['1', '0'].indexOf(property) !== -1) {
                                newItem[newProperty] = regex.test(property);
                            } else {
                                newItem[newProperty] = regex.test(item[property]);
                            }
                            break;
                        case 'int':
                            newItem[newProperty] = parseInt(item[property]);
                            break;
                        case 'float':
                            newItem[newProperty] = parseFloat(item[property]);
                            break;
                        case 'uuid':
                            if (item[property].length === 32) {
                                newItem[newProperty] = item[property];
                            }
                            break;
                        case 'date':
                            break;
                        default:
                            newItem[newProperty] = item[property];
                    }
                }
            }

            return newItem;
        }
    }
});

import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;
const utils = Shopware.Utils;

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
        },

        callbackFunction: {
            type: Function,
            required: false,
            default: null
        },

        sanitizeFunction: {
            type: Function,
            required: false,
            default: null
        },
    },

    data() {
        return {
            pause: false,
            step: 1,
            options: {
                overwrite: true,
                pause: true,
            },
            rowCount: 0,
            rowsDone: 0,
            defaultValues: this.defaultValues,
            entityName: this.entity,
            csv: {
                data: null,
                properties: {},
                mapping: {}
            },
            columns: null,
            showImportModal: true,
            showDefaultValues: true
        };
    },

    computed: {
        editColumns() {
            return this.initEditColumns(null);
        },

        getUniqueProperties() {
            let elements = [];

            for (let item of this.editColumns) {
                if (item.flags.primary_key || item.flags.moorl_unique) {
                    elements.push(item.label);
                }
            }

            return elements.join(', ');
        },

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
        createdComponent() {
            console.log('createdComponent()');

            this.columns = Shopware.EntityDefinition.get(this.entityName).properties;
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

            return columns;
        },

        validateCsv() {
            const that = this;

            this.csv.properties = Object.keys(this.csv.data[0]);
            this.csv.matches = 0;

            for (let property in this.columns) {
                let indexOf = (arr, q) => arr.findIndex(item => q.toLowerCase() === item.toLowerCase());
                let result = indexOf(that.csv.properties, property);

                if (result != -1) {
                    that.csv.mapping[property] = that.csv.properties[result];
                    that.csv.matches++;
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
                    that.csv.data = results.data;
                    that.validateCsv();
                    that.$refs.fileForm.reset();

                    that.rowCount = that.csv.data.length;
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
                title: this.$t('moorl-foundation.notification.importTitle'),
                message: this.$t('moorl-foundation.notification.importText'),
            });

            this.step = 3;

            this.importCsvRow();
        },

        async getItemById(id) {
            if (typeof id == 'undefined') {
                return null;
            }
            // TODO: Add multifilter by unique properties
            /*criteria.addFilter(Criteria.multi('OR', [
                Criteria.equals('id', id),
                Criteria.equals('originId', id)
            ]));*/
            let item = null;

            this.repository
                .get(id, Shopware.Context.api, new Criteria())
                .then((entity) => {
                    item = entity;
                });

            return item ? item : null;
        },

        async prepareSaveItem(srcItem) {
            const item = Object.assign({}, this.defaultValues, srcItem)

            console.log("prepareSaveItem()", item);
            let entity = await this.getItemById(item.id);
            if (!entity) {
                entity = this.repository.create(Shopware.Context.api);
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
                    if (this.csv.data.length !== 0) {
                        this.createNotificationSuccess({
                            title: this.$t('moorl-foundation.notification.progressTitle'),
                            message: this.$t('moorl-foundation.notification.progressText', 0, {
                                remaining: this.csv.data.length
                            })
                        });

                        this.rowsDone++;

                        this.importCsvRow();
                    } else {
                        this.isLoading = false;
                        this.createSystemNotificationSuccess({
                            title: this.$t('moorl-foundation.notification.successTitle'),
                            message: this.$t('moorl-foundation.notification.successText')
                        });

                        this.step = 4;

                        this.onFinishImport();
                    }
                }).catch((exception) => {
                    this.pause = true;

                    console.log(exception);
                    this.createNotificationError({
                        title: this.$t('moorl-foundation.notification.errorTitle'),
                        message: exception
                    });
                });
        },

        async importCsvRow() {
            if (this.pause) {
                return;
            }

            let item = this.csv.data.shift();
            item = await this.sanitizeItem(item);

            this.prepareSaveItem(item);
        },

        async sanitizeItem(item) {
            console.log("sanitizeItem() ", item);

            const that = this;
            let regex = /^\s*(true|1|on|yes|ja|an)\s*$/i; // For Type = boolean
            let newItem = {};

            console.log(this.csv);

            for (let csvProperty in this.csv.mapping) {
                if (typeof that.csv.mapping[csvProperty] == 'string') {
                    let property = this.columns[csvProperty];

                    switch (property.type) {
                        case 'association':
                            if (property.relation == 'many_to_one') {
                                if (property.entity == 'media' && item[that.csv.mapping[csvProperty]].length > 0) {
                                    if (!that.csv.mapping[property.localField] || that.csv.mapping[property.localField].length !== 32) {
                                        const newMediaItem = that.mediaRepository.create(Shopware.Context.api);
                                        const mediaUrl = new URL(item[that.csv.mapping[csvProperty]]);
                                        const file = mediaUrl.pathname.split('/').pop().split('.');

                                        if (file.length === 1) {
                                            newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                        } else {
                                            newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                            newMediaItem.fileExtension = file.pop();
                                        }
                                        newMediaItem.mediaFolderId = this.csv.options.mediaFolderId;
                                        let mediaId = await that.getMediaIdByFileName(newMediaItem.fileName);

                                        if (mediaId) {
                                            newItem[property.localField] = mediaId;
                                        } else {
                                            newItem[property.localField] = newMediaItem.id;
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
                            } else if (property.relation == 'many_to_many') {
                                // Split string - If uuid then ok, if not uuid then get uuid from entity name
                                let parts = item[that.csv.mapping[csvProperty]].split("|");
                                if (parts[0].length == 32) {
                                    newItem[csvProperty] = parts.map(function (id) {
                                        // TODO: Clean validation of all relationsship data
                                        if (!that.collections[property.entity].has(id)) {
                                            that.createNotificationError({
                                                title: 'ERROR',
                                                message: csvProperty + " - import validation error: unknown ID (" + id + ")",
                                                autoClose: false
                                            });
                                            return false;
                                        } else {
                                            return {id: id};
                                        }
                                    });
                                } else if (parts[0].length > 0) {
                                    // TODO: Try to auto add new Entities by name
                                    if (property.entity == 'tag') {
                                        let tagsCollection = [];
                                        that.tags.forEach(function (tag) {
                                            for (let i = 0; i < parts.length; i++) {
                                                if (tag.name == parts[i]) {
                                                    tagsCollection.push(tag);
                                                    parts.splice(i, 1);
                                                }
                                            }
                                        });
                                        for (let i = 0; i < parts.length; i++) {
                                            let result = that.tagRepository.create(Shopware.Context.api);
                                            result.name = parts[i];
                                            result.id = Shopware.Utils.createId();
                                            that.tagRepository.save(result, Shopware.Context.api);
                                            that.tags.add(result);
                                            tagsCollection.push(result);
                                        }
                                        newItem[csvProperty] = tagsCollection;
                                    }
                                }
                            }
                            break;
                        case 'boolean':
                            if (['1', '0'].indexOf(that.csv.mapping[csvProperty]) != -1) {
                                newItem[csvProperty] = regex.test(that.csv.mapping[csvProperty]);
                            } else {
                                newItem[csvProperty] = regex.test(item[that.csv.mapping[csvProperty]]);
                            }
                            break;
                        case 'int':
                            newItem[csvProperty] = parseInt(item[that.csv.mapping[csvProperty]]);
                            break;
                        case 'float':
                            newItem[csvProperty] = parseFloat(item[that.csv.mapping[csvProperty]]);
                            break;
                        case 'uuid':
                            if (item[that.csv.mapping[csvProperty]].length == 32) {
                                newItem[csvProperty] = item[that.csv.mapping[csvProperty]];
                            }
                            break;
                        case 'date':
                            // Do nothing
                            break;
                        default:
                            newItem[csvProperty] = item[that.csv.mapping[csvProperty]];
                    }
                }
            }

            return newItem;
        },

        async getMediaIdByFileName(filename) {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('fileName', filename));
            let media = null;
            await this.mediaRepository.search(criteria, Shopware.Context.api).then((result) => {
                media = result.first();
            });
            return media ? media.id : null;
        }
    }
});

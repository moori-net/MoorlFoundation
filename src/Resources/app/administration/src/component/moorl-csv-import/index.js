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
            defaultValues: this.defaultValues,
            data: null,
            properties: {},
            mapping: {},
            columns: null,
            showImportModal: true
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
        async getEntityById(entity, id) {
            // TODO: Check ID is valid
            return true;
        },

        async getEntityByUniqueProperties() {
            // TODO: Check entity has one match
            return true;
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
            for (let item of this.columns) {
                if (item.flags.primary_key || item.flags.moorl_unique) {
                    elements.push(item.label);
                }
            }
            return elements.join(', ');
        },

        validateCsv() {
            const that = this;

            this.properties = Object.keys(this.data[0]);
            this.matches = 0;

            for (let property in this.columns) {
                let indexOf = (arr, q) => arr.findIndex(item => q.toLowerCase() === item.toLowerCase());
                let result = indexOf(that.properties, property);

                if (result != -1) {
                    that.mapping[property] = that.properties[result];
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
                    this.importCsvRow();
                }).catch((exception) => {
                    this.pause = true;
                    console.log(exception);
                });
        },

        async importCsvRow() {
            if (this.pause) {
                return;
            }
            this.rowsDone++;
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

            for (const [property, newProperty] of Object.entries(this.mapping)) {
                if (typeof property == 'string') {
                    let item = this.columns.find(column => { return column.property === newProperty });

                    switch (item.type) {
                        case 'association':
                            if (item.relation === 'many_to_one') {
                                if (item.entity === 'media' && item[property].length > 0) {
                                    if (!that.mapping[item.localField] || that.mapping[item.localField].length !== 32) {
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
                                            newItem[item.localField] = mediaId;
                                        } else {
                                            newItem[item.localField] = newMediaItem.id;
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
                            } else if (item.relation === 'many_to_many') {
                                let parts = item[property].split("|");
                                if (parts[0].length === 32) {
                                    newItem[newProperty] = parts.map(function (id) {
                                        if (that.getEntityById(item.entity, id)) {
                                            return {id: id};
                                        } else {
                                            console.log(newProperty + " - import validation error: unknown ID (" + id + ")");
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

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
            pause: false,
            step: 1,
            options: {
                overwrite: true,
                pause: true,
                validateIds: true,
                mediaFolderId: null
            },
            requiredColumns: [],
            selectedItem: null,
            tags: null,
            rowCount: 0,
            rowsDone: 0,
            rowsLeft: 0,
            rowsSkipped: 0,
            rowsNew: 0,
            errorCount: 0,
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
        async getItemById(entity, id) {


            if (typeof id == 'undefined') {
                return null;
            }

            if (!id) {
                return null;
            }

            let item = null;

            if (typeof entity != 'undefined' && entity !== this.entity) {
                await this.repositoryFactory.create(entity).get(id.toLowerCase(), Shopware.Context.api, new Criteria()).then(async (entity) => {
                    item = entity;
                })
            } else {
                await this.repository.get(id.toLowerCase(), Shopware.Context.api, new Criteria()).then(async (entity) => {
                    item = entity;
                })
            }


            return item ? item : null;
        },

        async getItemByUniqueProperties(item) {


            const isUuid = /^[a-f0-9]{32}$/i; // uuid check
            const criteria = new Criteria(1, 1);

            if (item.id && isUuid.test(item.id)) {
                // highest priority
                criteria.addFilter(Criteria.equals('id', item.id));
            } else {
                let multiFromImport = [];
                let multiFromDefaultValues = [];

                for (let column of this.columns) {
                    if (column.flags.moorl_unique || column.flags.primary_key) {
                        if (item[column.property]) {
                            multiFromImport.push(Criteria.equals(column.property, item[column.property]));
                        }
                    }
                }

                if (multiFromImport.length === 0) {
                    return null; // no unique field set
                }

                for (const [field, value] of Object.entries(this.defaultItem)) {
                    multiFromDefaultValues.push(Criteria.equals(field, value));
                }

                criteria.addFilter(Criteria.multi('AND', [
                    Criteria.multi('OR', multiFromImport),
                    Criteria.multi('AND', multiFromDefaultValues)
                ]));
            }

            let entity = null;

            await this.repository.search(criteria, Shopware.Context.api).then(async (result) => {
                entity = result.first();

            });



            return entity;
        },

        async getMediaIdByFileName(filename, fileExtension) {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('fileName', filename));
            criteria.addFilter(Criteria.equals('fileExtension', fileExtension));
            let media = null;
            await this.mediaRepository.search(criteria, Shopware.Context.api).then((result) => {
                media = result.first();
            });
            return media ? media.id : null;
        },

        createdComponent() {
            this.initEditColumns();

            this.tagRepository.search(new Criteria(), Shopware.Context.api).then((result) => {
                this.tags = result;
            });
        },

        onCancel() {
            this.pause = true;
            this.onCloseModal();
        },

        initEditColumns() {
            let columns = [];
            let properties = Shopware.EntityDefinition.get(this.entity).properties



            for (const [property, column] of Object.entries(properties)) {
                // Since 6.3.5 there are new fields here
                if (column.relation === 'many_to_many' && column.localField !== null) {
                    delete column.flags.required;
                    delete column.localField;
                    delete column.referenceField;
                }

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

        getUniquePropertyLabels() {
            let elements = [];
            for (let column of this.columns) {
                if (column.flags.primary_key || column.flags.moorl_unique) {
                    elements.push(column.property);
                }
            }
            return elements.join(', ');
        },

        validateItem() {
            const that = this;

            this.properties = Object.keys(this.data[0]);
            this.matches = 0;

            const indexOf = (arr, q) => arr.findIndex(item => q.toLowerCase().replace(/[\W_]+/g,"") === item.toLowerCase().replace(/[\W_]+/g,""));

            for (let column of this.columns) {
                if (column.localField) {
                    let result = indexOf(that.properties, column.localField);

                    if (result != -1) {
                        that.mapping[column.localField] = that.properties[result];
                        that.matches++;
                    }
                } else {
                    let result = indexOf(that.properties, column.property);

                    if (result != -1) {
                        that.mapping[column.property] = that.properties[result];
                        that.matches++;
                    }
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


                    if (results.errors && results.errors.length > 0) {
                        that.createSystemNotificationError({
                            title: that.$t('moorl-foundation.notification.errorTitle'),
                            message: that.$t('moorl-foundation.notification.errorFileText'),
                        });

                        that.onCloseModal();

                        return;
                    }

                    that.data = results.data;
                    that.validateItem();
                    that.$refs.fileForm.reset();

                    that.rowCount = that.data.length;
                    that.rowsDone = 0;
                    that.step = 2;

                    that.initSelectedItem();
                }
            });
        },

        initSelectedItem() {
            this.selectedItem = {};

            for (let column of this.columns) {
                if (column.relation === 'many_to_many' || column.relation === 'one_to_many') {
                    let repository = this.repositoryFactory.create(column.entity);

                    this.selectedItem[column.property] = new Shopware.Data.EntityCollection(
                        repository.route,
                        repository.entityName,
                        Shopware.Context.api
                    );
                } else if (column.relation === 'many_to_one' && column.localField) {
                    this.selectedItem[column.localField] = null;
                }
            }

            Object.assign(this.selectedItem, this.defaultItem);
        },

        onClickBack() {
            this.step--;
        },

        onClickPause() {
            this.pause = !this.pause;

            // resuming import
            if (!this.pause) {
                this.importItem();
            }
        },

        mountedComponent() {},

        onClickImport() {
            this.createSystemNotificationSuccess({
                title: this.$tc('moorl-foundation.notification.finishImportTitle'),
                message: this.$tc('moorl-foundation.notification.finishImportText'),
            });

            this.step = 3;

            this.importItem();
        },

        async prepareSaveItem(srcItem) {
            const item = Object.assign({}, this.selectedItem, srcItem)


            let entity = await this.getItemByUniqueProperties(item);

            if (!entity) {
                entity = this.repository.create(Shopware.Context.api);
                this.rowsNew++;
            } else {
                this.rowsSkipped++;

                if (!this.options.overwrite) {
                    return this.onError('Error: (' + this.getUniquePropertyLabels() + ') is already in Database. Please chose overwrite and try again');
                }
            }

            //item.id = entity.id;

            Object.assign(entity, item);

            this.saveItem(entity);
        },

        saveItem(item) {


            this.repository
                .save(item, Shopware.Context.api)
                .then(() => {
                    this.statusMessage = this.rowsDone + ' of ' + this.rowCount + ' done';
                    this.importItem()
                }).catch((exception) => {
                    this.onError(exception);
                });
        },

        async importItem() {
            this.rowsLeft = this.data.length;

            if (this.rowsLeft < 1) {
                this.step = 4;
                this.onFinishImport();
                return;
            }

            this.rowsDone++;

            let item = this.data.shift();
            item = await this.sanitizeItem(item);

            if (this.pause && this.options.pause) {
                return;
            }

            this.pause = false;

            await this.prepareSaveItem(item);
        },

        onError(message) {
            this.errorCount++;

            this.statusMessage = message;
            this.pause = true;
        },

        isValidHttpUrl(string) {
            let url;
            try {
                url = new URL(string);
            } catch (_) {
                return false;
            }
            return url.protocol === "http:" || url.protocol === "https:";
        },

        async sanitizeItem(item) {


            const that = this;

            const newItem = {};
            const isBool = /^\s*(true|1|on|yes|y|j|ja|an|si|x|check)\s*$/i; // boolean check
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
                                if (column.entity === 'media' && !currentUuid) {
                                    const newMediaItem = this.mediaRepository.create(Shopware.Context.api);
                                    let file, mediaUrl;

                                    if (that.isValidHttpUrl(currentValue)) {
                                        mediaUrl = new URL(currentValue);
                                        file = mediaUrl.pathname.split('/').pop().split('.');
                                    } else {
                                        file = currentValue.split('.');
                                    }

                                    if (file.length === 1) {
                                        newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                    } else {
                                        newMediaItem.fileName = file[0].replace(/[^a-zA-Z0-9_\- ]/g, "");
                                        newMediaItem.fileExtension = file.pop();
                                    }

                                    newMediaItem.mediaFolderId = this.options.mediaFolderId;

                                    let mediaId = await this.getMediaIdByFileName(newMediaItem.fileName, newMediaItem.fileExtension);

                                    if (mediaId) {
                                        newItem[column.localField] = mediaId;
                                    } else if (that.isValidHttpUrl(currentValue)) {
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
                                //let parts = currentValue.toLowerCase().split("|");
                                let parts = currentValue.split("|");

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
                            newItem[newProperty] = parseInt(currentValue, 10);
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
                        case 'json_object':
                            try {
                                newItem[newProperty] = JSON.parse(currentValue);
                            } catch (e) {
                                newItem[newProperty] = null;
                            }
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

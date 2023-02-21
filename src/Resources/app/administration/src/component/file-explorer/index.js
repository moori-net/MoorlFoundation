const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-file-explorer', {
    template,

    inject: [
        'repositoryFactory',
        'context',
        'foundationApiService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: [
        'value',
        'clientId',
        'showActions'
    ],

    data() {
        return {
            isLoading: true,
            items: null,
            showCreateDirModal: false,
            dirname: null,
            filename: null,
        };
    },

    computed: {},

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
    },

    created() {
        this.listContents();
    },

    methods: {
        changeDirectory(directory) {
            if (directory === '..') {
                directory = this.value.split("/").slice(0, -1).join('/');
            }
            this.value = directory;
            this.listContents();
        },

        write(path, content) {
            this.isLoading = true;

            this.foundationApiService.post(`/moorl-foundation/file-explorer/write`, {
                clientId: this.clientId,
                path: path,
                content: content,
            }).then((response) => {
                this.isLoading = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });
                this.isLoading = false;
            });
        },

        read(path) {
            this.foundationApiService.download(`/moorl-foundation/file-explorer/read-stream`, {
                clientId: this.clientId,
                path: path
            });
        },

        delete(path) {
            this.isLoading = true;

            this.foundationApiService.post(`/moorl-foundation/file-explorer/delete`, {
                clientId: this.clientId,
                path: path
            }).then((response) => {
                this.listContents();
                this.isLoading = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });
                this.isLoading = false;
            });
        },

        createDir() {
            this.isLoading = true;

            let dirname = `${this.value ? this.value + '/' : ''}${this.dirname}`;

            this.foundationApiService.post(`/moorl-foundation/file-explorer/create-dir`, {
                clientId: this.clientId,
                dirname: dirname
            }).then((response) => {
                this.value = dirname;
                this.dirname = null;

                this.listContents();
                this.isLoading = false;
                this.showCreateDirModal = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });
                this.isLoading = false;
            });
        },

        deleteDir(directory) {
            this.isLoading = true;

            this.foundationApiService.post(`/moorl-foundation/file-explorer/delete-dir`, {
                clientId: this.clientId,
                directory: directory
            }).then((response) => {
                this.listContents();
                this.isLoading = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });
                this.isLoading = false;
            });
        },

        listContents() {
            this.isLoading = true;

            this.foundationApiService.post(`/moorl-foundation/file-explorer/list-contents`, {
                clientId: this.clientId,
                directory: this.value
            }).then((response) => {
                this.items = response;
                this.isLoading = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });
                this.isLoading = false;
            });
        },
    }
});

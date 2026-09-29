import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-ai-chat', {
    template,

    inject: ['foundationApiService'],

    mixins: [Shopware.Mixin.getByName('notification')],

    emits: ['apply-changes'],

    props: {
        entity: {
            type: String,
            required: true,
        },
        item: {
            type: Object,
            required: true,
        },
        itemHelper: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            isTesting: false,
            isSending: false,
            sendOnEnter: true,
            messageInput: '',
            messages: [],
        };
    },

    computed: {
        visibleMessages() {
            return this.messages.filter(message => message.role !== 'context');
        },

        aiPromptSuggestions() {
            if (typeof this.itemHelper.getAiPromptSuggestions !== 'function') {
                return [];
            }

            return this.itemHelper.getAiPromptSuggestions()
                .map((suggestion) => {
                    if (typeof suggestion?.field !== 'string' || typeof suggestion?.snippetKey !== 'string') {
                        return null;
                    }

                    const prompt = this.$tc(suggestion.snippetKey, {
                        field: suggestion.field,
                    }, 0);

                    if (!prompt || prompt === suggestion.snippetKey) {
                        return null;
                    }

                    const label = suggestion.buttonSnippetKey
                        ? this.$tc(suggestion.buttonSnippetKey, {
                            field: this.getAiFieldLabel(suggestion.field),
                        }, 0)
                        : prompt;

                    return {
                        field: suggestion.field,
                        prompt,
                        label: !label || label === suggestion.buttonSnippetKey ? prompt : label,
                    };
                })
                .filter(Boolean);
        },
    },

    methods: {
        getAiFieldLabel(field) {
            const snippetKey = `moorl-foundation.field.${field}`;
            const label = this.$tc(snippetKey, {}, 0);

            return !label || label === snippetKey ? field : label;
        },

        onSelectPromptSuggestion(suggestion) {
            if (typeof suggestion?.prompt !== 'string' || suggestion.prompt === '') {
                return;
            }

            this.messageInput = suggestion.prompt;
            this.$refs.messageInput?.$el?.querySelector('textarea')?.focus();
        },

        onEnterKeydown(event) {
            if (!this.sendOnEnter) {
                return;
            }

            event.preventDefault();
            return this.onSendMessage();
        },

        async scrollMessagesToBottom() {
            await this.$nextTick();

            const messages = this.$refs.messages;

            if (messages) {
                messages.scrollTop = messages.scrollHeight;
            }
        },

        createContextMessage() {
            return {
                role: 'context',
                content: [{
                    type: 'entity_context',
                    context: this.itemHelper.getAiContext(),
                }],
            };
        },

        createItemSnapshot() {
            try {
                return JSON.parse(JSON.stringify(this.item));
            } catch (exception) {
                return null;
            }
        },

        createImageInputs() {
            return this.itemHelper.getAiImageUrls(this.item)
                .slice(0, 1)
                .flatMap((url) => {
                    try {
                        const imageUrl = new URL(url, window.location.origin);

                        return imageUrl.protocol === 'https:' ? [{url: imageUrl.href}] : [];
                    } catch (exception) {
                        return [];
                    }
                });
        },

        async onSendMessage() {
            const text = this.messageInput.trim();

            if (!text || this.isSending || this.isTesting) {
                return;
            }

            if (text.length > 10000) {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('moorl-foundation.ai.messageTooLong'),
                });

                return;
            }

            const item = this.createItemSnapshot();

            if (!item) {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('moorl-foundation.ai.chatFailed'),
                });

                return;
            }

            const isFirstRequest = !this.messages.length;

            if (isFirstRequest) {
                this.messages.push(this.createContextMessage());
            }

            if (this.messages.length >= 29) {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('moorl-foundation.ai.chatLimitReached'),
                });

                return;
            }

            this.messages.push({
                role: 'user',
                content: [{ type: 'text', text }],
            });
            this.messageInput = '';
            this.isSending = true;

            try {
                const response = await this.foundationApiService.post('/moorl-foundation/ai/chat', {
                    messages: this.messages,
                    item,
                    images: isFirstRequest ? this.createImageInputs() : [],
                });

                if (response?.errors || !response?.message) {
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: response?.errors?.[0]?.message || this.$tc('moorl-foundation.ai.chatFailed'),
                    });

                    return;
                }

                this.messages.push({
                    role: 'assistant',
                    content: [{ type: 'text', text: response.message }],
                });
                await this.scrollMessagesToBottom();

                if (response.changes && Object.keys(response.changes).length) {
                    this.$emit('apply-changes', response.changes);
                }
            } catch (exception) {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');

                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail || exception.message || this.$tc('moorl-foundation.ai.chatFailed'),
                });
            } finally {
                this.isSending = false;
            }
        },

        async onTestConnection() {
            this.isTesting = true;

            try {
                const response = await this.foundationApiService.post('/moorl-foundation/ai/test');

                if (response?.errors) {
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: response.errors[0].message,
                    });

                    return;
                }

                this.createNotificationSuccess({
                    title: this.$tc('global.default.success'),
                    message: this.$tc('moorl-foundation.ai.connectionSuccess'),
                });
            } catch (exception) {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');

                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail || exception.message,
                });
            } finally {
                this.isTesting = false;
            }
        },
    },
});

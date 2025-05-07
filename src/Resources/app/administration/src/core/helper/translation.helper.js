export default class TranslationHelper {
    constructor({$tc, componentName = undefined}) {
        this.componentName = componentName;
        this.$tc = $tc;

        this.snippetSets = [];
        this.snippetStruct = {};

        this._init();
    }

    getNotification(property) {
        return this.getLabel('notification', property, false);
    }

    get(group, property) {
        return this.getLabel(group, property, false);
    }

    getLabel(group, property, showConsoleError = true) {
        const parts = property.split(".");
        const translatedParts = [];

        for (const part of parts) {
            for (const set of this.snippetSets) {
                if (this.snippetStruct[set] === undefined) {
                    this.snippetStruct[set] = {};
                }
                if (this.snippetStruct[set][group] === undefined) {
                    this.snippetStruct[set][group] = {};
                }

                const snippet = `${set}.${group}.${part}`;
                const translated = this.$tc(snippet);

                if (translated !== snippet) {
                    translatedParts.push(translated);
                    break;
                } else {
                    this.snippetStruct[set][group][part] = part;
                }
            }
        }

        if (translatedParts.length > 0) {
            return translatedParts.join("-");
        }

        if (showConsoleError) {
            const snippet = `${group}.${property}`;

            console.error(this.componentName, `No translation found for ${snippet}`, this.snippetStruct);

            return snippet;
        }

        return undefined;
    }

    _init() {
        if (this.componentName !== undefined) {
            this._addSnippetSourceByComponentName(
                this.componentName.split("-")
            );
        }

        this.snippetSets.push("moorl-foundation"); // Fallback
    }

    _addSnippetSourceByComponentName(parts) {
        this.snippetSets.push(parts.join("-"));
        parts.pop();
        // ignore moorl prefix
        if (parts.length > 1) {
            this._addSnippetSourceByComponentName(parts);
        }
    }
}

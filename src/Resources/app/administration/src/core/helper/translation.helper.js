class TranslationHelper {
    constructor({componentName, snippetSrc, tc}) {
        this._componentName = componentName;
        this._snippetSrc = snippetSrc;
        this._tc = tc;
        this._snippetSets = [];
        this._snippetStruct = {};

        this._init();
    }

    getSnippetSets() {
        return this._snippetSets;
    }

    getNotification(property) {
        return this.getLabel('notification', property, false);
    }

    getLabel(group, property, showConsoleError = true) {
        const parts = property.split(".");
        const translatedParts = [];

        for (const part of parts) {
            for (const set of this._snippetSets) {
                if (this._snippetStruct[set] === undefined) {
                    this._snippetStruct[set] = {};
                }
                if (this._snippetStruct[set][group] === undefined) {
                    this._snippetStruct[set][group] = {};
                }

                const snippet = `${set}.${group}.${part}`;
                const translated = this._tc(snippet);

                if (translated !== snippet) {
                    translatedParts.push(translated);
                    break;
                } else {
                    this._snippetStruct[set][group][part] = part;
                }
            }
        }

        if (translatedParts.length > 0) {
            return translatedParts.join("-");
        }

        if (showConsoleError) {
            const snippet = `${group}.${property}`;

            console.error(`${this._componentName}: No translation found for ${snippet}`);
            console.error(this._snippetSets);
            console.error(this._snippetStruct);

            return snippet;
        }

        return undefined;
    }

    _init() {
        if (this._snippetSrc !== undefined) {
            this._snippetSets.push(this._snippetSrc);
        }
        let parts = this._componentName.split("-");

        this._addSnippetSourceByComponentName(parts);

        this._snippetSets.push("moorl-foundation"); // Fallback
    }

    _addSnippetSourceByComponentName(parts) {
        this._snippetSets.push(parts.join("-"));
        parts.pop();
        // ignore moorl prefix
        if (parts.length > 1) {
            this._addSnippetSourceByComponentName(parts);
        }
    }
}

export default TranslationHelper;

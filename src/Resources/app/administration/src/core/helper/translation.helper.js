class TranslationHelper {
    constructor({componentName, snippetSrc, tc}) {
        this._componentName = componentName;
        this._snippetSrc = snippetSrc;
        this._snippetSets = [];
        this._snippetStruct = {};
        this._tc = tc;

        this._init();
    }

    getSnippetSets() {
        return this._snippetSets;
    }

    getNotification(property) {
        return this.getLabel('notification', property, false);
    }

    getLabel(group, property, showConsoleError = true) {
        for (const set of this._snippetSets) {
            if (this._snippetStruct[set] === undefined) {
                this._snippetStruct[set] = {};
            }
            if (this._snippetStruct[set][group] === undefined) {
                this._snippetStruct[set][group] = {};
            }
            if (this._snippetStruct[set][group][property] === undefined) {
                this._snippetStruct[set][group][property] = property;
            }

            const snippet = `${set}.${group}.${property}`;
            const translated = this._tc(snippet);

            if (translated !== snippet) {
                return translated;
            }
        }

        if (showConsoleError) {
            console.error(`${this._componentName}: No translation found for ${group}.${property}`);
            console.error(this._snippetSets);
            console.error(this._snippetStruct);
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

export function applyAutoConfiguration({ configList, context, debug = true }) {
    const aliasMap = {};
    const appliedAliases = [];

    for (const config of configList) {
        if (config.alias) {
            aliasMap[config.alias] = config;
        }
    }

    const resolveCondition = (condition) => {
        if (typeof condition === 'function') return condition(context);

        if (typeof condition === 'string') {
            const isNegated = condition.startsWith('!');
            const alias = isNegated ? condition.slice(1) : condition;

            const subConfig = aliasMap[alias];
            if (!subConfig) {
                console.warn(`[autoConfiguration] Unknown alias: ${alias}`);
                return false;
            }

            const result = subConfig.conditions.every(resolveCondition);
            return isNegated ? !result : result;
        }

        return false;
    };

    for (const config of configList) {
        const match = config.conditions.every(resolveCondition);

        if (match && typeof config.apply === 'function') {
            if (debug) {
                const alias = config.alias ?? '(final)';
                const description = typeof config.description === 'function'
                    ? config.description(context)
                    : config.description ?? '';

                console.debug(`[autoConfiguration] Applying rule [${alias}]${description ? `: ${description}` : ''}`);
            }

            config.apply(context);

            if (config.alias) {
                appliedAliases.push(config.alias);
            } else {
                break; // abbrechen bei finaler Regel ohne alias
            }
        }
    }

    return appliedAliases;
}

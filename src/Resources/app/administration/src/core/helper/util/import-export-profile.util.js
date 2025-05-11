export function buildImportExportProfile(entity, depth = 0, path = '') {
    const fields = Shopware.EntityDefinition.get(entity).properties;

    const typeOrder = ['uuid', 'boolean', 'int', 'float', 'string', 'text', 'date', 'association'];
    const blacklist = ['createdAt', 'updatedAt', 'translations', 'salesChannel', 'versionId'];
    const whitelist = ['id', 'name', 'url', 'title', 'alt', 'taxRate'];

    const toSnakeCase = (str) =>
        str
            .replace(/([a-z0-9])([A-Z])/g, '$1_$2')
            .replace(/([a-zA-Z])([0-9]+)/g, '$1_$2')
            .replace(/\./g, '_')
            .toLowerCase();

    const entries = Object.entries(fields)
        .map(([key, def]) => ({ key, ...def }))
        .filter(entry => typeOrder.includes(entry.type))
        .filter(entry => !blacklist.includes(entry.key))
        .filter(entry => depth === 0 || whitelist.includes(entry.key))
        .sort((a, b) => typeOrder.indexOf(a.type) - typeOrder.indexOf(b.type));

    const mapping = [];

    for (const entry of entries) {
        const fullPath = `${path}${entry.key}`;

        if (entry.type === 'uuid' && !entry.flags?.primary_key) {
            continue;
        }

        if (entry.type === 'association') {
            if (depth === 0 && entry.relation === 'many_to_one') {
                const nested = buildImportExportProfile(entry.entity, depth + 1, fullPath + '.');
                mapping.push(...nested);
            }
            continue;
        }

        const mappedKey = toSnakeCase(fullPath);
        if (entry.flags?.translatable) {
            mapping.push({
                key: `translations.DEFAULT.${fullPath}`,
                mappedKey
            });
        } else {
            mapping.push({
                key: fullPath,
                mappedKey
            });
        }
    }

    // Add position index
    mapping.forEach((entry, index) => {
        entry.position = index;
    });

    // Optional debug output
    if (depth === 0) {
        //console.log('buildImportExportProfile', entity, mapping);
    }

    return mapping;
}

export default class ConditionHelper {
    static isVisible(field, item) {
        if (!field.conditions || field.conditions.length === 0) {
            return true;
        }

        return ConditionHelper.evaluate(field.conditions, item);
    }

    static evaluate(conditions = [], item = {}) {
        let fulfilled = 0;

        for (const condition of conditions) {
            const { property, value, operator = '==', callback } = condition;
            let result = false;

            if (typeof value === 'function') {
                result = value(item);
            } else {
                let a, b;

                if (!Array.isArray(property)) {
                    a = item[property];
                    b = value;
                } else if (property.length === 2) {
                    a = item[property[0]];
                    b = item[property[1]];
                }

                if (a === undefined) {
                    result = true;
                } else {
                    result = ConditionHelper._compare(a, b, operator);
                }
            }

            if (typeof callback === 'function') {
                callback({ result, item });
            }

            if (result) fulfilled++;
        }

        return fulfilled === conditions.length;
    }

    static _compare(a, b, operator) {
        if (typeof a == 'object' && a !== null) {
            a = a.value; // Handle CMS element config
        }

        switch (operator) {
            case 'eq':
            case '==': return a == b;
            case '===': return a === b;
            case '!=': return a != b;
            case '!==': return a !== b;
            case 'gt':
            case '>': return a > b;
            case 'lt':
            case '<': return a < b;
            case 'gte':
            case '>=': return a >= b;
            case 'lte':
            case '<=': return a <= b;
            case 'in': return Array.isArray(b) && b.includes(a);
            case 'nin': return Array.isArray(b) && !b.includes(a);
            case 'includes': return typeof a === 'string' && a.includes(b);
            case 'notIncludes': return typeof a === 'string' && !a.includes(b);
            default: return false;
        }
    }
}

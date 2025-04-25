class Logger {
    static logLevel = 'log'; // 'log', 'warn', 'error', 'none'

    static log(prefix, message, context = null) {
        if (!Logger._shouldLog('log')) return;
        console.log(`[${prefix}] ${message}`, context);
    }

    static warn(prefix, message, context = null) {
        if (!Logger._shouldLog('warn')) return;
        console.warn(`[${prefix}] ${message}`, context);
    }

    static error(prefix, message, context = null) {
        if (!Logger._shouldLog('error')) return;
        console.error(`[${prefix}] ${message}`, context);
    }

    static _shouldLog(level) {
        const levels = ['log', 'warn', 'error'];
        const current = levels.indexOf(Logger.logLevel);
        const requested = levels.indexOf(level);

        return requested >= current && Logger.logLevel !== 'none';
    }
}

export default Logger;

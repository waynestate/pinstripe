// Shared helper for the Pinstripe ESLint presets.
//
// raiseWarnToError returns a copy of a flat config array with every rule
// severity of "warn" (or 1) raised to "error" (2). Rules already at "error"
// (or 2), and every other setting, are left untouched.
//
// The input is never mutated: each config object and its rules table are
// copied, so the plugins' own config objects stay usable by the -warn
// presets and by anyone else who spreads them.

/**
 * Is this a warn severity, spelled out or numeric?
 *
 * @param {unknown} severity
 * @return {boolean}
 */
function isWarn(severity) {
    return severity === 'warn' || severity === 1
}

/**
 * Raise every warn severity in a flat config array to error.
 *
 * @param {Record<string, unknown>[]} configs a flat config array, as exported by an ESLint plugin
 * @return {Record<string, unknown>[]} a new array with warn raised to error
 */
export function raiseWarnToError(configs) {
    return configs.map((config) => {
        if (!config.rules) {
            return { ...config }
        }
        const rules = {}
        for (const [ruleId, setting] of Object.entries(config.rules)) {
            const severity = Array.isArray(setting) ? setting[0] : setting
            if (isWarn(severity)) {
                rules[ruleId] = Array.isArray(setting) ? ['error', ...setting.slice(1)] : 'error'
            } else {
                rules[ruleId] = setting
            }
        }
        return { ...config, rules }
    })
}

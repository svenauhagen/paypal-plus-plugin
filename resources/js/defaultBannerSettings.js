export const DEFAULT_SETTINGS = {
  amount: 0,
  style: {
    layout: 'text',
    color: 'blue',
    ratio: '8x1',
    logo: {
      type: 'primary',
      color: 'black'
    }
  }
}
/**
 * @params {Object} settings The object with the selected settings values
 * @params {Object} defaults The default values to assign to the given target
 * @return {Object} The target object with assigned default values if needed
 */
export function assignDefaults (settings, defaults) {
  const target = defaults
  for (let key in settings) {
    if (key in target) {
      target[key] = (typeof settings[key] === 'object') ? assignDefaults(settings[key], target[key]) : settings[key]
    }
  }
  return target
}

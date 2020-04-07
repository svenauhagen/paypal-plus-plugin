const DEFAULT_SETTINGS = {
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

export function merger (settings) {
  let merged = DEFAULT_SETTINGS
  for (let key in settings) {
    if (typeof settings[key] === 'object') {
      for (let innerKey in settings[key]) {
        if (typeof settings[key][innerKey] === 'object') {
          for (let logoKey in settings[key][innerKey]) {
            merged[key][innerKey][logoKey] = settings[key][innerKey][logoKey]
          }
        } else {
          if (merged[key][innerKey]) {
            merged[key][innerKey] = settings[key][innerKey]
          }
        }
      }
    } else {
      merged[key] = settings[key]
    }
  }
  return merged
}

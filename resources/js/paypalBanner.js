import { assignDefaults, DEFAULT_SETTINGS } from './defaultBannerSettings'

(function (jQuery, paypalBannerFrontData) {
  if (typeof paypalBannerFrontData === 'undefined') {
    return
  }
  if (!paypalBannerFrontData.settings) {
    console.warn('paypalBannerFrontData.settings not defined')
    return
  }

  window.addEventListener('load', () => {
    let settings = paypalBannerFrontData.settings || {}
    settings = assignDefaults(settings, DEFAULT_SETTINGS)
    const { color, ratio, layout, logo } = settings.style
    let options = {
      amount: settings.amount,
      style: {
        layout: layout,
        logo: {
          type: logo.type
        },
        text: {
          color: logo.color
        }
      }
    }
    if (settings && layout !== 'text') {
      options = {
        amount: settings.amount,
        style: {
          layout: layout,
          color: color,
          ratio: ratio
        }
      }
    }
    paypalBannerSdk.Messages(options).render('#paypal-credit-banner')
  })
})(jQuery, window.paypalBannerFrontData)

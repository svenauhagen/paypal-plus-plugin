import { merger } from './defaultBannerSettings'

(function (jQuery, paypalBannerFrontData) {
  if (typeof paypalBannerFrontData === 'undefined') {
    return
  }
  if (!paypalBannerFrontData.settings) {
    console.warn('paypalBannerFrontData.settings not defined')
    return
  }

  window.addEventListener('load', () => {
    let settings = paypalBannerFrontData.settings
    settings = merger(settings)
    let options = {
      amount: settings.amount,
      style: {
        layout: settings.style.layout,
        logo: {
          type: settings.style.logo.type
        },
        text: {
          color: settings.style.logo.color
        }
      }
    }
    if (settings && settings.style.layout !== 'text') {
      options = {
        amount: settings.amount,
        style: {
          layout: settings.style.layout,
          color: settings.style.color,
          ratio: settings.style.ratio
        }
      }
    }
    paypalBannerSdk.Messages(options).render('#paypal-credit-banner')
  })
})(jQuery, window.paypalBannerFrontData)

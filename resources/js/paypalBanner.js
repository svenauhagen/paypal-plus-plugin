(function (jQuery, paypalBannerFrontData) {
  if (typeof paypalBannerFrontData === 'undefined') {
    return
  }
  if (!paypalBannerFrontData.settings) {
    console.warn('paypalBannerFrontData.settings not defined')
    return
  }

  window.addEventListener('load', () => {
    const settings = paypalBannerFrontData.settings
    if (!settings.style) {
      settings['style'] = {
        layout: 'text',
        color: 'blue',
        ratio: '8x1',
        logo: { type: 'primary', color: 'black' }
      }
    }
    if (!settings.style.logo) {
      settings.style['logo'] = {
        type: 'primary',
        color: 'black'
      }
    }

    let options = {
      amount: settings.amount || 0,
      style: {
        layout: settings.style.layout || 'text',
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
        amount: settings.amount || 0,
        style: {
          layout: settings.style.layout || 'flex',
          color: settings.style.color || 'blue',
          ratio: settings.style.ratio || '8x1'
        }
      }
    }
    paypalBannerSdk.Messages(options).render('#paypal-credit-banner')
  })
})(jQuery, window.paypalBannerFrontData)

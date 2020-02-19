(function (jQuery, paypalBannerFrontData) {
  if (typeof paypalBannerFrontData === 'undefined') {
    return
  }
  if (!paypalBannerFrontData.settings) {
    console.warn('paypalBannerFrontData.settings not defined')
    return
  }
  if (!paypalBannerFrontData.settings.script_url) {
    console.warn('paypalBannerFrontData.settings.script_url not defined')
    return
  }
  window.addEventListener('load', () => {
    jQuery.ajax({
      url: paypalBannerFrontData.settings.script_url,
      dataType: 'script',
      cache: true,
      success: () => {
        const settings = paypalBannerFrontData.settings
        let options = {
          amount: settings.amount,
          currency: 'EUR',
          style: {
            layout: settings.style.layout,
            color: settings.style.color,
            ratio: settings.style.ratio
          }
        }
        if (settings && settings.style.layout !== 'flex') {
          options = {
            amount: settings.amount,
            currency: 'EUR',
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
        }
        paypal.Messages(options).render('#paypal-credit-banner')
      }
    })
  })
})(jQuery, window.paypalBannerFrontData)

import { bootstrapPayPalApp } from './plusFrameView'

(function (jQuery, _, pageinfo) {
  if (typeof pageinfo === 'undefined') {
    return
  }

  function isCheckoutPayPage () {
    // eslint-disable-next-line no-undef
    return frontpagedata.pageinfo.isCheckoutPayPage
  }

  function isCheckoutPage () {
    // eslint-disable-next-line no-undef
    return frontpagedata.pageinfo.isCheckout
  }

  function isConflictiveWC () {
    // eslint-disable-next-line no-undef
    return frontpagedata.isConflictVersion
  }

  window.addEventListener('load', () => {
    bootstrapPayPalApp()
    // Isn't possible to listen on a jQuery event by using `addEventListener`
    if (isCheckoutPayPage() || isConflictiveWC()) {
      bootstrapPayPalApp()
      return
    }

    if (isCheckoutPage()) {
      jQuery(document.body).on('updated_checkout', () => {
        bootstrapPayPalApp()
      })
    }
  })
})(jQuery, _, window.frontpagedata)

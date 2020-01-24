import { bootstrapPayPalApp } from './plusFrameView'

(function (jQuery, _, frontpagedata) {
  if (!frontpagedata) {
    return
  }

  function isCheckoutPayPage () {
    return Boolean(frontpagedata.pageinfo) && Boolean(frontpagedata.pageinfo.isCheckoutPayPage)
  }

  function isCheckoutPage () {
    return Boolean(frontpagedata.pageinfo) && Boolean(frontpagedata.pageinfo.isCheckout)
  }

  function isConflictiveWC () {
    return Boolean(frontpagedata) && Boolean(frontpagedata.isConflictVersion)
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

import { bootstrapPayPalApp } from './plusFrameView'

(function (jQuery, _, pluginpagedata) {
  if (!pluginpagedata) {
    return
  }

  function isCheckoutPayPage () {
    return (Boolean)((pluginpagedata.pageinfo) && (pluginpagedata.pageinfo.isCheckoutPayPage))
  }

  function isCheckoutPage () {
    return (Boolean)((pluginpagedata.pageinfo) && (pluginpagedata.pageinfo.isCheckout))
  }

  function isConflictiveWC () {
    return (Boolean)((pluginpagedata) && (pluginpagedata.isConflictVersion))
  }

  // isConfliveWC checks for WC versions that do not call updated_checkout on load
  window.addEventListener('load', () => {
    // Isn't possible to listen on a jQuery event by using `addEventListener`
    if (isCheckoutPayPage() || (isCheckoutPage() && isConflictiveWC())) {
      bootstrapPayPalApp()
      return
    }

    if (isCheckoutPage()) {
      jQuery(document.body).on('updated_checkout', () => {
        bootstrapPayPalApp()
      })
    }
  })
})(jQuery, _, window.pluginpagedata)

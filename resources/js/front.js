import { bootstrapPayPalApp } from './plusFrameView'

(function (jQuery, _, pppFrontDataCollection) {
  if (!pppFrontDataCollection) {
    return
  }

  function isCheckoutPayPage () {
    return (Boolean)(pppFrontDataCollection.pageinfo && pppFrontDataCollection.pageinfo.isCheckoutPayPage)
  }

  function isCheckoutPage () {
    return (Boolean)(pppFrontDataCollection.pageinfo && pppFrontDataCollection.pageinfo.isCheckout)
  }

  function isConflictiveWC () {
    return (Boolean)(pppFrontDataCollection && pppFrontDataCollection.isConflictVersion)
  }

  // isConfliveWC checks for WC versions that do not call updated_checkout on load
  window.addEventListener('load', () => {
    // Isn't possible to listen on a jQuery event by using `addEventListener`
    if (isCheckoutPayPage() || (isCheckoutPage() && isConflictiveWC())) {
      bootstrapPayPalApp()
    }

    if (isCheckoutPage()) {
      jQuery(document.body).on('updated_checkout', () => {
        bootstrapPayPalApp()
      })
    }
  })
})(jQuery, _, window.pppFrontDataCollection)

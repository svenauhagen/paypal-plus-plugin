import { bootstrapPayPalApp } from './plusFrameView'

(function (jQuery, _, pageinfo) {

  if (typeof pageinfo === 'undefined') {
    return
  }

  window.addEventListener('load', () => {
    // Isn't possible to listen on a jQuery event by using `addEventListener`
    if (pageinfo.isCheckoutPayPage) {
      bootstrapPayPalApp()
    }

    if (pageinfo.isCheckout && !pageinfo.isCheckoutPayPage) {
      jQuery(document.body).on('updated_checkout', () => {
        bootstrapPayPalApp()
      })
    }
  })
})(jQuery, _, window.pageinfo)

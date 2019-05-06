import { RequestFactory } from './expressCheckout/Request'
import { SmartPaymentButtonRendererFactory } from './expressCheckout/smartPaymentButton'

(function (jQuery, _, expressCheckoutData) {
  /**
   * Ensure the Global Express Checkout is a Valid Value
   * @returns {boolean}
   */
  function isValidExpressCheckoutData ()
  {
    return !_.isUndefined(expressCheckoutData) && !_.isEmpty(expressCheckoutData)
  }

  /**
   * Bootstrap Express checkout
   * @returns void
   */
  function bootstrapExpressCheckout ()
  {
    if (!isValidExpressCheckoutData()) {
      return
    }

    const requestData = expressCheckoutData.request
    if (!_.isObject(requestData)) {
      return
    }

    const validContexts = expressCheckoutData.validContexts
    if (!_.isArray(validContexts)) {
      return
    }

    const request = RequestFactory(requestData.ajaxUrl, requestData.action)
    const smartPaymentButton = SmartPaymentButtonRendererFactory(
      expressCheckoutData,
      validContexts,
      request,
    )

    smartPaymentButton.singleProductButtonRender()
    smartPaymentButton.cartButtonRender()
    jQuery(document.body).on(
      'wc_fragments_refreshed wc_fragments_loaded removed_from_cart added_to_cart', () => {
        smartPaymentButton.cartButtonRender()
      })
  }

  window.addEventListener('load', () => {
    bootstrapExpressCheckout()
  })
})(jQuery, window._, window.wooPayPalPlusExpressCheckout)

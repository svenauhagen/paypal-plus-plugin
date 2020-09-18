import { formDataByElement, formDataForCart } from './form'
import { contextByElement } from './context'
import * as utils from '../utils'

const SINGLE_PRODUCT_BUTTON = 'paypalplus_ecs_single_product_button'
const CART_BUTTON = 'paypalplus_ecs_cart_button'

const TASK_CREATE_ORDER = 'createOrder'
const TASK_STORE_PAYMENT_DATA = 'storePaymentData'

/**
 * Retrieve Form Data values by the Given Element and context
 *
 * @params element
 * @params {Array} context
 * @returns {Array}
 */
function formDataByContext (element, validContexts)
{
  let formData = []
  const context = contextByElement(element)

  if (-1 === validContexts.indexOf(context)) {
    throw new Error(
      'Invalid context when try to retrieve the form data during express checkout request.',
    )
  }

  try {
    switch (context) {
      case 'cart':
        formData = formDataForCart(element)
        break
      case 'product':
        formData = formDataByElement(element)
        break
    }
  } catch (err) {
  }

  return formData
}

/**
 * Class Smart Payment Button Renderer
 *
 * @type {SmartPaymentButtonRenderer}
 */
const SmartPaymentButtonRenderer = class SmartPaymentButtonRenderer
{
  /**
   * Constructor
   *
   * @param buttonConfiguration
   * @param validContexts
   * @param request
   */
  constructor (buttonConfiguration, validContexts, request)
  {
    this.buttonConfiguration = buttonConfiguration
    this.cancelUrl = this.buttonConfiguration.redirect_urls.cancel_url
    this.validContexts = validContexts
    this.request = request
  }

  /**
   * Render button for single product
   */
  singleProductButtonRender ()
  {
    const element = document.querySelector(`#${SINGLE_PRODUCT_BUTTON}`)
    const variationsForm = document.querySelector('.variations_form')
    const hasVariations = variationsForm && variationsForm.length
    if (!element) {
      return
    }
    if (hasVariations) {
      const selectedVariationContainer = jQuery('.single_variation_wrap')
      selectedVariationContainer.on('show_variation', () => {
        this.render(element)
      })
      selectedVariationContainer.on('hide_variation', function () {
        const scriptButton = document.querySelector('#paypalplus_ecs_single_product_button')
        scriptButton.parentNode.removeChild(scriptButton)
      })
    } else {
      this.render(element)
    }
  }

  /**
   * Render Button for Cart
   */
  cartButtonRender ()
  {
    const element = document.querySelector(`#${CART_BUTTON}`)
    element && this.render(element)
  }

  /**
   * Render Button for the Given Element
   *
   * @param element
   * @returns {*}
   */
  // TODO Make it private
  render (element)
  {
    if (_.isUndefined(paypal)) {
      return
    }

    const button = element.querySelector('.paypal-button')
    button && button.parentNode.removeChild(button)

    paypal.Button.render({
      ...this.buttonConfiguration,

      /**
       * Do Payment
       *
       * @returns {*}
       */
      payment: () => {
        let formData = formDataByContext(element, this.validContexts)
        formData = formData.concat([{name: 'task', value: TASK_CREATE_ORDER}])

        return this.request
          .submit(formData)
          .then(response => {
            if (!'data' in response) {
              console.warn('Unable to process the payment, server did not response with valid data')
              try {
                window.location = this.cancelUrl
              } catch (e) {
                return
              }
            }

            if (!response.success) {
              try {
                window.location = utils.redirectUrlByRequest(response, this.cancelUrl)
              } catch (e) {
                return
              }
            }

            const orderId = 'orderId' in response.data ? response.data.orderId : ''

            if (!orderId) {
              try {
                window.location = utils.redirectUrlByRequest(response, this.cancelUrl)
              } catch (e) {
                return
              }
            }

            return orderId
          }).catch(error => {
            const textStatus = 'textStatus' in error ? error.textStatus : 'Unknown Error during payment'
            console.warn(textStatus)
          })
      },

      /**
       * Execute Authorization
       *
       * @param {Array} data
       * @param actions
       * @returns {*}
       */
      onAuthorize: (data, actions) => {
        // TODO Ensure return_url exists.
        let formData = formDataByContext(element, this.validContexts)

        formData = formData.concat(formData, [
          {name: 'task', value: TASK_STORE_PAYMENT_DATA},
          {name: 'orderId', value: encodeURIComponent(data.OrderID)},
          {name: 'PayerID', value: encodeURIComponent(data.payerID)},
          {name: 'paymentId', value: encodeURIComponent(data.paymentID)},
          {name: 'token', value: encodeURIComponent(data.paymentToken)},
        ])

        return this.request.submit(formData).then(response => {
          if (!response.success) {
            try {
              window.location = utils.redirectUrlByRequest(response, this.cancelUrl)
            } catch (e) {
              return
            }
          }

          let returnUrl = ''

          if ('redirect_urls' in this.buttonConfiguration
            && 'return_url' in this.buttonConfiguration.redirect_urls
          ) {
            returnUrl = this.buttonConfiguration.redirect_urls.return_url
          }

          returnUrl && actions.redirect(null, returnUrl)
        })
      },

      /**
       * Perform Action when a Payment get Cancelled
       *
       * @param data
       * @param actions
       */
      onCancel: (data, actions) => {
        actions.close()
        const cancelUrl = 'cancelUrl' in data ? data.cancelUrl : ''
        cancelUrl && actions.redirect(null, cancelUrl)
      },

      onError: (data, actions) => {
        console.log('ON ERROR', data, actions)
        // TODO Redirect to cart and show customizable notice with message.
      },

    }, element)
  }
}

/**
 * Smart Payment Button Renderer Factory
 *
 * @param buttonConfiguration
 * @param validContexts
 * @param request
 * @returns {SmartPaymentButtonRenderer}
 * @constructor
 */
export function SmartPaymentButtonRendererFactory (buttonConfiguration, validContexts, request)
{
  const object = new SmartPaymentButtonRenderer(buttonConfiguration, validContexts, request)

  Object.freeze(object)

  return object
}

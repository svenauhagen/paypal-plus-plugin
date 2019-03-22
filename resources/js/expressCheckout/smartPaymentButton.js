import { formDataByElement, formDataForCart } from './form'
import { contextByElement } from './context'

const SINGLE_PRODUCT_BUTTON = 'paypalplus_ecs_single_product_button'
const CART_BUTTON = 'paypalplus_ecs_cart_button'

const TASK_CREATE_ORDER = 'createOrder'
const TASK_STORE_PAYMENT_DATA = 'storePaymentData'

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
    this.validContexts = Array.from(validContexts)
    this.request = request
  }

  /**
   * Render button for single product
   */
  singleProductButtonRender ()
  {
    const element = document.querySelector(`#${SINGLE_PRODUCT_BUTTON}`)
    element && this.render(element)
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
    button && button.remove()

    paypal.Button.render({
      ...this.buttonConfiguration,

      /**
       * Do Payment
       *
       * @returns {*}
       */
      payment: () => {
        const formData = this.formDataByElement(element)
        formData.append('task', TASK_CREATE_ORDER)

        return this.request
          .submit(formData)
          .then(response => {
            if (!('data' in response)) {
              console.warn('Unable to process the payment, server did not response with valid data')
              return
            }
            if (!response.success) {
              // TODO Do something to inform user about the problem and close the flow.
            }

            const orderId = 'orderID' in response.data ? response.data.orderID : ''
            if (!orderId) {
              // TODO Do something to inform user about the problem and close the flow.
            }

            return orderId
          })
          .catch(error => {
            const textStatus = 'textStatus' in error ? error.textStatus : 'Unknown Error during payment'
            console.warn(textStatus)
          })
      },

      /**
       * Execute Authorization
       *
       * @param data
       * @param actions
       * @returns {*}
       */
      onAuthorize: (data, actions) => {
        // TODO Ensure return_url exists.
        const formData = this.formDataByElement(element)

        formData.append('task', TASK_STORE_PAYMENT_DATA)
        formData.append('orderID', data.orderID)
        formData.append('payerID', data.payerID)
        formData.append('paymentID', data.paymentID)
        formData.append('paymentToken', data.paymentToken)

        return this.request
          .submit(formData)
          .then((response) => {
            if (response.success) {
              const returnUrl = 'returnUrl' in data ? data.returnUrl : ''
              returnUrl && actions.redirect(null, returnUrl)
            }

            // TODO Show alert to the user
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
      }

    }, element)
  }

  /**
   * Retrieve context for FormData instance by the Given Element
   *
   * @param element
   * @returns {FormData}
   */
  // TODO Make it private if not possible move it as closure within the render function.
  formDataByElement (element)
  {
    let formData = new FormData()
    const context = contextByElement(element)

    if (!this.validContexts.includes(context)) {
      throw new Error(
        'Invalid context when try to retrieve the form data during express checkout request.'
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

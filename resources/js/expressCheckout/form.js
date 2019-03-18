import { contextByElement } from './context'

const CART_BUTTON_SELECTOR = 'woo-paypalplus-checkout-nonce'

/**
 * Create a FormData object by the closest form of the given element
 *
 * @param element
 * @returns {FormData}
 */
export function formDataByElement (element) {
  const form = element.closest('form')

  if (!form) {
    throw new Error(
      'Unable to retrieve form. Form element does not exists or is not a parent of the given element.'
    )
  }

  const formData = new FormData(form)
  formData.append('context', contextByElement(element))

  return formData
}

/**
 * Create a FormData for the Cart
 * WooCommerce mini cart doesn't have any form associated with it
 *
 * @param element
 * @returns {FormData}
 */
export function formDataForCart (element) {
  try {
    const [nonceName, nonceValue] = retrieveNonceForCart(element)
    const formData = new FormData()

    formData.append('context', contextByElement(element))
    formData.append(nonceName, nonceValue)

    return formData
  } catch (err) {
    return new FormData()
  }
}

function retrieveNonceForCart (element) {
  const nonceElement = element.parentNode.querySelector(`.${CART_BUTTON_SELECTOR}`)

  if (!nonceElement) {
    throw new Error('Cannot build the form data, missing security nonce.')
  }

  const nonceName = ('noncename' in nonceElement.dataset) ? nonceElement.dataset.noncename : ''
  const nonceValue = ('noncevalue' in nonceElement.dataset) ? nonceElement.dataset.noncevalue : ''

  if (!nonceName || !nonceValue) {
    throw new Error('Cannot build the form data, missing security nonce.')
  }

  return [
    nonceName,
    nonceValue
  ]
}

import { contextByElement } from './context'

const CART_BUTTON_SELECTOR = 'woo-paypalplus-checkout-nonce'

/**
 * Create a FormData object by the closest form of the given element
 *
 * @param element
 * @returns {String}
 */
export function formDataByElement (element)
{
  const form = element.closest('form')

  if (!form) {
    throw new Error(
      'Unable to retrieve form. Form element does not exists or is not a parent of the given element.',
    )
  }

  let formData = jQuery(form).serialize()
  const context = contextByElement(element)

  formData += `&context=${context}`

  return formData
}

/**
 * Create a FormData for the Cart
 * WooCommerce mini cart doesn't have any form associated with it
 *
 * @param element
 * @returns {String}
 */
export function formDataForCart (element)
{
  try {
    const [nonceName, nonceValue] = retrieveNonceForCart(element)
    const context = contextByElement(element)
    return `context=${context}&${nonceName}=${nonceValue}`
  } catch (err) {
    return ''
  }
}

function retrieveNonceForCart (element)
{
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
    nonceValue,
  ]
}

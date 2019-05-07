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
  const $form = jQuery(element).closest('form')

  if (!$form.length) {
    throw new Error(
      'Unable to retrieve form. Form element does not exists or is not a parent of the given element.',
    )
  }

  let formData = $form.serializeArray()
  const context = contextByElement(element)

  formData = formData.concat([{name: 'context', value: context}])
  formData = formData.filter(item => item.name !== 'add-to-cart')

  return formData
}

/**
 * Create a FormData for the Cart
 * WooCommerce mini cart doesn't have any form associated with it
 *
 * @param element
 * @returns {Array}
 */
export function formDataForCart (element)
{
  try {
    const [nonceName, nonceValue] = retrieveNonceForCart(element)
    const context = contextByElement(element)

    return [
      {name: 'context', value: encodeURIComponent(context)},
      {name: nonceName, value: encodeURIComponent(nonceValue)},
    ]
  } catch (err) {
    return []
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

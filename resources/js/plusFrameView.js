const FRAME_SELECTOR = 'ppplus'

/**
 * Retrieve the configuration from the json string attached to an element
 * @param frameElement
 * @returns {*}
 */
function appConfigurationFromElement (frameElement)
{
  if (!frameElement) {
    return null
  }

  try {
    return JSON.parse(frameElement.dataset.config)
  } catch (e) {
    return null
  }
}

/**
 * Setup the PayPal Application
 * @returns {*}
 */
export function bootstrapPayPalApp ()
{
  const frameElement = document.querySelector(`#${FRAME_SELECTOR}`)
  const appConfiguration = appConfigurationFromElement(frameElement)

  if (typeof PAYPAL === 'undefined') {
    return
  }
  if (!frameElement || !appConfiguration) {
    return
  }

  return PAYPAL.apps.PPP(appConfiguration)
}

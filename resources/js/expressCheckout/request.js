/**
 * Class Request
 *
 * @type {Request}
 */
const Request = class Request
{
  // TODO Make the fields private if possible.
  constructor (ajaxUrl, action)
  {
    this.ajaxUrl = ajaxUrl
    this.action = action
  }

  submit (formData)
  {
    // TODO Extract specific data such as: action, nonce, task, context and
    //      put the rest within a specific object.
    //      Make a separation for data controls and real request data.

    if (_.isEmpty(formData)) {
      return Promise.reject('No formData to send to the server.')
    }

    formData += `&action=${this.action}`;

    return new Promise((resolve, reject) => {
      jQuery.ajax({
        traditional: true,
        url: this.ajaxUrl,
        method: 'POST',
        data: formData,
        error: reject,
        success: resolve,
      })
    })
  }
}

/**
 * Request Factory
 *
 * @param ajaxUrl
 * @param action
 * @returns {Request}
 */
export function RequestFactory (ajaxUrl, action)
{
  if (!ajaxUrl || !action) {
    throw new Error('Invalid parameters when construct Request instance')
  }

  let object = new Request(ajaxUrl, action)
  Object.freeze(object)

  return object
}

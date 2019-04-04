/**
 * Build Redirect Url By Ajax Request
 *
 * @param response
 * @param defaultUrl
 * @returns {string|*|string}
 */
function redirectUrlByRequest (response, defaultUrl = '')
{
  if (!'data' in response) {
    return defaultUrl
  }

  const redirectUrl = ('redirectUrl' in response.data) ? response.data.redirectUrl : defaultUrl

  return redirectUrl
}

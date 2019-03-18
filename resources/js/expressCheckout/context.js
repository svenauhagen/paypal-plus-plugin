/**
 * Retrieve Context by Element
 *
 * @param element
 * @returns {string}
 */
export function contextByElement (element)
{
  return ('context' in element.dataset ? element.dataset.context : '')
}

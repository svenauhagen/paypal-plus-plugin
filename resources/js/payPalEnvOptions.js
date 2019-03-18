const TEST_MODE_SELECTOR_ID = 'paypalplus_testmode'
const SANDBOX_DATA_SELECTORS = [
  'paypalplus_rest_client_id_sandbox',
  'paypalplus_rest_secret_id_sandbox',
  'paypalplus_sandbox_experience_profile_id'
]
const PRODUCTION_DATA_SELECTORS = [
  'paypalplus_rest_client_id',
  'paypalplus_rest_secret_id',
  'paypalplus_live_experience_profile_id'
]

/**
 * Toggle the Environment Fields based on given values
 * @param fieldsSelectors
 * @param style
 */
function toggleEnvFields (fieldsSelectors, style) {
  fieldsSelectors.forEach(itemId => {
    const itemEl = document.querySelector(`#${itemId}`)
    if (!itemEl) {
      return
    }

    const container = itemEl.closest('tr')
    if (!container) {
      return
    }

    itemEl.closest('tr').style.display = style
  })
}

/**
 * Initialize the Environment
 */
export function envOptionsInitialize () {
  const modeElement = document.getElementById(TEST_MODE_SELECTOR_ID)
  if (!modeElement) {
    return
  }

  modeElement.addEventListener(
    'change',
    (event) => {
      const testModeCheckbox = event.currentTarget
      if (testModeCheckbox.getAttribute('id') !== TEST_MODE_SELECTOR_ID) {
        return
      }

      switch (testModeCheckbox.checked) {
        case true:
          toggleEnvFields(SANDBOX_DATA_SELECTORS, 'table-row')
          toggleEnvFields(PRODUCTION_DATA_SELECTORS, 'none')
          break
        default:
          toggleEnvFields(SANDBOX_DATA_SELECTORS, 'none')
          toggleEnvFields(PRODUCTION_DATA_SELECTORS, 'table-row')
          break
      }
    }
  )

  modeElement.dispatchEvent(new Event('change'))
}

const TEST_MODE_SELECTOR_ID = 'paypalplus_testmode';
const SANDBOX_DATA_SELECTORS = [
    'paypalplus_rest_client_id_sandbox',
    'paypalplus_rest_secret_id_sandbox',
    'paypalplus_sandbox_experience_profile_id'
];
const PRODUCTION_DATA_SELECTORS = [
    'paypalplus_rest_client_id',
    'paypalplus_rest_secret_id',
    'paypalplus_live_experience_profile_id'
];

/**
 * Toggle the Environment Fields based on given values
 * @param fieldsSelectors
 * @param style
 */
function toggleEnvFields(fieldsSelectors, style)
{
    fieldsSelectors.forEach(itemId => {
        const itemEl = document.querySelector(`#${itemId}`);
        if (!itemEl) {
            return;
        }

        const container = itemEl.closest('tr');
        if (!container) {
            return;
        }

        itemEl.closest('tr').style.display = style;
    });
}

/**
 * Toggle the Environment Form
 *
 * @param toggler
 */
function environmentToggler(toggler)
{
    if (toggler.getAttribute('id') !== TEST_MODE_SELECTOR_ID) {
        return;
    }

    switch (toggler.checked) {
        case true:
            toggleEnvFields(SANDBOX_DATA_SELECTORS, 'table-row');
            toggleEnvFields(PRODUCTION_DATA_SELECTORS, 'none');
            break;
        default:
            toggleEnvFields(SANDBOX_DATA_SELECTORS, 'none');
            toggleEnvFields(PRODUCTION_DATA_SELECTORS, 'table-row');
            break;
    }
}

/**
 * Initialize the Environment
 */
export function envOptionsInitialize()
{
    const modeElement = document.getElementById(TEST_MODE_SELECTOR_ID);
    if (!modeElement) {
        return;
    }

    environmentToggler(modeElement);

    modeElement.addEventListener(
        'change',
        (event) => {
            environmentToggler(event.currentTarget);
        }
    );
}

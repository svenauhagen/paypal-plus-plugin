<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 12:13
 */

namespace WCPayPalPlus\WC;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\PayPal\Exception\PayPalInvalidCredentialException;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use const WCPayPalPlus\ACTION_LOG;

/**
 * Class CredentialVerification
 *
 * @package WCPayPalPlus\WC
 */
class CredentialVerification
{
    /**
     * PayPal SDK API Context object.
     *
     * @var ApiContext
     */
    private $context;
    /**
     * The last error that occurred.
     *
     * @var string
     */
    private $error;

    /**
     * CredentialVerification constructor.
     *
     * @param ApiContext $context PayPal SDK API Context object.
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * Verify the API Credentials by making a dummy API call with them.
     *
     * @return bool
     */
    public function verify()
    {
        $api_context = $this->context;
        if (is_null($api_context)) {
            return false;
        }
        $credential = $api_context->getCredential();
        if (empty($credential->getClientId()) || empty($credential->getClientSecret())) {
            $this->error = 'Missing API Credentials';

            return false;
        }
        try {
            $params = ['count' => 1];
            Payment::all($params, $api_context);
        } catch (PayPalInvalidCredentialException $ex) {
            do_action(ACTION_LOG, \WC_Log_Levels::ERROR, 'credential_exception:' . $ex->getMessage(), compact($ex));
            $this->error = $ex->getMessage();

            return false;
        } catch (PayPalConnectionException $ex) {
            do_action(ACTION_LOG, \WC_Log_Levels::ERROR, 'credential_exception:' . $ex->getMessage(), compact($ex));
            $this->error = $ex->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Returns the last error that occurred during verification
     *
     * @return string
     */
    public function get_error_message()
    {
        return $this->error;
    }
}

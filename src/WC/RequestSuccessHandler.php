<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 10:55
 */

namespace WCPayPalPlus\WC;

interface RequestSuccessHandler {
	/**
	 * Allow the implementing class to setup hooks
	 */
	public function register();
	/**
	 * Handles a successful REST call
	 *
	 * @return bool
	 */
	public function execute();
}

<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 25.10.16
 * Time: 15:30
 */

namespace PayPalPlusPlugin;

/**
 * Interface Controller
 *
 * @package PayPalPlusPlugin
 */
interface Controller {

	/**
	 * Initializes the Controller.
	 *
	 * @return mixed
	 */
	public function init();
}
<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 27.01.17
 * Time: 11:47
 */

namespace PayPalPlusPlugin\WC\Payment;

/**
 * Class OrderItemData
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
class OrderItemData implements OrderItemDataProvider {

	use OrderDataProcessor;

	/**
	 * Item data.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * OrderItemData constructor.
	 *
	 * @param array $data Item data.
	 */
	public function __construct( array $data ) {

		$this->data = $data;
	}

	/**
	 * Returns the item price.
	 *
	 * @return string
	 */
	public function get_price() {

		return $this->format( $this->data['line_subtotal'] / $this->get_quantity() );
	}

	/**
	 * Returns the item quantity.
	 *
	 * @return int
	 */
	public function get_quantity() {

		return intval( $this->data['qty'] );
	}

	/**
	 * Returns the item name.
	 *
	 * @return string
	 */
	public function get_name() {

		$product = $this->get_product();

		return $product->get_title();
	}

	/**
	 * Returns the WC_Product associated with the order item.
	 *
	 * @return \WC_Product
	 */
	public function get_product() {

		return wc_get_product( $this->data['product_id'] );
	}

	/**
	 * Returns the product SKU.
	 * TODO Un-DRY. CartItemData does pretty much the exact same thing
	 *
	 * @return string|null
	 */
	public function get_sku() {

		$product = $this->get_product();
		$sku     = $product->get_sku();
		if ( $product instanceof \WC_Product_Variation ) {
			$sku = $product->parent->get_sku();
		}

		return $sku;
	}
}

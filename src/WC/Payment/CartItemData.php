<?php
namespace PayPalPlusPlugin\WC\Payment;

class CartItemData implements OrderItemDataProvider {

	use OrderDataProcessor;

	/**
	 * @var array
	 */
	private $data;

	public function __construct( array $data ) {

		$this->data = $data;
	}

	public function get_price() {

		return $this->format( $this->data['line_subtotal'] / $this->get_quantity() );
	}

	public function get_quantity() {

		return intval( $this->data['quantity'] );
	}

	/**
	 * @return string
	 */
	public function get_name() {

		$product = $this->get_product();

		return $product->get_title();
	}

	protected function get_product() {

		return wc_get_product( $this->data['product_id'] );
	}

	/**
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
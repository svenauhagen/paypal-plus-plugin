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

	public function get_product() {

		return wc_get_product( $this->data['product_id'] );
	}
}
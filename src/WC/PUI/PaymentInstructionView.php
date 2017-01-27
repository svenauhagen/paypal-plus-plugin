<?php
namespace PayPalPlusPlugin\WC\PUI;

/**
 * Class PaymentInstructionView
 */
class PaymentInstructionView {

	/**
	 * @var PaymentInstructionData
	 */
	private $data;

	/**
	 * PaymentInstructionView constructor.
	 *
	 * @param PaymentInstructionData $data PUI Data provider.
	 */
	public function __construct( PaymentInstructionData $data ) {

		$this->data = $data;
	}

	/**
	 *
	 */
	public function thankyou_page() {

		echo wpautop( wptexturize( __(
				'Please transfer the complete amount to the bank account provided below.',
				'woo-paypal-plus'
			) ) ) . PHP_EOL;
		?>
		<h2><?php _e( 'PayPal Bank Details', 'woo-paypal-plus' ); ?></h2>
		<table class="shop_table order_details">
			<tbody>
			<tr>
				<th scope="row"><?php _e( 'Bank name:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo $this->data->get_bank_name(); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Account holder name:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo $this->data->get_account_holder_name(); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'IBAN:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo $this->data->get_international_bank_account_number(); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'BIC:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo $this->data->get_bank_identifier_code(); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Payment due date:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo $this->data->get_payment_due_date() ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Reference:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo $this->data->get_reference_number(); ?></span></td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	public function email_instructions() {

		echo wpautop( wptexturize( __(
				'Please transfer the complete amount to the bank account provided below.',
				'woo-paypal-plus'
			) ) ) . PHP_EOL;
		echo '<h2 class="wc-bacs-bank-details-heading">' . __( 'PayPal Bank Details',
				'woo-paypal-plus' ) . '</h2>' . PHP_EOL;

		echo '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;

		$account_fields = [
			'bank_name'           => [
				'label' => __( 'Bank name', 'woo-paypal-plus' ),
				'value' => $this->data->get_bank_name(),
			],
			'account_holder_name' => [
				'label' => __( 'Account holder name', 'woo-paypal-plus' ),
				'value' => $this->data->get_account_holder_name(),
			],
			'iban'                => [
				'label' => __( 'IBAN', 'woo-paypal-plus' ),
				'value' => $this->data->get_international_bank_account_number(),
			],
			'bic'                 => [
				'label' => __( 'BIC', 'woo-paypal-plus' ),
				'value' => $this->data->get_bank_identifier_code(),
			],
			'payment_due_date'    => [
				'label' => __( 'Payment due date', 'woo-paypal-plus' ),
				'value' => $this->data->get_payment_due_date(),
			],
			'reference_number'    => [
				'label' => __( 'Reference', 'woo-paypal-plus' ),
				'value' => $this->data->get_reference_number(),
			],
		];

		foreach ( $account_fields as $field_key => $field ) {
			if ( ! empty( $field['value'] ) ) {
				echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li>' . PHP_EOL;
			}
		}

		echo '</ul>';
	}

}
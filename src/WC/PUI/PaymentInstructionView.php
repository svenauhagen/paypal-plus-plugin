<?php
namespace PayPalPlusPlugin\WC\PUI;

/**
 * Class PaymentInstructionView
 */
class PaymentInstructionView {

	/**
	 * PUI data
	 *
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
	 * Render the instructions table on the thank you page
	 */
	public function thankyou_page() {

		echo esc_html__( 'Please transfer the complete amount to the bank account provided below.',
				'woo-paypal-plus' ) . PHP_EOL;
		?>
		<h2><?php esc_html_e( 'PayPal Bank Details', 'woo-paypal-plus' ); ?></h2>
		<table class="shop_table order_details">
			<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Bank name:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_bank_name() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Account holder name:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_account_holder_name() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'IBAN:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_iban() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'BIC:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_bic() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Payment due date:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_payment_due_date() ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Reference:', 'woo-paypal-plus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_reference_number() ); ?></span></td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Renders the instructions table.
	 */
	public function email_instructions() {

		echo esc_html__( 'Please transfer the complete amount to the bank account provided below.', 'woo-paypal-plus'
		     ) . PHP_EOL;
		echo '<h2 class="wc-bacs-bank-details-heading">' . esc_html( __( 'PayPal Bank Details',
				'woo-paypal-plus' ) ) . '</h2>' . PHP_EOL;

		echo '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;

		foreach ( $this->get_account_fields() as $field_key => $field ) {
			if ( ! empty( $field['value'] ) ) {
				echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li>' . PHP_EOL;
			}
		}

		echo '</ul>';
	}

	/**
	 * Returns the account fields.
	 *
	 * @return array
	 */
	private function get_account_fields() {

		return [
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
				'value' => $this->data->get_iban(),
			],
			'bic'                 => [
				'label' => __( 'BIC', 'woo-paypal-plus' ),
				'value' => $this->data->get_bic(),
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

	}

}
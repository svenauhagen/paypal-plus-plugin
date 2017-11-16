<?php
namespace WCPayPalPlus\WC\PUI;

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
				'woo-paypalplus' ) . PHP_EOL;
		?>
		<h2><?php esc_html_e( 'PayPal Bank Details', 'woo-paypalplus' ); ?></h2>
		<table class="shop_table order_details">
			<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Bank name:', 'woo-paypalplus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_bank_name() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Account holder name:', 'woo-paypalplus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_account_holder_name() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'IBAN:', 'woo-paypalplus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_iban() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'BIC:', 'woo-paypalplus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_bic() ); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Payment due date:', 'woo-paypalplus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_payment_due_date() ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Reference:', 'woo-paypalplus' ); ?></th>
				<td><span><?php echo esc_html( $this->data->get_reference_number() ); ?></span></td>
			</tr>
			</tbody>
		</table>
		<?php
        echo $this->data->get_legal_note();
	}

    /**
     * Renders the instructions table.
     *
     * @param bool $plain_text
     */
	public function email_instructions( $plain_text = false ) {

        if ( ! $plain_text ) {
            echo esc_html__( 'Please transfer the complete amount to the bank account provided below.', 'woo-paypalplus'
                 ) . PHP_EOL;
            echo '<h2 class="wc-bacs-bank-details-heading">' . esc_html( __( 'PayPal Bank Details',
                    'woo-paypalplus' ) ) . '</h2>' . PHP_EOL;

            echo '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;

            foreach ( $this->get_account_fields() as $field_key => $field ) {
                if ( ! empty( $field['value'] ) ) {
                    echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li>' . PHP_EOL;
                }
            }

            echo '</ul>';
            echo $this->data->get_legal_note() . PHP_EOL;
        } else {
            echo esc_html__( 'Please transfer the complete amount to the bank account provided below.', 'woo-paypalplus' ) . PHP_EOL;
            echo esc_html( __( 'PayPal Bank Details', 'woo-paypalplus' ) ) . PHP_EOL;

            foreach ( $this->get_account_fields() as $field_key => $field ) {
                if ( ! empty( $field['value'] ) ) {
                    echo ' - ' . esc_attr( $field['label'] ) . ': ' . wptexturize( $field['value'] ) . PHP_EOL;
                }
            }

            echo PHP_EOL . esc_html( strip_tags( $this->data->get_legal_note() ) ) . PHP_EOL;
        }
	}

	/**
	 * Returns the account fields.
	 *
	 * @return array
	 */
	private function get_account_fields() {

		return [
			'bank_name'           => [
				'label' => __( 'Bank name', 'woo-paypalplus' ),
				'value' => $this->data->get_bank_name(),
			],
			'account_holder_name' => [
				'label' => __( 'Account holder name', 'woo-paypalplus' ),
				'value' => $this->data->get_account_holder_name(),
			],
			'iban'                => [
				'label' => __( 'IBAN', 'woo-paypalplus' ),
				'value' => $this->data->get_iban(),
			],
			'bic'                 => [
				'label' => __( 'BIC', 'woo-paypalplus' ),
				'value' => $this->data->get_bic(),
			],
			'payment_due_date'    => [
				'label' => __( 'Payment due date', 'woo-paypalplus' ),
				'value' => $this->data->get_payment_due_date(),
			],
			'reference_number'    => [
				'label' => __( 'Reference', 'woo-paypalplus' ),
				'value' => $this->data->get_reference_number(),
			],
		];

	}

}
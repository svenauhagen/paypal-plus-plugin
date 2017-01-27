<?php
namespace PayPalPlusPlugin\WC\PUI;

/**
 * Class PaymentInstructionView
 */
class PaymentInstructionView implements PUIView {

	/**
	 * @var PaymentInstructionData
	 */
	private $data;

	/**
	 * PaymentInstructionView constructor.
	 *
	 * @param PaymentInstructionData $data
	 */
	public function __construct( PaymentInstructionData $data ) {

		$this->data = $data;
	}

	public function thankyou_page( $order_id ) {
		if ( $this->pay_upon_invoice_instructions ) {
			echo wpautop( wptexturize( wp_kses_post( $this->pay_upon_invoice_instructions ) ) );
		}
		?>
		<h2><?php _e( 'PayPal Bank Details', 'woo-paypal-plus' ); ?></h2>
		<table class="shop_table order_details">
			<tbody>
			<?php if ( ! empty( $bank_name ) ) : ?>
				<tr>
					<th scope="row"><?php _e( 'Bank name:', 'woo-paypal-plus' ); ?></th>
					<td><span><?php echo $bank_name; ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ( ! empty( $account_holder_name ) ) : ?>
				<tr>
					<th scope="row"><?php _e( 'Account holder name:', 'woo-paypal-plus' ); ?></th>
					<td><span><?php echo $account_holder_name; ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ( ! empty( $international_bank_account_number ) ) : ?>
				<tr>
					<th scope="row"><?php _e( 'IBAN:', 'woo-paypal-plus' ); ?></th>
					<td><span><?php echo $international_bank_account_number; ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ( ! empty( $bank_identifier_code ) ) : ?>
				<tr>
					<th scope="row"><?php _e( 'BIC:', 'woo-paypal-plus' ); ?></th>
					<td><span><?php echo $bank_identifier_code; ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ( ! empty( $payment_due_date ) ) : ?>
				<tr>
					<th scope="row"><?php _e( 'Payment due date:', 'woo-paypal-plus' ); ?></th>
					<td><span><?php echo date_i18n( get_option( 'date_format' ),
								strtotime( $payment_due_date ) ); ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ( ! empty( $reference_number ) ) : ?>
				<tr>
					<th scope="row"><?php _e( 'Reference:', 'woo-paypal-plus' ); ?></th>
					<td><span><?php echo $reference_number; ?></span></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

		echo wpautop( wptexturize( __(
				'Please transfer the complete amount to the bank account provided below.',
				'woo-paypal-plus'
			) ) ) . PHP_EOL;
		$this->bank_details( $order->id );
	}

	/**
	 * Get bank details and place into a list format.
	 *
	 * @param int $order_id
	 */
	private function bank_details( $order_id = '' ) {

		$bank_name                         = get_post_meta( $order_id, 'bank_name', true );
		$account_holder_name               = get_post_meta( $order_id, 'account_holder_name', true );
		$international_bank_account_number = get_post_meta( $order_id, 'international_bank_account_number', true );
		$payment_due_date                  = get_post_meta( $order_id, 'payment_due_date', true );
		$reference_number                  = get_post_meta( $order_id, 'reference_number', true );
		$bank_identifier_code              = get_post_meta( $order_id, 'bank_identifier_code', true );

		echo '<h2 class="wc-bacs-bank-details-heading">' . __( 'PayPal Bank Details',
				'woo-paypal-plus' ) . '</h2>' . PHP_EOL;

		echo '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;

		$account_fields = [
			'bank_name'           => [
				'label' => __( 'Bank name', 'woo-paypal-plus' ),
				'value' => $bank_name,
			],
			'account_holder_name' => [
				'label' => __( 'Account holder name', 'woo-paypal-plus' ),
				'value' => $account_holder_name,
			],
			'iban'                => [
				'label' => __( 'IBAN', 'woo-paypal-plus' ),
				'value' => $international_bank_account_number,
			],
			'bic'                 => [
				'label' => __( 'BIC', 'woo-paypal-plus' ),
				'value' => $bank_identifier_code,
			],
			'payment_due_date'    => [
				'label' => __( 'Payment due date', 'woo-paypal-plus' ),
				'value' => $payment_due_date,
			],
			'reference_number'    => [
				'label' => __( 'Reference', 'woo-paypal-plus' ),
				'value' => $reference_number,
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
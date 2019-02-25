jQuery(document).ready( ($)=> {


	jQuery('.paypal_plus_order_cancellations').change( ()=> {
		var email_notify_order_cancellations = jQuery('.paypal_plus_email_notify_order_cancellations').closest('tr');
		if (jQuery(this).val() !== 'disabled') {
			email_notify_order_cancellations.show();
		} else {
			email_notify_order_cancellations.hide();
		}
	}).change();

	jQuery( '#woocommerce_paypal_plus_testmode' ).change( function() {
		jQuery( "#woocommerce_paypal_plus_live_experience_profile_id" ).prop( "readonly", true );
		jQuery( "#woocommerce_paypal_plus_sandbox_experience_profile_id" ).prop( "readonly", true );
		let sandbox = jQuery(
			'#woocommerce_paypal_plus_rest_client_id_sandbox, #woocommerce_paypal_plus_rest_secret_id_sandbox, #woocommerce_paypal_plus_sandbox_experience_profile_id' ).closest(
			'tr' ),
			production = jQuery(
				'#woocommerce_paypal_plus_rest_client_id, #woocommerce_paypal_plus_rest_secret_id, #woocommerce_paypal_plus_live_experience_profile_id' ).closest(
				'tr' );
		if ( jQuery( this ).is( ':checked' ) ) {
			sandbox.show();
			production.hide();
		} else {
			sandbox.hide();
			production.show();
		}
	} ).change();

});

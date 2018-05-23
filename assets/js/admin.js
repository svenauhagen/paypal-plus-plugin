(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

jQuery(document).ready(function ($) {

	jQuery('.paypal_plus_order_cancellations').change(function () {
		var email_notify_order_cancellations = jQuery('.paypal_plus_email_notify_order_cancellations').closest('tr');
		if (jQuery(undefined).val() !== 'disabled') {
			email_notify_order_cancellations.show();
		} else {
			email_notify_order_cancellations.hide();
		}
	}).change();

	jQuery('#woocommerce_paypal_plus_testmode').change(function () {
		jQuery("#woocommerce_paypal_plus_live_experience_profile_id").prop("readonly", true);
		jQuery("#woocommerce_paypal_plus_sandbox_experience_profile_id").prop("readonly", true);
		var sandbox = jQuery('#woocommerce_paypal_plus_rest_client_id_sandbox, #woocommerce_paypal_plus_rest_secret_id_sandbox, #woocommerce_paypal_plus_sandbox_experience_profile_id').closest('tr'),
		    production = jQuery('#woocommerce_paypal_plus_rest_client_id, #woocommerce_paypal_plus_rest_secret_id, #woocommerce_paypal_plus_live_experience_profile_id').closest('tr');
		if (jQuery(this).is(':checked')) {
			sandbox.show();
			production.hide();
		} else {
			sandbox.hide();
			production.show();
		}
	}).change();
});

},{}]},{},[1]);

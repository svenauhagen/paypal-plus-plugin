(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

<?php

namespace WPForms\Helpers;

use WPForms\SetupWizard\Service\SettingsDetector;

/**
 * Payment gateway configuration checks shared across admin surfaces.
 *
 * @since {VERSION}
 */
class PaymentGatewayDetector {

	/**
	 * Whether any supported payment gateway — Stripe, PayPal Commerce, Square,
	 * Authorize.Net, Mercado Pago, or Paystack — is configured in the current payment mode.
	 *
	 * @since {VERSION}
	 *
	 * @return bool
	 */
	public static function is_any_payment_gateway_configured(): bool {

		$detector = new SettingsDetector();

		return $detector->is_stripe_configured()
			|| $detector->is_paypal_commerce_configured()
			|| $detector->is_square_configured()
			|| $detector->is_authorize_net_configured()
			|| $detector->is_mercado_pago_configured()
			|| $detector->is_paystack_configured();
	}
}

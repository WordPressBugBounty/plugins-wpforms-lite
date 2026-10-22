<?php

namespace WPForms\Emails\Summaries;

use WPForms\Helpers\EventCounter;

/**
 * Lifetime per-block counters for the weekly summary, reported through Usage Tracking.
 *
 * Same pattern as the builder adoption tooltips: `[ key => [ event => count ] ]` in a
 * non-autoloaded option, exposed to the weekly payload through a dedicated filter.
 *
 * @since {VERSION}
 */
class Tracking {

	/**
	 * Option holding the counters.
	 *
	 * @since {VERSION}
	 */
	public const OPTION = 'wpforms_email_summaries_events';

	/**
	 * Event recorded when a block goes out in a sent summary.
	 *
	 * @since {VERSION}
	 */
	public const EVENT_SHOWN = 'shown';

	/**
	 * Counters store.
	 *
	 * @since {VERSION}
	 *
	 * @var EventCounter
	 */
	private $counter;

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 */
	public function __construct() {

		$this->counter = new EventCounter( self::OPTION );
	}

	/**
	 * Initialize.
	 *
	 * @since {VERSION}
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since {VERSION}
	 */
	private function hooks(): void {

		add_filter( 'wpforms_integrations_usage_tracking_usage_tracking_get_email_summaries_data', [ $this, 'filter_email_summaries_data' ] );
	}

	/**
	 * Increment a counter.
	 *
	 * @since {VERSION}
	 *
	 * @param string $key   Block key.
	 * @param string $event Event name.
	 */
	public function bump( string $key, string $event ): void {

		$this->counter->bump( $key, $event );
	}

	/**
	 * All counters.
	 *
	 * @since {VERSION}
	 *
	 * @return array
	 */
	public function get_events(): array {

		return $this->counter->get_all();
	}

	/**
	 * Add the counters to the weekly usage tracking payload.
	 *
	 * @since {VERSION}
	 *
	 * @param array|mixed $data Counters collected so far.
	 *
	 * @return array
	 */
	public function filter_email_summaries_data( $data ): array {

		return array_merge( (array) $data, $this->get_events() );
	}
}

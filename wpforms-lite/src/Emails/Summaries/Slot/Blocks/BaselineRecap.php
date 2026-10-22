<?php

namespace WPForms\Emails\Summaries\Slot\Blocks;

use WPForms\Emails\Summaries\Slot\AbstractBlock;
use WPForms\Emails\Summaries\Slot\Context;
use WPForms\Emails\Summaries\Slot\Pipeline;

/**
 * Baseline recap: the plain intro every summary falls back to when no other top block fires.
 *
 * @since {VERSION}
 */
class BaselineRecap extends AbstractBlock {

	/**
	 * Block id.
	 *
	 * @since {VERSION}
	 */
	public const ID = 'baseline_recap';

	/**
	 * Slot.
	 *
	 * @since {VERSION}
	 */
	public const SLOT = Pipeline::SLOT_TOP;

	/**
	 * Priority. Last, so it only shows when nothing else does.
	 *
	 * @since {VERSION}
	 */
	public const PRIORITY = 100;

	/**
	 * Intro copy.
	 *
	 * @since {VERSION}
	 *
	 * @param Context $context Summary context.
	 * @param string  $variant Unused.
	 *
	 * @return array
	 */
	public function get_args( Context $context, string $variant ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return [
			'title' => __( 'Your Weekly Recap is Here', 'wpforms-lite' ),
		];
	}
}

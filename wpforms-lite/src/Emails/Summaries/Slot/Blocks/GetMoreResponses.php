<?php

namespace WPForms\Emails\Summaries\Slot\Blocks;

use WPForms\Emails\Summaries\Slot\AbstractBlock;
use WPForms\Emails\Summaries\Slot\BlockState;
use WPForms\Emails\Summaries\Slot\Context;
use WPForms\Emails\Summaries\Slot\Pipeline;

/**
 * Get More Responses: takes the bottom slot on a zero-entry week instead of Tip and Feature Spotlight.
 *
 * @since {VERSION}
 */
class GetMoreResponses extends AbstractBlock {

	/**
	 * Block id.
	 *
	 * @since {VERSION}
	 */
	public const ID = 'get_more_responses';

	/**
	 * Slot.
	 *
	 * @since {VERSION}
	 */
	public const SLOT = Pipeline::SLOT_BOTTOM;

	/**
	 * Priority.
	 *
	 * @since {VERSION}
	 */
	public const PRIORITY = 5;

	/**
	 * Visible only on a zero-entry week.
	 *
	 * @since {VERSION}
	 *
	 * @param Context $context Summary context.
	 *
	 * @return BlockState
	 */
	public function get_state( Context $context ): BlockState {

		return new BlockState( ! $context->has_entries() );
	}

	/**
	 * Copy and CTA.
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
			'title'   => __( 'Get More Responses', 'wpforms-lite' ),
			'content' => __( 'Need help to get more entries? A few tricks can help, like keeping your form short, and being clear about what visitors get for filling it out.', 'wpforms-lite' ),
			'main'    => [
				'text' => __( 'Learn More', 'wpforms-lite' ),
				'url'  => self::utm_link( 'https://wpforms.com/research-based-tips-to-improve-contact-form-conversions/', 'Get More Responses' ),
			],
		];
	}
}

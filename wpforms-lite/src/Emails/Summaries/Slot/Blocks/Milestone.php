<?php

namespace WPForms\Emails\Summaries\Slot\Blocks;

use WPForms\Emails\Summaries\Milestones;
use WPForms\Emails\Summaries\Slot\AbstractBlock;
use WPForms\Emails\Summaries\Slot\BlockState;
use WPForms\Emails\Summaries\Slot\Context;
use WPForms\Emails\Summaries\Slot\Pipeline;

/**
 * Milestone headline: a first or a threshold the site just reached. Each fires once.
 *
 * @since {VERSION}
 */
class Milestone extends AbstractBlock {

	/**
	 * Block id.
	 *
	 * @since {VERSION}
	 */
	public const ID = 'milestone';

	/**
	 * Slot.
	 *
	 * @since {VERSION}
	 */
	public const SLOT = Pipeline::SLOT_TOP;

	/**
	 * Priority.
	 *
	 * @since {VERSION}
	 */
	public const PRIORITY = 30;

	/**
	 * Milestone rules and store.
	 *
	 * @since {VERSION}
	 *
	 * @var Milestones
	 */
	private $milestones;

	/**
	 * Milestone resolved by get_state().
	 *
	 * @since {VERSION}
	 *
	 * @var array|null
	 */
	private $milestone;

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 *
	 * @param Milestones|null $milestones Rules and store. Defaults to the registered service.
	 */
	public function __construct( ?Milestones $milestones = null ) {

		$this->milestones = $milestones ?? wpforms()->obj( 'emails_summaries_milestones' );
	}

	/**
	 * Visible when a new milestone was reached; the variant is its key.
	 *
	 * @since {VERSION}
	 *
	 * @param Context $context Summary context.
	 *
	 * @return BlockState
	 */
	public function get_state( Context $context ): BlockState {

		if ( ! $context->has_entries() ) {
			return new BlockState( false );
		}

		$this->milestone = $this->milestones->resolve( $context->get_data() );

		return new BlockState( $this->milestone !== null, (string) ( $this->milestone['key'] ?? '' ) );
	}

	/**
	 * Milestone copy; a forced preview renders the requested or the first-entry milestone.
	 *
	 * @since {VERSION}
	 *
	 * @param Context $context Summary context.
	 * @param string  $variant Milestone key.
	 *
	 * @return array
	 */
	public function get_args( Context $context, string $variant ): array {

		if ( $this->milestone !== null && $this->milestone['key'] === $variant ) {
			return $this->milestone;
		}

		return $this->milestones->build( $variant !== '' ? $variant : Milestones::FIRST_ENTRY, $context->get_data() );
	}

	/**
	 * The milestone key prefixed with the block id, e.g. `milestone_entries_100`.
	 *
	 * @since {VERSION}
	 *
	 * @param string $variant Milestone key.
	 *
	 * @return string
	 */
	public function get_key( string $variant ): string {

		return self::ID . '_' . ( $variant !== '' ? $variant : Milestones::FIRST_ENTRY );
	}

	/**
	 * Spend the milestone.
	 *
	 * @since {VERSION}
	 *
	 * @param Context $context Summary context.
	 * @param string  $variant Milestone key.
	 */
	public function on_sent( Context $context, string $variant ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		if ( $variant !== '' ) {
			$this->milestones->mark_spent( $variant );
		}
	}
}

<?php

namespace WPForms\Emails\Summaries\Slot;

/**
 * Block state value object: whether it renders, and which variant.
 *
 * @since {VERSION}
 */
class BlockState {

	/**
	 * Whether the block renders.
	 *
	 * @since {VERSION}
	 *
	 * @var bool
	 */
	private $visible;

	/**
	 * Template variant.
	 *
	 * @since {VERSION}
	 *
	 * @var string
	 */
	private $variant;

	/**
	 * Constructor.
	 *
	 * @since {VERSION}
	 *
	 * @param bool   $visible Whether the block renders.
	 * @param string $variant Template variant.
	 */
	public function __construct( bool $visible, string $variant = '' ) {

		$this->visible = $visible;
		$this->variant = $variant;
	}

	/**
	 * Whether the block renders.
	 *
	 * @since {VERSION}
	 *
	 * @return bool
	 */
	public function is_visible(): bool {

		return $this->visible;
	}

	/**
	 * Template variant.
	 *
	 * @since {VERSION}
	 *
	 * @return string
	 */
	public function get_variant(): string {

		return $this->variant;
	}
}

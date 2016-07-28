<?php

namespace Ixolit\Moreify\Exceptions;

/**
 * @package Moreify
 */
class InvalidTagException extends \InvalidArgumentException implements MoreifyException {
	/**
	 * @var mixed
	 */
	private $tag;

	/**
	 * @param mixed          $tag
	 * @param \Exception|null $previous
	 */
	public function __construct($tag, \Exception $previous = null) {
		parent::__construct(
			'Invalid tag. The tag must be a string and 50 bytes long at most. ' . \var_export($tag, true),
			0,
			$previous
		);

		$this->tag = $tag;
	}

	/**
	 * @return mixed
	 */
	public function getTag() {
		return $this->tag;
	}
}
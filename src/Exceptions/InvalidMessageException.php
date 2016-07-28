<?php

namespace Ixolit\Moreify\Exceptions;

/**
 * @package Moreify
 */
class InvalidMessageException extends \InvalidArgumentException implements MoreifyException {
	/**
	 * @var mixed
	 */
	private $message;

	/**
	 * @param mixed           $message
	 * @param int             $code
	 * @param \Exception|null $previous
	 */
	public function __construct($message, $code = 0, \Exception $previous = null) {
		parent::__construct(
			'Invalid message. The message must be a string. ' . \var_export($message, true),
			$code,
			$previous
		);

		$this->message = $message;
	}

	/**
	 * @return mixed
	 */
	public function getMessageText() {
		return $this->message;
	}
}
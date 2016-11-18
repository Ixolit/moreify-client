<?php

namespace Ixolit\Moreify\Exceptions;

/**
 * Indicates that the given phone number was invalid.
 *
 * @package Moreify
 */
class InvalidPhoneNumberException extends \InvalidArgumentException implements MoreifyException {
	/**
	 * @var string
	 */
	private $phoneNumber;

	public function __construct($phoneNumber, \Exception $previous = null) {
		parent::__construct(
			'Invalid phone number. Phone numbers should be in the international format starting with "+" or "00".' .
			'The phone number passed was: ' . \var_export($phoneNumber),
			1203,
			$previous
		);

		$this->phoneNumber = $phoneNumber;
	}

	/**
	 * @return string
	 */
	public function getPhoneNumber() {
		return $this->phoneNumber;
	}
}
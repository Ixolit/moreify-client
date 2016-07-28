<?php

namespace Ixolit\Moreify\Exception;

use Ixolit\Moreify\Exceptions\MoreifyException;

class InvalidVerificationCodeException extends \InvalidArgumentException implements MoreifyException {
	/**
	 * @var string
	 */
	private $verificationCode;

	/**
	 * @param string     $verificationCode
	 * @param int        $code
	 * @param \Exception $previous
	 */
	public function __construct($verificationCode, $code = 0, \Exception $previous = null) {
		parent::__construct('Invalid verification code: ' . \var_export($verificationCode, true), $code, $previous);
		$this->verificationCode = $verificationCode;
	}

	/**
	 * @return string
	 */
	public function getVerificationCode() {
		return $this->verificationCode;
	}
}
<?php

namespace Ixolit\Moreify\Exceptions;

class VerifyCodeMismatchException extends \InvalidArgumentException implements MoreifyException {

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
		parent::__construct('Verification code mismatch: ' . \var_export($verificationCode, true), $code, $previous);
		$this->verificationCode = $verificationCode;
	}

	/**
	 * @return string
	 */
	public function getVerificationCode() {
		return $this->verificationCode;
	}
}
<?php

namespace Ixolit\Moreify\Exceptions;

/**
 * @package Moreify
 */
class InternalErrorException extends \Exception implements MoreifyException {

	/**
	 * @param string $errorMessage
	 * @param int    $errorCode
	 */
	public function __construct($errorMessage, $errorCode) {}
}
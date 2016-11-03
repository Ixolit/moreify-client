<?php

namespace Ixolit\Moreify\Exceptions;

/**
 * @package Moreify
 */
class UrlsNotAllowedException extends \Exception implements MoreifyException {

	/**
	 * @var mixed
	 */
	private $message;

	/**
	 * @var array
	 */
	private $urlsFoundButNotAllowed;

	/**
	 * @param mixed           $message
	 * @param string		  $urlsFoundButNotAllowed
	 * @param int             $code
	 * @param \Exception|null $previous
	 */
	public function __construct($message, $urlsFoundButNotAllowed, $code = 0, \Exception $previous = null) {
		parent::__construct(
			'Invalid message. The delivery of URLs within SMS messages has not been approved yet!' . \var_export($message, true),
			$code,
			$previous
		);

		$this->message = $message;
		$this->urlsFoundButNotAllowed = explode('|', $urlsFoundButNotAllowed);
	}

	/**
	 * @return mixed
	 */
	public function getMessageText() {
		return $this->message;
	}

	/**
	 * @return array
	 */
	public function getUrlsFoundButNotAllowed() {
		return $this->urlsFoundButNotAllowed;
	}

}
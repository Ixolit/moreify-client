<?php

namespace Ixolit\Moreify\Responses;

/**
 * @package Moreify
 */
class VerificationCallResponse {
	/**
	 * @var bool
	 */
	private $success;
	/**
	 * @var string
	 */
	private $messageIdentifier;
	/**
	 * @var string
	 */
	private $tag;

	public function __construct($success, $messageIdentifier, $tag = '') {
		$this->success = $success;
		$this->messageIdentifier = $messageIdentifier;
		$this->tag = $tag;
	}

	/**
	 * @return bool
	 */
	public function getSuccess() {
		return $this->success;
	}

	/**
	 * @return string
	 */
	public function getMessageIdentifier() {
		return $this->messageIdentifier;
	}

	/**
	 * @return string
	 */
	public function getTag() {
		return $this->tag;
	}


}